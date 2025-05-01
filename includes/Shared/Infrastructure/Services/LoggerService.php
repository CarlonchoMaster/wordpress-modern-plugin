<?php
declare(strict_types=1);

namespace Fronpe\Fronpe_Settings\Shared\Infrastructure\Services;

use Fronpe\Fronpe_Settings\Shared\Domain\Constants\LoggerLevel;
use Throwable;

/**
 * Servicio de logging que utiliza el sistema nativo de WordPress
 */
readonly class LoggerService
{
  private string $source;

  /**
   * @param string $name Nombre del origen para identificar los mensajes de log
   */
  public function __construct(string $name = 'fronpe-settings')
  {
    $this->source = $name;
  }

  /**
   * Registra un mensaje de nivel INFO
   */
  public function info($message, array $context = []): void
  {
    $this->writeLog(LoggerLevel::INFO, $message, $context);
  }

  /**
   * Registra un mensaje de nivel ERROR
   */
  public function error($message, array $context = []): void
  {
    $this->writeLog(LoggerLevel::ERROR, $message, $context);
  }

  /**
   * Registra un mensaje de nivel DEBUG
   */
  public function debug($message, array $context = []): void
  {
    $this->writeLog(LoggerLevel::DEBUG, $message, $context);
  }

  /**
   * Registra un mensaje de nivel WARNING
   */
  public function warning($message, array $context = []): void
  {
    $this->writeLog(LoggerLevel::WARN, $message, $context);
  }

  /**
   * Escribe un mensaje en el log con el nivel especificado
   */
  private function writeLog(string $level, mixed $message, array $context = []): void
  {
    // Verificar si el debugging está activado en WordPress
    if ( ! $this->isDebugEnabled()) {
      return;
    }

    // Formatear el mensaje
    $formattedMessage = $this->formatMessage($level, $message, $context);

    // Usar la función nativa de PHP para escribir en el log
    error_log($formattedMessage);
  }

  /**
   * Formatea un mensaje para el log con nivel, origen y contexto
   */
  private function formatMessage(string $level, mixed $message, array $context = []): string
  {
    // Convertir objetos o arrays a string si es necesario
    if ( ! is_string($message)) {
      $message = print_r($message, true);
    }

    // Formatear el mensaje básico con prefijo y nivel
    $formattedMessage = sprintf(
      "[%s] [%s] %s",
      $this->source,
      $level,
      $message
    );

    // Agregar contexto si existe
    if ( ! empty($context)) {
      // Convertir el contexto a JSON o formato legible
      $contextStr = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
      if ($contextStr === false) {
        // Si la conversión JSON falla, usar print_r
        $contextStr = print_r($context, true);
      }

      $formattedMessage .= " | Context: $contextStr";
    }

    return $formattedMessage;
  }

  /**
   * Verifica si el debugging está habilitado en WordPress
   */
  private function isDebugEnabled(): bool
  {
    return defined('WP_DEBUG') && WP_DEBUG &&
           defined('WP_DEBUG_LOG') && WP_DEBUG_LOG;
  }

  /**
   * Registra el tiempo de ejecución de una operación
   */
  public function timeOperation(callable $operation, string $operationName): mixed
  {
    $startTime = microtime(true);
    $result    = $operation();
    $endTime   = microtime(true);

    $executionTime = round(($endTime - $startTime) * 1000, 2);

    $this->info(sprintf(
      "Operación '%s' completada en %s ms",
      $operationName,
      $executionTime
    ));

    return $result;
  }

  /**
   * Registra un manejador de errores PHP personalizado
   */
  public function registerErrorHandler(): void
  {
    // No registrar si no estamos en modo debug
    if ( ! $this->isDebugEnabled()) {
      return;
    }

    // Guardar el manejador existente
    $previousHandler = set_error_handler(function (
      int $errNo,
      string $errStr,
      string $errFile,
      int $errLine
    ) use (&$previousHandler) {
      // No registrar si error reporting está desactivado (por @)
      if ( ! (error_reporting() & $errNo)) {
        return false;
      }

      // Mapear tipo de error a nivel de log
      $level = match ($errNo) {
        E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR,
        E_RECOVERABLE_ERROR => LoggerLevel::ERROR,

        E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING,
        E_USER_WARNING => LoggerLevel::WARN,

        E_NOTICE, E_USER_NOTICE => LoggerLevel::NOTICE,
        E_DEPRECATED, E_USER_DEPRECATED => LoggerLevel::DEPRECATED,

        default => LoggerLevel::DEBUG
      };

      // Registrar el error
      $this->writeLog($level, $errStr, [
        'file'       => $errFile,
        'line'       => $errLine,
        'error_type' => $errNo
      ]);

      // Llamar al manejador anterior
      if ($previousHandler !== null) {
        return call_user_func($previousHandler, $errNo, $errStr, $errFile, $errLine);
      }

      // False permite a PHP manejar el error normalmente
      return false;
    });

    // Registrar un manejador de excepciones no capturadas
    set_exception_handler(function (Throwable $exception) {
      $this->error($exception->getMessage(), [
        'file'  => $exception->getFile(),
        'line'  => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
      ]);
    });
  }

  /**
   * Rota el archivo de log si excede un tamaño
   * Útil para evitar archivos de log demasiado grandes
   */
  public function rotateLogIfNeeded(int $maxSizeBytes = 5242880): void
  {
    if ( ! $this->isDebugEnabled()) {
      return;
    }

    // Normalmente el log está en wp-content/debug.log pero verificamos si está personalizado
    $logFile = defined('WP_DEBUG_LOG') && is_string(WP_DEBUG_LOG)
      ? WP_DEBUG_LOG
      : WP_CONTENT_DIR . '/debug.log';

    if ( ! file_exists($logFile) || ! is_readable($logFile) || ! is_writable($logFile)) {
      return;
    }

    // Verificar tamaño
    if (filesize($logFile) <= $maxSizeBytes) {
      return;
    }

    $backupFile = WP_CONTENT_DIR . '/debug-' . date('Y-m-d-H-i-s') . '.log';

    if (rename($logFile, $backupFile)) {
      // Crear nuevo archivo de log con marca de rotación
      file_put_contents(
        $logFile,
        sprintf(
          "[%s] [] Log rotado en %s. Archivo anterior: %s\n",
          $this->source,
          date('Y-m-d H:i:s'),
          basename($backupFile)
        )
      );

      $this->info("Archivo de log rotado satisfactoriamente", [
        'previous_size' => filesize($backupFile),
        'backup_file'   => basename($backupFile)
      ]);
    }
  }
}

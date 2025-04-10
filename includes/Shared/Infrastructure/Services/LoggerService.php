<?php
declare(strict_types=1);

namespace Fronpe\Fronpe_Settings\Shared\Infrastructure\Services;

use Exception;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

readonly class LoggerService
{
  private Logger $logger;
  private string $logPath;

  public function __construct(string $name = 'fronpe-settings')
  {
    // Crear logger
    $this->logger = new Logger($name);

    // Define el directorio de logs
    $logDir        = WP_CONTENT_DIR . '/logs';
    $this->logPath = $logDir . '/' . $name . '.log';

    // Verificar y crear el directorio de logs si no existe
    $this->_ensureLogDirectoryExists($logDir);

    // Añadir handlers
    $this->logger->pushHandler(new StreamHandler($this->logPath, Level::Debug));

    // En desarrollo podríamos añadir este handler para depurar
    if (defined('WP_DEBUG') && WP_DEBUG) {
      $this->logger->pushHandler(new FirePHPHandler());
    }
  }

  public function info($message, array $context = []): void
  {
    try {
      $this->logger->info($message, $context);
    } catch (Exception $e) {
      error_log('Error al escribir log info: ' . $e->getMessage());
    }
  }

  public function error($message, array $context = []): void
  {
    try {
      $this->logger->error($message, $context);
    } catch (Exception $e) {
      error_log('Error al escribir log error: ' . $e->getMessage());
    }
  }

  public function debug($message, array $context = []): void
  {
    try {
      $this->logger->debug($message, $context);
    } catch (Exception $e) {
      error_log('Error al escribir log debug: ' . $e->getMessage());
    }
  }

  public function warning($message, array $context = []): void
  {
    try {
      $this->logger->warning($message, $context);
    } catch (Exception $e) {
      error_log('Error al escribir log warning: ' . $e->getMessage());
    }
  }

  /**
   * Obtener la ruta del archivo de log
   */
  public function getLogPath(): string
  {
    return $this->logPath;
  }

  /**
   * Asegura que el directorio de logs exista, sea escribible y que el archivo de log también exista
   */
  private function _ensureLogDirectoryExists(string $logDir): void
  {
    // Verificar si el directorio existe
    if ( ! file_exists($logDir)) {
      $this->createDirectoryLog($logDir);
    }

    // Verificar que el directorio sea escribible
    if ( ! is_writable($logDir)) {
      error_log("El directorio de logs no es escribible: $logDir");

      return;
    }

    // Verificar si el archivo de log existe, y crearlo si no
    if ( ! file_exists($this->logPath)) {
      $this->createFileLog();
    }

    // Verificar que el archivo sea escribible
    if (file_exists($this->logPath) && ! is_writable($this->logPath)) {
      error_log("El archivo de log no es escribible: $this->logPath");
    }
  }

  private function createDirectoryLog(string $logDir): void
  {
    // Intentar crear el directorio con permisos 0755 (propietario: lectura/escritura/ejecución, grupo y otros: lectura/ejecución)
    if ( ! mkdir($logDir, 0755, true) && ! is_dir($logDir)) {
      error_log("No se pudo crear el directorio de logs: $logDir");

      return;
    }

    // Crear un archivo .htaccess para proteger los logs en servidores Apache
    if ( ! file_exists("$logDir/.htaccess")) {
      $data = '
      # Denegar acceso a los archivos de log
      <FilesMatch \"\.(log)$\">
        Order allow,deny
        Deny from all
      </FilesMatch>
      ';

      file_put_contents("$logDir/.htaccess", $data);
    }

    // Crear un archivo index.php vacío para mayor seguridad
    if ( ! file_exists("$logDir/index.php")) {
      file_put_contents("$logDir/index.php", "<?php\n// Silence is golden.");
    }
  }

  private function createFileLog(): void
  {
    try {
      // Crear el archivo vacío
      $result = file_put_contents($this->logPath, '');
      if ($result === false) {
        error_log("No se pudo crear el archivo de log: $this->logPath");

        return;
      }

      chmod($this->logPath, 0644);
    } catch (Exception $e) {
      error_log("Error al crear el archivo de log: " . $e->getMessage());
    }
  }
}

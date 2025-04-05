<?php
declare(strict_types=1);

namespace Fronpe\Fronpe_Settings\Shared\Infrastructure\Services;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

readonly class LoggerService {
  private Logger $logger;

  public function __construct(string $name = 'frontend100p') {
    // Crear logger
    $this->logger = new Logger($name);

    // Define la ubicación del log fuera del directorio público
    $logPath = WP_CONTENT_DIR . '/logs/' . $name . '.log';

    // Añadir handlers
    $this->logger->pushHandler(new StreamHandler($logPath, Logger::DEBUG));

    // En desarrollo podríamos añadir este handler para depurar
    if (defined('WP_DEBUG') && WP_DEBUG) {
      $this->logger->pushHandler(new FirePHPHandler());
    }
  }

  public function info($message, array $context = []): void {
    $this->logger->info($message, $context);
  }

  public function error($message, array $context = []): void {
    $this->logger->error($message, $context);
  }

  // Añade más métodos según necesites
}

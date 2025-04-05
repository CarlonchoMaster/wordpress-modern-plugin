<?php
declare(strict_types=1);

namespace Fronpe\Fronpe_Settings\Shared\Infrastructure\Services;

use Fronpe\Fronpe_Settings\Shared\Domain\Models\DIContainer;

class ContainerService
{
  private static ?DIContainer $instance = null;

  public static function set(DIContainer $container): void {
    self::$instance = $container;
  }

  public static function get(): DIContainer {
    if (self::$instance === null) {
      throw new \RuntimeException('Global Container no inicializado');
    }

    return self::$instance;
  }

  public function __clone(): void
  {
    _doing_it_wrong(__FUNCTION__, esc_html(__('Cloning of Fronpe_Setting is forbidden.')), esc_attr($this->parent->_version));
  }

}

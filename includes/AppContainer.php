<?php
namespace Frontend100p\Frontend100p_Settings;

use Frontend100p\Frontend100p_Settings\Services\DIContainerService;

class AppContainer
{
  private static ?DIContainerService $instance = null;

  public static function set(DIContainerService $container): void {
    self::$instance = $container;
  }

  public static function get(): DIContainerService {
    if (self::$instance === null) {
      throw new \RuntimeException('Global Container no inicializado');
    }

    return self::$instance;
  }
}

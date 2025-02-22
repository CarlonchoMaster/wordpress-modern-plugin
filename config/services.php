<?php
use Frontend100p\Frontend100p_Settings\DIContainer;
use Frontend100p\Frontend100p_Settings\Frontend100pPlugin;
use Frontend100p\Frontend100p_Settings\Services\MigrationService;
use Frontend100p\Frontend100p_Settings\Services\AssetService;
use Frontend100p\Frontend100p_Settings\Services\ShortCodeService;

return function (DIContainer $container) {
  // Registrar parámetros
  $container->setParameter('plugin_path', FRONTEND100P_SETTINGS_PATH);
  $container->setParameter('plugin_url', FRONTEND100P_SETTINGS_URL);
  $container->setParameter('version', FRONTEND100P_SETTINGS_VERSION);

  // Registrar servicios
  $container->set(MigrationService::class);
  $container->set(AssetService::class);
  $container->set(ShortCodeService::class);

  $container->set(Frontend100pPlugin::class, function($container) {
    return new Frontend100pPlugin(
      $container->get(MigrationService::class),
      $container->get(AssetService::class)
    );
  });
};

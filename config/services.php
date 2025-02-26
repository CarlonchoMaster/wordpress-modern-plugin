<?php

use Frontend100p\Frontend100p_Settings\Services\DIContainerService;
use Frontend100p\Frontend100p_Settings\Frontend100pPlugin;
use Frontend100p\Frontend100p_Settings\Services\MigrationService;
use Frontend100p\Frontend100p_Settings\Services\AssetService;
use Frontend100p\Frontend100p_Settings\Services\ShortCodeService;

return function (DIContainerService $containerSrv) {
  // Registrar parámetros
  $containerSrv->setParameter('plugin_path', FRONTEND100P_SETTINGS_PATH);
  $containerSrv->setParameter('plugin_url', FRONTEND100P_SETTINGS_URL);
  $containerSrv->setParameter('version', FRONTEND100P_SETTINGS_VERSION);

  // Registrar servicios
  $containerSrv->set(MigrationService::class);
  $containerSrv->set(AssetService::class);
  $containerSrv->set(ShortCodeService::class);

  $containerSrv->set(Frontend100pPlugin::class, function ($container) {
    $shortcodeSrv = $container->get(ShortCodeService::class);
    $migrationSrv = $container->get(MigrationService::class);

    return new Frontend100pPlugin(shortcodeSrv: $shortcodeSrv, migrationSrv: $migrationSrv);
  });
};

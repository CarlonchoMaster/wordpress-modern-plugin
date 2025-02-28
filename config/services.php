<?php

use Fronpe\Fronpe_Settings\Services\Depend;
use Frontend100p\Frontend100p_Settings\FronpePlugin;
use Frontend100p\Frontend100p_Settings\Services\MigrationService;
use Frontend100p\Frontend100p_Settings\Services\AssetService;
use Frontend100p\Frontend100p_Settings\Services\ShortCodeService;

return function (Depend $containerSrv) {
  // Registrar parámetros
  $containerSrv->setParameter('plugin_path', FRONTEND100P_SETTINGS_PATH);
  $containerSrv->setParameter('plugin_url', FRONTEND100P_SETTINGS_URL);
  $containerSrv->setParameter('version', FRONTEND100P_SETTINGS_VERSION);

  // Registrar servicios
  $containerSrv->set(MigrationService::class);
  $containerSrv->set(AssetService::class);
  $containerSrv->set(ShortCodeService::class);

  $containerSrv->set(FronpePlugin::class, function ($container) {
    $shortcodeSrv = $container->get(ShortCodeService::class);
    $migrationSrv = $container->get(MigrationService::class);

    return new FronpePlugin(shortcodeSrv: $shortcodeSrv, migrationSrv: $migrationSrv);
  });
};

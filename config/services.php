<?php

use Fronpe\Fronpe_Settings\Shared\Domain\Models\DIContainer;
use Fronpe\Fronpe_Settings\FronpePlugin;
use Fronpe\Fronpe_Settings\Shared\Infrastructure\Services\MigrationService;
use Fronpe\Fronpe_Settings\Shared\Infrastructure\Services\AssetService;
use Fronpe\Fronpe_Settings\Shared\Infrastructure\Services\ShortCodeService;
use Fronpe\Fronpe_Settings\Shared\Domain\Constants\AppKeys;

return function (DIContainer $containerSrv) {
  // Registrar parámetros
  $containerSrv->setParameter(AppKeys::PLUGIN_PATH, FRONPE_SETTINGS_PATH);
  $containerSrv->setParameter(AppKeys::PLUGIN_URL, FRONPE_SETTINGS_URL);
  $containerSrv->setParameter(AppKeys::VERSION, FRONPE_SETTINGS_VERSION);

  // Registrar servicios
  $containerSrv->set(MigrationService::class);
  $containerSrv->set(AssetService::class);
  $containerSrv->set(ShortCodeService::class);

  $containerSrv->set(FronpePlugin::class, function ($container) {
    $shortcodeSrv = $container->get(ShortCodeService::class);
    $migrationSrv = $container->get(MigrationService::class);

    return new FronpePlugin(shortcodeSrv: $shortcodeSrv, migrationSrv: $migrationSrv, pluginPath: FRONPE_SETTINGS_PATH);
  });
};

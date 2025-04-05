<?php
declare(strict_types=1);

use Fronpe\Fronpe_Settings\Shared\Domain\Models\DIContainer;
use Fronpe\Fronpe_Settings\FronpePlugin;
use Fronpe\Fronpe_Settings\Shared\Domain\Constants\AppKeys;
use Fronpe\Fronpe_Settings\Shared\Infrastructure\Services\{AdminSettingsService,
  ImageService,
  LoggerService,
  MigrationService,
  AssetService,
  SeoService,
  ShortCodeService,
  SecurityService
};

return function (DIContainer $container) {
  // Registrar parámetros
  $container->setParameter(AppKeys::PLUGIN_PATH, FRONPE_SETTINGS_PATH);
  $container->setParameter(AppKeys::PLUGIN_URL, FRONPE_SETTINGS_URL);
  $container->setParameter(AppKeys::VERSION, FRONPE_SETTINGS_VERSION);
  $container->setParameter(AppKeys::PLUGIN_NAME, FRONPE_SETTINGS_BASENAME);

  // Registrar servicios
  $container->set(MigrationService::class);
  $container->set(AssetService::class);
  $container->set(ShortCodeService::class);
  $container->set(LoggerService::class);
  $container->set(ImageService::class);
  $container->set(SeoService::class);
  $container->set(SecurityService::class);

  // Registrar el servicio de Admin Settings
  $container->set(AdminSettingsService::class, function (DIContainer $diContainer) {
    $assetService = $diContainer->get(AssetService::class);

    return new AdminSettingsService(
      assetService: $assetService,
      pluginVersion: FRONPE_SETTINGS_VERSION
    );
  });

  $container->set(FronpePlugin::class, function (DIContainer $diContainer) {
    $shortcodeSrv     = $diContainer->get(ShortCodeService::class);
    $migrationSrv     = $diContainer->get(MigrationService::class);
    $imageSrv         = $diContainer->get(ImageService::class);
    $adminSettingsSrv = $diContainer->get(AdminSettingsService::class);
    $seoSrv           = $diContainer->get(SeoService::class);
    $securitySrv = $diContainer->get(SecurityService::class);

    return new FronpePlugin(
      shortcodeSrv: $shortcodeSrv,
      migrationSrv: $migrationSrv,
      imageSrv: $imageSrv,
      adminSettingsSrv: $adminSettingsSrv,
      securitySrv: $securitySrv,
      seoSrv: $seoSrv,
      pluginPath: FRONPE_SETTINGS_PATH
    );
  });
};

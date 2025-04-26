<?php
declare(strict_types=1);

use Fronpe\Fronpe_Settings\FronpePlugin;
use Fronpe\Fronpe_Settings\Shared\Domain\Constants\AppKeys;
use Fronpe\Fronpe_Settings\Shared\Domain\Models\DIContainer;
use Fronpe\Fronpe_Settings\Shared\Infrastructure\Services\{AdminSettingsService,
  AssetService,
  ImageService,
  LoggerService,
  MigrationService,
  SecurityService,
  SEOService,
  ShortcodeService
};
use Fronpe\Fronpe_Settings\Shared\Infrastructure\Shortcodes\{MainSliderShortcode,
  PackagesByPageShortcode,
  PageClusterShortcode
};

return function (DIContainer $container) {
  // Registrar parámetros
  $container->setParameter(AppKeys::PLUGIN_PATH, FRONPE_SETTINGS_PATH);
  $container->setParameter(AppKeys::PLUGIN_URL, FRONPE_SETTINGS_URL);
  $container->setParameter(AppKeys::PLUGIN_VERSION, FRONPE_SETTINGS_VERSION);
  $container->setParameter(AppKeys::PLUGIN_NAME, FRONPE_SETTINGS_BASENAME);

  // Registrar shortcodes
  $container->set(MainSliderShortcode::class, fn($_) => new MainSliderShortcode());
  $container->set(PackagesByPageShortcode::class, fn($_) => new PackagesByPageShortcode());
  $container->set(PageClusterShortcode::class, fn($_) => new PageClusterShortcode());

  // Registrar servicios
  $container->set(MigrationService::class);
  $container->set(AssetService::class);
  $container->set(LoggerService::class);
  $container->set(ImageService::class);
  $container->set(SecurityService::class);
  $container->set(SEOService::class, fn(DIContainer $di) => new SEOService($di->get(LoggerService::class)));
  $container->set(ShortcodeService::class, function (DIContainer $di) {
    return new ShortcodeService(
      mainSliderShortcode: $di->get(MainSliderShortcode::class),
      packagesByPageShortcode: $di->get(PackagesByPageShortcode::class),
      pageClusterShortcode: $di->get(PageClusterShortcode::class)
    );
  });

  // Registrar el servicio de Admin Settings
  $container->set(AdminSettingsService::class, function (DIContainer $di) {
    return new AdminSettingsService($di->getParameter(AppKeys::PLUGIN_VERSION, FRONPE_SETTINGS_VERSION));
  });

  $container->set(FronpePlugin::class, function (DIContainer $di) {
    return new FronpePlugin(
      shortcodeSrv: $di->get(ShortcodeService::class),
      migrationSrv: $di->get(MigrationService::class),
      imageSrv: $di->get(ImageService::class),
      securitySrv: $di->get(SecurityService::class),
      seoSrv: $di->get(SEOService::class),
      pluginPath: $di->getParameter(AppKeys::PLUGIN_PATH, FRONPE_SETTINGS_PATH)
    );
  });
};

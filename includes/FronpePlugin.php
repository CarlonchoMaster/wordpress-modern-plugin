<?php
declare(strict_types=1);

namespace Fronpe\Fronpe_Settings;

use Fronpe\Fronpe_Settings\Shared\Infrastructure\Services\{AdminSettingsService,
  ImageService,
  MigrationService,
  SecurityService,
  SeoService,
  ShortCodeService
};

readonly class FronpePlugin
{
  public function __construct(
    private ShortCodeService $shortcodeSrv,
    private MigrationService $migrationSrv,
    private ImageService $imageSrv,
    private AdminSettingsService $adminSettingsSrv,
    private SecurityService $securitySrv,
    private SeoService $seoSrv,
    private string $pluginPath
  ) {
  }

  public function init(): void
  {
    // Registrar hooks
    register_activation_hook($this->pluginPath . 'fronpe-settings.php',
      [$this->migrationSrv, 'activate']
    );

    register_deactivation_hook($this->pluginPath . 'fronpe-settings.php',
      [$this->migrationSrv, 'deactivate']
    );

    // Inicializar componentes
    /*if ( is_admin() ) {
      // Código si es administrador
    }*/

    $this->shortcodeSrv->init();
    $this->imageSrv->init();
    $this->seoSrv->init();
    $this->securitySrv->init();
  }
}

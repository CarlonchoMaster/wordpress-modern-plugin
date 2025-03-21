<?php

namespace Fronpe\Fronpe_Settings;

use Fronpe\Fronpe_Settings\Shared\Infrastructure\Services\MigrationService;
use Fronpe\Fronpe_Settings\Shared\Infrastructure\Services\ShortCodeService;

readonly class FronpePlugin
{
  public function __construct(
    private ShortCodeService $shortcodeSrv,
    private MigrationService $migrationSrv,
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
  }
}

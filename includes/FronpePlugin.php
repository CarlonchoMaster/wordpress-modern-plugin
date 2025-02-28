<?php

namespace Fronpe\Fronpe_Settings;

use Fronpe\Fronpe_Settings\Shared\Infrastructure\Services\MigrationService;
use Fronpe\Fronpe_Settings\Shared\Infrastructure\Services\ShortCodeService;

class FronpePlugin
{
  public function __construct(
    private readonly ShortCodeService $shortcodeSrv,
    private readonly MigrationService $migrationSrv
  ) {
  }

  public function init(): void
  {
    // Registrar hooks
    register_activation_hook(FRONTEND100P_SETTINGS_PATH . 'frontend100p-settings.php',
      [$this->migrationSrv, 'activate']
    );

    register_deactivation_hook(FRONTEND100P_SETTINGS_PATH . 'frontend100p-settings.php',
      [$this->migrationSrv, 'deactivate']
    );

    // Inicializar componentes
    /*if ( is_admin() ) {
      // Código si es administrador
    }*/

    $this->shortcodeSrv->init();
  }
}

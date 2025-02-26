<?php

namespace Frontend100p\Frontend100p_Settings;

use Frontend100p\Frontend100p_Settings\Services\AssetService;
use Frontend100p\Frontend100p_Settings\Services\MigrationService;
use Frontend100p\Frontend100p_Settings\Services\ShortCodeService;

readonly class Frontend100pPlugin
{
  public function __construct(
    private ShortCodeService $shortcodeSrv,
    private MigrationService $migrationSrv
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

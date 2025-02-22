<?php

namespace Frontend100p\Frontend100p_Settings;

use Frontend100p\Frontend100p_Settings\Services\AssetService;
use Frontend100p\Frontend100p_Settings\Services\MigrationService;
use Frontend100p\Frontend100p_Settings\Services\ShortCodeService;

class Frontend100pPlugin {
  public function __construct(
    private ShortCodeService $shortcodeService,
    private MigrationService $migrationService
  ) {
  }

  public function init(): void {
    // Registrar hooks
    register_activation_hook( FRONTEND100P_SETTINGS_PATH . 'frontend100p-settings.php',
      [ $this->migrationService, 'activate' ]
    );

    register_deactivation_hook( FRONTEND100P_SETTINGS_PATH . 'frontend100p-settings.php',
      [ $this->migrationService, 'deactivate' ]
    );

    // Inicializar componentes
    /*if ( is_admin() ) {
      // Codigo si es administrador
    }*/

    $this->shortcodeService->init();
  }
}

<?php
declare(strict_types=1);

namespace Fronpe\Fronpe_Settings\Shared\Presentation\Pages;

class AdminPage
{
  public function init(): void
  {
    add_action('admin_menu', [$this, 'addMenuPage']);
  }

  public function addMenuPage(): void
  {
    add_menu_page(
      __('Fronpe Settings', 'fronpe-settings'),  // Título de la página
      __('Fronpe', 'fronpe-settings'),          // Título del menú
      'manage_options',                         // Capacidad requerida
      'fronpe-settings',                        // Slug del menú (esto es lo importante)
      [$this, 'renderPage'],                    // Función callback
      'dashicons-admin-generic'                 // Icono
    );
  }

  public function renderPage(): void
  {
    include FRONPE_SETTINGS_PATH . 'templates/admin/main-page.php';
  }
}

<?php
declare(strict_types=1);

namespace Fronpe\Fronpe_Settings\Shared\Presentation\Pages;

class AdminPage
{
  public function init(): void
  {
    add_action('admin_menu', [$this, 'addMenuPage']);
    add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
  }

  public function addMenuPage(): void
  {
    add_menu_page(
      __('My Awesome Plugin', 'my-awesome-plugin'),
      __('Awesome Plugin', 'my-awesome-plugin'),
      'manage_options',
      'my-awesome-plugin',
      [$this, 'renderPage'],
      'dashicons-admin-generic'
    );
  }

  public function renderPage(): void
  {
    include FRONPE_SETTINGS_PATH . 'templates/admin/main-page.php';
  }
}

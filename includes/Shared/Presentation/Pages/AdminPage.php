<?php

namespace Fronpe\Fronpe_Settings\Shared\Presentation\Pages;

class AdminPage
{
  public function init(): void
  {
    add_action('admin_menu', [$this, 'add_menu_page']);
    add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
  }

  public function add_menu_page(): void
  {
    add_menu_page(
      __('My Awesome Plugin', 'my-awesome-plugin'),
      __('Awesome Plugin', 'my-awesome-plugin'),
      'manage_options',
      'my-awesome-plugin',
      [$this, 'render_page'],
      'dashicons-admin-generic'
    );
  }

  public function render_page(): void
  {
    include FRONPE_SETTINGS_PATH . 'templates/admin/main-page.php';
  }
}

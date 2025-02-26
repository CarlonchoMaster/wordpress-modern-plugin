<?php

namespace Frontend100p\Frontend100p_Settings\Services;

class MigrationService
{
  public function activate(): void
  {
    global $wpdb;

    $table_name      = $wpdb->prefix . 'awesome_plugin_data';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            name varchar(100) NOT NULL,
            value text NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    add_option('my_awesome_plugin_version', FRONTEND100P_SETTINGS_VERSION);
  }

  public function deactivate()
  {
    // Limpiar datos temporales si es necesario
  }
}

<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * Dashboard. This file also includes all the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * this starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Frontend100p-Settings
 *
 * @wordpress-plugin
 * Plugin Name:       Frontend100p Settings
 * Plugin URI:        http://example.com/plugin-name-uri/
 * Description:       Plugin para agregar funcionalidades a WordPress
 * Version:           1.0.0
 * Author:            Carlos Pereda
 * Author URI:        https://frontend100p.net
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       frontend100p-settings
 * Domain Path:       /languages
 */
if ( ! defined('ABSPATH')) {
  exit;
}

//Constantes del plugin
const FRONTEND100P_SETTINGS_VERSION = '1.0.0';
define("FRONTEND100P_SETTINGS_PATH", plugin_dir_path(__FILE__));
define('FRONTEND100P_SETTINGS_URL', plugin_dir_url(__FILE__));

require_once FRONTEND100P_SETTINGS_PATH . 'vendor/autoload.php';

use Frontend100p\Frontend100p_Settings\AppContainer;
use Frontend100p\Frontend100p_Settings\Frontend100pPlugin;
use Frontend100p\Frontend100p_Settings\Services\DIContainerService;

// Inicializar el contenedor
$container = new DIContainerService();

// Cargar configuración de servicios
$services = require_once FRONTEND100P_SETTINGS_PATH . 'config/services.php';
$services($container);

// Guardar en el contenedor estático
AppContainer::set($container);

//Inicializar el plugin
/**
 * @throws Exception
 */
function init_frontend100p_settings(): void
{
  global $container;
  $plugin = $container->get(Frontend100pPlugin::class);
  $plugin->init();
}

add_action('plugins_loaded', 'init_frontend100p_settings', 10, 0);



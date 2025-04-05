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
 * @package           Fronpe-Settings
 *
 * @wordpress-plugin
 * Plugin Name:       Fronpe Settings
 * Plugin URI:        http://example.com/plugin-name-uri/
 * Description:       Plugin para agregar funcionalidades a WordPress
 * Version:           1.0.0
 * Author:            Carlos Pereda
 * Author URI:        https://frontend100p.net
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       fronpe-settings
 * Domain Path:       /languages
 */
if ( ! defined('ABSPATH')) {
  exit;
}

//Constantes del plugin
const FRONPE_SETTINGS_VERSION = '1.0.0';
define('FRONPE_SETTINGS_PATH', plugin_dir_path(__FILE__));
define('FRONPE_SETTINGS_URL', plugin_dir_url(__FILE__));
define('FRONPE_SETTINGS_BASENAME', plugin_basename(__FILE__));

require_once FRONPE_SETTINGS_PATH . 'vendor/autoload.php';

use Fronpe\Fronpe_Settings\FronpePlugin;
use Fronpe\Fronpe_Settings\Shared\Domain\Models\DIContainer;
use Fronpe\Fronpe_Settings\Shared\Infrastructure\Services\ContainerService;

// Inicializar el contenedor
$container = new DIContainer();

// Cargar configuración de servicios
$services = require_once FRONPE_SETTINGS_PATH . 'config/services.php';
$services($container);

// Guardar en el contenedor estático
ContainerService::set($container);

//Inicializar el plugin
/**
 * @throws Exception
 */
function initFronpeSettings(): void
{
  global $container;
  /** @var FronpePlugin $plugin */
  $plugin = $container->get(FronpePlugin::class);
  $plugin->init();
}

add_action('plugins_loaded', 'initFronpeSettings', 10, 0);



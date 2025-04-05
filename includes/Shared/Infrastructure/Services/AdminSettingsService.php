<?php
declare(strict_types=1);

namespace Fronpe\Fronpe_Settings\Shared\Infrastructure\Services;

readonly class AdminSettingsService
{
  private string $pageTitle;
  private string $menuTitle;
  private string $capability;
  private string $menuSlug;
  private string $optionGroup;

  public function __construct(
    private AssetService $assetService,
    private string $pluginVersion
  ) {
    $this->pageTitle   = 'Fronpe Settings';
    $this->menuTitle   = 'Fronpe';
    $this->capability  = 'manage_options';
    $this->menuSlug    = 'fronpe-settings';
    $this->optionGroup = 'fronpe_settings_group';
  }

  /**
   * Inicializa el servicio de administración
   * Hook para WordPress
   */
  public function init(): void
  {
    add_action('admin_menu', [$this, 'addAdminMenu']);
    add_action('admin_init', [$this, 'registerSettings']);
    add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
  }

  /**
   * Agrega la página de menú en el panel de WordPress
   * Hook para WordPress
   */
  public function addAdminMenu(): void
  {
    add_menu_page(
      $this->pageTitle,
      $this->menuTitle,
      $this->capability,
      $this->menuSlug,
      [$this, 'renderSettingsPage'],
      'dashicons-admin-generic',
      30
    );
  }

  /**
   * Registra las configuraciones, secciones y campos
   * Hook para WordPress
   */
  public function registerSettings(): void
  {
    // Registrar grupo de opciones
    register_setting(
      $this->optionGroup,
      'fronpe_general_settings',
      [
        'sanitize_callback' => [$this, 'sanitizeGeneralSettings'],
        'default'           => [
          'enable_feature_1' => '0',
          'api_key'          => '',
          'custom_text'      => '',
        ]
      ]
    );

    // Sección General
    add_settings_section(
      'fronpe_general_section',
      __('General Settings', 'fronpe-settings'),
      [$this, 'renderGeneralSection'],
      $this->menuSlug
    );

    // Campos
    add_settings_field(
      'enable_feature_1',
      __('Enable Feature 1', 'fronpe-settings'),
      [$this, 'renderCheckboxField'],
      $this->menuSlug,
      'fronpe_general_section',
      [
        'label_for'   => 'enable_feature_1',
        'field_name'  => 'fronpe_general_settings[enable_feature_1]',
        'description' => __('Enable this feature to activate functionality 1', 'fronpe-settings'),
      ]
    );

    add_settings_field(
      'api_key',
      __('API Key', 'fronpe-settings'),
      [$this, 'renderTextField'],
      $this->menuSlug,
      'fronpe_general_section',
      [
        'label_for'   => 'api_key',
        'field_name'  => 'fronpe_general_settings[api_key]',
        'description' => __('Enter your API key here', 'fronpe-settings'),
      ]
    );

    add_settings_field(
      'custom_text',
      __('Custom Text', 'fronpe-settings'),
      [$this, 'renderTextareaField'],
      $this->menuSlug,
      'fronpe_general_section',
      [
        'label_for'   => 'custom_text',
        'field_name'  => 'fronpe_general_settings[custom_text]',
        'description' => __('Enter custom text here', 'fronpe-settings'),
      ]
    );

    // Segunda sección - Avanzada
    add_settings_section(
      'fronpe_advanced_section',
      __('Advanced Settings', 'fronpe-settings'),
      [$this, 'renderAdvancedSection'],
      $this->menuSlug
    );

    // Campos avanzados
    add_settings_field(
      'debug_mode',
      __('Debug Mode', 'fronpe-settings'),
      [$this, 'renderCheckboxField'],
      $this->menuSlug,
      'fronpe_advanced_section',
      [
        'label_for'   => 'debug_mode',
        'field_name'  => 'fronpe_general_settings[debug_mode]',
        'description' => __('Enable debug mode for developers', 'fronpe-settings'),
      ]
    );
  }

  /**
   * Sanitiza las opciones antes de guardarlas
   * Hook para WordPress
   */
  public function sanitizeGeneralSettings($input): array
  {
    $sanitized = [];

    // Sanitizar enable_feature_1 (checkbox)
    $sanitized['enable_feature_1'] = isset($input['enable_feature_1']) ? '1' : '0';

    // Sanitizar API key (text)
    $sanitized['api_key'] = sanitize_text_field($input['api_key'] ?? '');

    // Sanitizar custom_text (textarea)
    $sanitized['custom_text'] = sanitize_textarea_field($input['custom_text'] ?? '');

    // Sanitizar debug_mode (checkbox)
    $sanitized['debug_mode'] = isset($input['debug_mode']) ? '1' : '0';

    return $sanitized;
  }

  /**
   * Renderiza la página de configuración principal
   * Hook para WordPress
   */
  public function renderSettingsPage(): void
  {
    if ( ! current_user_can($this->capability)) {
      return;
    }

    $settings = get_option('fronpe_general_settings', []);

    // Capturar la salida de las funciones de WordPress
    ob_start();
    settings_fields($this->optionGroup);
    do_settings_sections($this->menuSlug);
    submit_button();
    $bufferView = ob_get_clean();

    // Construir el HTML en una variable
    $escapedPageTitle                = esc_html($this->pageTitle);
    $translatedTextPluginInformation = __('Plugin Information', 'fronpe-settings');
    $translatedTextVersion           = sprintf(__('Version: %s', 'fronpe-settings'), esc_html($this->pluginVersion));
    $translatedTextThankYou          = __('Thank you for using Fronpe Settings Plugin!', 'fronpe-settings');

    $htmlView = <<<HTML
        <div class="wrap">
            <h1>{$escapedPageTitle}</h1>
            <form method="post" action="options.php">{$bufferView}</form>
            <div class="fronpe-info-box">
                <h3>{$translatedTextPluginInformation}</h3>
                <p>{$translatedTextVersion}</p>
                <p>{$translatedTextThankYou}</p>
            </div>
        </div>
    HTML;

    // Imprimir todo el contenido con un solo echo
    echo $htmlView;
  }

  /**
   * Renderiza la descripción de la sección general
   * Hook para WordPress
   */
  public function renderGeneralSection(): void
  {
    $output = '<p>' . __('Configure the general settings for the plugin.', 'fronpe-settings') . '</p>';
    echo $output;
  }

  /**
   * Renderiza la descripción de la sección avanzada
   * Hook para WordPress
   */
  public function renderAdvancedSection(): void
  {
    $output = '<p>' . __('Advanced configuration options for developers.', 'fronpe-settings') . '</p>';
    echo $output;
  }

  /**
   * Renderiza un campo de tipo checkbox
   * Hook para WordPress
   */
  public function renderCheckboxField(array $args): void
  {
    $settings    = get_option('fronpe_general_settings', []);
    $fieldName   = $args['field_name'] ?? '';
    $labelFor    = $args['label_for'] ?? '';
    $description = $args['description'] ?? '';
    $checked     = isset($settings[$labelFor]) ? checked('1', $settings[$labelFor], false) : '';

    // Construir el HTML en una variable
    $output = '';
    $output .= '<input type="checkbox" id="' . esc_attr($labelFor) . '" ';
    $output .= 'name="' . esc_attr($fieldName) . '" ';
    $output .= 'value="1" ' . $checked . ' />';

    if ( ! empty($description)) {
      $output .= '<p class="description">' . esc_html($description) . '</p>';
    }

    // Imprimir todo el contenido con un solo echo
    echo $output;
  }

  /**
   * Renderiza un campo de tipo texto
   * Hook para WordPress
   */
  public function renderTextField(array $args): void
  {
    $settings    = get_option('fronpe_general_settings', []);
    $fieldName   = $args['field_name'] ?? '';
    $labelFor    = $args['label_for'] ?? '';
    $description = $args['description'] ?? '';
    $value       = $settings[$labelFor] ?? '';

    // Construir el HTML en una variable
    $output = '';
    $output .= '<input type="text" id="' . esc_attr($labelFor) . '" ';
    $output .= 'name="' . esc_attr($fieldName) . '" ';
    $output .= 'value="' . esc_attr($value) . '" ';
    $output .= 'class="regular-text" />';

    if ( ! empty($description)) {
      $output .= '<p class="description">' . esc_html($description) . '</p>';
    }

    // Imprimir todo el contenido con un solo echo
    echo $output;
  }

  /**
   * Renderiza un campo de tipo textarea
   * Hook para WordPress
   */
  public function renderTextareaField(array $args): void
  {
    $settings    = get_option('fronpe_general_settings', []);
    $fieldName   = $args['field_name'] ?? '';
    $labelFor    = $args['label_for'] ?? '';
    $description = $args['description'] ?? '';
    $value       = $settings[$labelFor] ?? '';

    // Construir el HTML en una variable
    $output = '';
    $output .= '<textarea id="' . esc_attr($labelFor) . '" ';
    $output .= 'name="' . esc_attr($fieldName) . '" ';
    $output .= 'rows="5" ';
    $output .= 'class="large-text">';
    $output .= esc_textarea($value);
    $output .= '</textarea>';

    if ( ! empty($description)) {
      $output .= '<p class="description">' . esc_html($description) . '</p>';
    }

    // Imprimir todo el contenido con un solo echo
    echo $output;
  }

  /**
   * Carga estilos y scripts en el admin
   * Hook para WordPress
   */
  public function enqueueAdminAssets($hook): void
  {
    // Solo cargar en la página de nuestro plugin
    if ('toplevel_page_' . $this->menuSlug !== $hook) {
      return;
    }

    // Enqueue CSS
    wp_enqueue_style(
      'fronpe-admin-styles',
      FRONPE_SETTINGS_URL . 'assets/admin/css/admin.css',
      [],
      $this->pluginVersion
    );

    // Enqueue JS
    wp_enqueue_script(
      'fronpe-admin-scripts',
      FRONPE_SETTINGS_URL . 'assets/admin/js/admin.js',
      ['jquery'],
      $this->pluginVersion,
      true
    );
  }

  /**
   * Métodos personalizados que siguen PSR-1 y PSR-2
   */

  /**
   * Obtiene una configuración específica
   * Método personalizado (camelCase)
   */
  private function getSetting(string $key, $default = null)
  {
    $settings = get_option('fronpe_general_settings', []);

    return $settings[$key] ?? $default;
  }

  /**
   * Genera una notificación para el admin
   * Método personalizado (camelCase)
   */
  private function addAdminNotice(string $message, string $type = 'info'): void
  {
    add_action('admin_notices', function () use ($message, $type) {
      $class = 'notice notice-' . esc_attr($type);
      printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
    });
  }

  /**
   * Construye un campo con un formato específico
   * Método personalizado (camelCase)
   */
  private function buildFieldHtml(string $type, array $attributes, string $description = ''): string
  {
    $html = '';

    // Tag de apertura específico según el tipo
    if ($type === 'textarea') {
      $html .= '<textarea';
    } else {
      $html .= '<input type="' . esc_attr($type) . '"';
    }

    // Agregar atributos
    foreach ($attributes as $key => $value) {
      $html .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
    }

    // Cerrar tag según el tipo
    if ($type === 'textarea') {
      $html .= '>' . esc_textarea($attributes['value'] ?? '') . '</textarea>';
    } else {
      $html .= ' />';
    }

    // Agregar descripción si existe
    if ( ! empty($description)) {
      $html .= '<p class="description">' . esc_html($description) . '</p>';
    }

    return $html;
  }
}

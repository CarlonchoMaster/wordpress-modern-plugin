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
    private string $pluginVersion
  ) {
    $this->pageTitle   = 'Fronpe Settings';
    $this->menuTitle   = 'Fronpe';
    $this->capability  = 'manage_options';
    $this->menuSlug    = 'fronpe-settings';
    $this->optionGroup = 'fronpe_settings_group';
  }

  /**
   * Inicializa el servicio de administración Hook para WordPress
   */
  public function init(): void
  {
    add_action('admin_menu', [$this, 'addAdminMenu']);
    add_action('admin_init', [$this, 'registerSettings']);
    add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
  }

  /**
   * Agrega la página de menú en el panel de WordPress
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
        <h1>$escapedPageTitle</h1>
        <form method="post" action="options.php">$bufferView</form>
        <div class="fronpe-info-box">
          <h3>$translatedTextPluginInformation</h3>
          <p>$translatedTextVersion</p>
          <p>$translatedTextThankYou</p>
        </div>
      </div>
    HTML;

    // Imprimir todo el contenido con un solo echo
    echo $htmlView;
  }

  /**
   * Renderiza la descripción de la sección general
   */
  public function renderGeneralSection(): void
  {
    echo sprintf(
      '<p>%s</p>',
      __('Configure the general settings for the plugin.', 'fronpe-settings')
    );
  }

  /**
   * Renderiza la descripción de la sección avanzada
   * Hook para WordPress
   */
  public function renderAdvancedSection(): void
  {
    echo sprintf(
      '<p>%s</p>',
      __('Advanced configuration options for developers.', 'fronpe-settings')
    );
  }

  /**
   * Renderiza un campo de tipo checkbox
   */
  public function renderCheckboxField(array $args): void
  {
    $settings    = get_option('fronpe_general_settings', []);
    $fieldName   = $args['field_name'] ?? '';
    $labelFor    = $args['label_for'] ?? '';
    $description = $args['description'] ?? '';
    $checked     = isset($settings[$labelFor]) ? checked('1', $settings[$labelFor], false) : '';

    // Construir el HTML en una variable
    $htmlView = sprintf(
      '<input type="checkbox" id="%s" name="%s" value="1" %s />',
      esc_attr($labelFor),
      esc_attr($fieldName),
      $checked
    );

    if ( ! empty($description)) {
      $htmlView .= sprintf(
        '<p class="description">%s</p>',
        esc_html($description)
      );
    }

    // Imprimir todo el contenido con un solo echo
    echo $htmlView;
  }

  /**
   * Renderiza un campo de tipo texto
   */
  public function renderTextField(array $args): void
  {
    $settings    = get_option('fronpe_general_settings', []);
    $fieldName   = $args['field_name'] ?? '';
    $labelFor    = $args['label_for'] ?? '';
    $description = $args['description'] ?? '';
    $value       = $settings[$labelFor] ?? '';

    // Construir el HTML en una variable
    $htmlView = sprintf(
      '<input type="text" id="%s" name="%s" value="%s" class="regular-text" />',
      esc_attr($labelFor),
      esc_attr($fieldName),
      esc_attr($value)
    );

    if ( ! empty($description)) {
      $htmlView .= sprintf(
        '<p class="description">%s</p>',
        esc_html($description)
      );
    }

    // Imprimir todo el contenido con un solo echo
    echo $htmlView;
  }

  /**
   * Renderiza un campo de tipo textarea
   */
  public function renderTextareaField(array $args): void
  {
    $settings    = get_option('fronpe_general_settings', []);
    $fieldName   = $args['field_name'] ?? '';
    $labelFor    = $args['label_for'] ?? '';
    $description = $args['description'] ?? '';
    $value       = $settings[$labelFor] ?? '';

    // Construir el HTML en una variable
    $htmlView = sprintf(
      '<textarea id="%s" name="%s" rows="5" class="large-text">%s</textarea>',
      esc_attr($labelFor),
      esc_attr($fieldName),
      esc_textarea($value)
    );

    if ( ! empty($description)) {
      $htmlView .= sprintf(
        '<p class="description">%s</p>',
        esc_html($description)
      );
    }

    // Imprimir todo el contenido con un solo echo
    echo $htmlView;
  }

  /**
   * Carga estilos y scripts en el admin
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
   * Obtiene una configuración específica
   */
  private function getSetting(string $key, mixed $default)
  {
    $settings = get_option('fronpe_general_settings', []);

    return $settings[$key] ?? $default;
  }

  /**
   * Genera una notificación para el admin
   */
  private function addAdminNotice(string $message): void
  {
    add_action('admin_notices', function () use ($message) {
      $class = 'notice notice-info';
      printf('<div class="%s"><p>%s</p></div>', $class, $message);
    });
  }

  /**
   * Construye un campo con un formato específico
   */
  private function buildFieldHtml(string $type, array $attributes, string $description = ''): string
  {
    $htmlView     = '';
    $htmlAttrView = '';

    // Agregar atributos
    foreach ($attributes as $key => $value) {
      $htmlAttrView .= sprintf(' %s="%s"', esc_attr($key), esc_attr($value));
    }

    // Tag de apertura específica según el tipo
    if ($type === 'textarea') {
      $htmlView .= sprintf(
        '<textarea %s>%s</textarea>',
        $htmlAttrView,
        esc_textarea($attributes['value'] ?? '')
      );

      $htmlView .= ! empty($description) ? sprintf('<p class="description">%s</p>', esc_html($description)) : '';

      return $htmlView;
    }

    $htmlView .= sprintf('<input type="%s" %s />', esc_attr($type), $htmlAttrView);
    $htmlView .= ! empty($description) ? sprintf('<p class="description">%s</p>', esc_html($description)) : '';

    return $htmlView;
  }
}

<?php
namespace Fronpe\Fronpe_Settings\Shared\Infrastructure\Services;

class ShortCodeService
{
  public function init(): void
  {
    add_shortcode('awesome_feature', [$this, 'render_shortcode']);
    add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
  }

  public function render_shortcode($atts, $content = null): bool|string
  {
    $attributes = shortcode_atts([
      'type' => 'default',
    ], $atts);

    ob_start();
    include FRONPE_SETTINGS_PATH . 'templates/shortcode/feature.php';

    return ob_get_clean();
  }

  public function enqueue_assets(): void
  {
    wp_enqueue_style(
      'my-awesome-plugin-frontend',
      FRONPE_SETTINGS_URL . 'assets/css/frontend.css',
      [],
      FRONPE_SETTINGS_VERSION
    );
  }
}

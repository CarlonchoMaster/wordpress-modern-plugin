<?php

namespace Frontend100p\Frontend100p_Settings\Services;

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
    include FRONTEND100P_SETTINGS_PATH . 'templates/shortcode/feature.php';

    return ob_get_clean();
  }

  public function enqueue_assets(): void
  {
    wp_enqueue_style(
      'my-awesome-plugin-frontend',
      FRONTEND100P_SETTINGS_URL . 'assets/css/frontend.css',
      [],
      FRONTEND100P_SETTINGS_VERSION
    );
  }
}

<?php
declare(strict_types=1);

namespace Fronpe\Fronpe_Settings\Shared\Infrastructure\Services;

class ShortCodeService
{
  public function init(): void
  {
    add_shortcode('awesome_feature', [$this, 'renderShortcode']);
    add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
  }

  public function renderShortcode($attrs, $content = null): bool|string
  {
    $attributes = shortcode_atts([
      'type' => 'default',
    ], $attrs);

    ob_start();
    include FRONPE_SETTINGS_PATH . 'templates/shortcode/feature.php';

    return ob_get_clean();
  }

  public function enqueueAssets(): void
  {
    wp_enqueue_style(
      'my-awesome-plugin-frontend',
      FRONPE_SETTINGS_URL . 'assets/css/frontend.css',
      [],
      FRONPE_SETTINGS_VERSION
    );
  }
}

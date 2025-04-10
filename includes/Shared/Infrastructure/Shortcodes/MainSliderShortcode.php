<?php

namespace Fronpe\Fronpe_Settings\Shared\Infrastructure\Shortcodes;

use WP_Post;
use WP_Query;

class MainSliderShortcode
{
  public function init(): void
  {
    add_shortcode('show-main-slider', [$this, 'showMainSlider']);
  }

  /**
   * Muestra un slider principal
   */
  public function showMainSlider(array $attrs = []): string
  {
    global $post;

    if ( ! isset($post) || ! ($post instanceof WP_Post) || ! is_page($post->ID)) {
      return '';
    }

    // Mensaje por defecto si no hay sliders
    $defaultMessage = '<p>No se encontraron Sliders.</p>';

    // Configurar atributos del shortcode con valores por defecto
    $shortcodeAttrs = shortcode_atts([
      'posts_per_page' => -1,
      'order'          => 'ASC',
      'orderby'        => 'menu_order',
    ], $attrs);

    // Argumentos para la consulta
    $queryArgs = [
      'post_type'      => 'slider',
      'posts_per_page' => $shortcodeAttrs['posts_per_page'],
      'post_status'    => 'private',
      'orderby'        => $shortcodeAttrs['orderby'],
      'order'          => $shortcodeAttrs['order'],
    ];

    // Ejecutar la consulta
    $query = new WP_Query($queryArgs);

    // Verificar si hay posts
    if ( ! $query->have_posts()) {
      return $defaultMessage;
    }

    return $this->buildSliderHtml($query);
  }

  /**
   * Genera el HTML para el slider
   * @noinspection HtmlUnknownTarget
   */
  private function buildSliderHtml(WP_Query $query): string
  {
    $partialHtmlHeaders = '';
    $partialHtmlBodies  = '';
    // Construir indicadores del carrusel
    $countIndicators = 0;
    $countSlides     = 0;

    while ($query->have_posts()) {
      $query->the_post();
      $activeClassNameHeader = $countIndicators === 0 ? 'active' : '';
      $partialHtmlHeaders    .= sprintf(
        '<li data-target="#main-carousel" data-slide-to="%d" class="%s"></li>',
        $countIndicators,
        $activeClassNameHeader
      );

      $activeClassNameBody = $countSlides === 0 ? 'active' : '';
      $linkHtml            = '';
      $imageHtml           = '';
      $content             = get_the_content();
      $link                = get_field('sli_link');
      $image               = get_field('sli_image');

      if ( ! empty($link)) {
        $linkHtml = sprintf(
          '<a class="btn btn-primary" href="%s">%s</a>',
          esc_url($link),
          __('Ver más', 'fronpe-settings')
        );
      }

      if ( ! empty($image)) {
        $imageHtml = sprintf(
          '<img src="%s" alt="%s" class="d-block w-100">',
          esc_url($image['url']),
          esc_attr($image['alt'] ?? '')
        );
      }

      $partialHtmlBodies .= <<<HTML
        <div class="carousel-item $activeClassNameBody">
          <div class="row">
            <div class="col-md-6">
              <p class="display-4 text-break">$content</p>
              $linkHtml
            </div>
            <div class="col-md-6">
              $imageHtml
            </div>
          </div>
        </div>
      HTML;

      $countSlides++;
      $countIndicators++;
    }
    // Restaurar datos originales
    wp_reset_postdata();

    return sprintf(
      '
        <section class="main-slider py-md-3 py-2 mb-2 mb-md-3">
          <div id="main-carousel" class="carousel slide container" data-ride="carousel">
            <ol class="carousel-indicators">%s</ol>
            <div class="carousel-inner">%s</div>
          </div>
        </section>
      ',
      $partialHtmlHeaders,
      $partialHtmlBodies
    );
  }
}

<?php
declare(strict_types=1);

namespace Fronpe\Fronpe_Settings\Shared\Infrastructure\Shortcodes;

use WP_Post;

class PageClusterShortcode
{
  public function init(): void
  {
    add_shortcode('pages-cluster', [$this, 'listChildPages']);
  }

  /**
   * Lista, páginas hijas de la página actual o de la página padre
   */
  public function listChildPages(array $attrs = []): string
  {
    global $post;

    // Configurar atributos del shortcode con valores por defecto
    $args = shortcode_atts([
      'sort_column' => 'menu_order',
      'depth'       => 1,
      'title_li'    => '',
      'echo'        => false,
      'css_class'   => 'pages-cluster',
    ], $attrs);

    $isNotPageAndInstance = ! (is_page() && $post instanceof WP_Post);
    // Determinar el ID de la página de referencia
    if ($isNotPageAndInstance) {
      return '';
    }

    if ($post->post_parent) {
      // Si estamos en una página hija, listar las hermanas (hijas del mismo padre)
      $args['child_of'] = $post->post_parent;

      return $this->getNavigationView($args);
    }

    $args['child_of'] = $post->ID;

    return $this->getNavigationView($args);
  }

  /**
   * Genera la vista de navegación para páginas
   */
  private function getNavigationView(array $args): string
  {
    // Obtener la lista de páginas
    $childPages = wp_list_pages($args);

    // Devolver el HTML formateado solo si hay páginas hijas
    if (empty($childPages)) {
      return '';
    }

    return sprintf(
      '<nav class="%s"><ul>%s</ul></nav>',
      esc_attr($args['css_class']),
      $childPages
    );
  }
}

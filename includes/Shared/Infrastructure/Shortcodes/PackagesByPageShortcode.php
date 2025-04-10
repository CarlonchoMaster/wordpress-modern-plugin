<?php

namespace Fronpe\Fronpe_Settings\Shared\Infrastructure\Shortcodes;

use WP_Post;

class PackagesByPageShortcode
{
  public function init(): void
  {
    add_shortcode('show-package-by-page', [$this, 'showPackageByPage']);
  }

  /**
   * Muestra los paquetes asociados a la página actual
   */
  public function showPackageByPage(array $attrs = []): string
  {
    global $post;
    global $wpdb;

    if ( ! isset($post) || ! ($post instanceof WP_Post)) {
      return '<p>No se pueden mostrar los paquetes.</p>';
    }

    if ( ! is_page($post->ID)) {
      return '<p>No se pueden visualizar paquetes en entradas.</p>';
    }

    // Definir nombres de tablas
    $tablePostMeta     = $wpdb->prefix . 'postmeta';
    $tablePost         = $wpdb->prefix . 'posts';
    $tableTerms        = $wpdb->prefix . 'terms';
    $tableTermTax      = $wpdb->prefix . 'term_taxonomy';
    $tableTermRelation = $wpdb->prefix . 'term_relationships';

    // Obtener paquetes asociados a la página actual
    $queryPackagesByPage  = $wpdb->prepare(
      "SELECT zp.post_id FROM $tablePostMeta zp WHERE zp.meta_key='pac_page' AND zp.meta_value=%d",
      $post->ID
    );
    $packagesByPageResult = $wpdb->get_results($queryPackagesByPage);

    if (empty($packagesByPageResult)) {
      return '<p>No se encontraron Paquetes.</p>';
    }

    // Extraer IDs de paquetes y convertirlos a string para la consulta
    $packagesByPage    = array_map(fn($p) => $p->post_id, $packagesByPageResult);
    $packagesByPageStr = implode(',', $packagesByPage);

    // Obtener información de los paquetes
    $queryPackages = "
      SELECT zp.ID, zp.post_excerpt, zp.post_title, zp.post_content, ztr.term_taxonomy_id
      FROM $tablePost zp
      INNER JOIN $tableTermRelation ztr ON zp.ID = ztr.object_id
      WHERE zp.ID IN ($packagesByPageStr)
      ORDER BY ztr.term_taxonomy_id
    ";

    $packagesResult = $wpdb->get_results($queryPackages);

    // Obtener términos de taxonomía asociados a los paquetes
    $queryTerms        = "SELECT DISTINCT ztr.term_taxonomy_id FROM $tableTermRelation ztr
                          WHERE ztr.object_id IN ($packagesByPageStr)";
    $termsResult       = $wpdb->get_results($queryTerms);
    $termsByPackage    = array_map(fn($t) => $t->term_taxonomy_id, $termsResult);
    $termsByPackageStr = implode(',', $termsByPackage);

    // Obtener información de taxonomías
    $queryTaxonomies  = "
      SELECT t.name, t.term_id, tt.taxonomy, tt.term_taxonomy_id
      FROM $tableTerms AS t
      INNER JOIN $tableTermTax AS tt ON t.term_id = tt.term_id
      WHERE tt.term_taxonomy_id IN ($termsByPackageStr)
    ";
    $taxonomiesResult = $wpdb->get_results($queryTaxonomies);

    // Configurar parámetros de visualización
    $phone            = get_field('busd_mobile_whatsapp', 'options');
    $subCategories    = array_filter($taxonomiesResult, function ($t) {
      return $t->taxonomy === 'subcategory_package';
    });
    $categories       = array_filter($taxonomiesResult, function ($t) {
      return $t->taxonomy === 'category_package';
    });
    $category         = ! empty($categories) ? reset($categories) : new stdClass();
    $hasSubCategories = ! empty($subCategories);

    // Generar la vista apropiada según la estructura de categorías
    if ($hasSubCategories) {
      return $this->getPackagesWithSubcategoriesView([
        'packagesResult' => $packagesResult,
        'phone'          => $phone,
        'subCategories'  => $subCategories,
        'category'       => $category
      ]);
    }

    return $this->getPackagesView($packagesResult, $category, $phone);
  }

  /**
   * Genera la vista de paquetes sin subcategorías
   */
  private function getPackagesView(array $packagesResult, object $category, string $phone): string
  {
    $viewHtml = '';

    foreach ($packagesResult as $package) {
      $categoryName = $category->name ?? '';
      $message      = urlencode($categoryName . ' - ' . $package->post_excerpt);
      $viewHtml     .= $this->getGroupBySubCategoriesView($package, $message, $phone);
    }

    return sprintf(
      '<div class="packages row justify-content-center row-cols-md-3 mb-3">%s</div>',
      $viewHtml
    );
  }

  /**
   * Genera la vista de paquetes con subcategorías
   */
  private function getPackagesWithSubcategoriesView(array $params): string
  {
    $packagesResult       = $params['packagesResult'];
    $phone                = $params['phone'];
    $subCategories        = $params['subCategories'];
    $category             = $params['category'];
    $currentSubCategoryId = -1;
    $isFirstTab           = true;
    $partialHtml          = '';

    foreach ($subCategories as $subCategory) {
      $packagesFilteredResult = array_filter($packagesResult,
        fn($p) => $p->term_taxonomy_id === $subCategory->term_taxonomy_id);

      foreach ($packagesFilteredResult as $package) {
        $categoryName = $category->name ?? '';
        $message      = urlencode($categoryName . ' - ' . $package->post_excerpt);

        if ($currentSubCategoryId !== (int)$subCategory->term_id) {
          $partialHtml          .= $this->getHeaderSubCategoriesView($subCategory, $category, $isFirstTab);
          $partialHtml          .= $this->getGroupBySubCategoriesView($package, $message, $phone);
          $currentSubCategoryId = (int)$subCategory->term_id;
          $isFirstTab           = false;
          continue;
        }

        $partialHtml .= $this->getGroupBySubCategoriesView($package, $message, $phone);
      }

      $partialHtml .= '</div></div></div></div>';
    }

    return sprintf(
      '<div class="accordion mb-3" id="accPackages">%s</div>',
      $partialHtml
    );
  }

  /**
   * Genera la vista de un paquete individual
   */
  private function getGroupBySubCategoriesView(object $package, string $message, string $phone): string
  {
    $postId            = (int)$package->ID;
    $titlePackage      = get_field('pac_title', $postId);
    $pricePackage      = (float)get_field('pac_price', $postId);
    $hasPrice          = $pricePackage > 0;
    $unitPackage       = get_field('pac_unit_sale', $postId);
    $contentPrice      = $hasPrice ?
      sprintf(
        '<p class="price mb-2">S/ %s</p><p class="mesure-unit">%s</p>',
        number_format($pricePackage, 2),
        esc_html($unitPackage)
      ) :
      "";
    $contentPackage    = wp_kses_post($package->post_content);
    $whatsAppUrl       = esc_url("https://api.whatsapp.com/send?phone=$phone&text=$message");
    $contactTextButton = __('Contactar', 'fronpe-settings');

    return <<<HTML
      <div class="col-md mb-2">
        <div class="package card text-center">
          <span class="card-header h4">$titlePackage</span>
          <div class="card-body">
            $contentPackage $contentPrice
            <a class="btn btn-outline-cta" href="$whatsAppUrl" target="_blank" rel="noopener noreferrer" role="button">
              $contactTextButton&ensp;<i class="fa-brands fa-whatsapp fa-xl"></i>
            </a>
          </div>
        </div>
      </div>
    HTML;
  }

  /**
   * Genera la cabecera para una subcategoría
   */
  private function getHeaderSubCategoriesView(object $subCategory, object $category, bool $isFirstTab): string
  {
    $subCategoryDescription = isset($category->name) ? ' - ' . esc_html($category->name) : '';
    $termId                 = (int)$subCategory->term_id;
    $isAreaExpanded         = $isFirstTab ? 'true' : 'false';
    $collapseClassName      = $isFirstTab ? 'show' : '';
    $subCategoryName        = isset($subCategory->name) ? esc_html($subCategory->name) : '';

    return <<<HTML
      <div class="card">
        <div class="card-header" id="heading$termId">
          <h3 class="mb-0">
            <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse$termId" aria-expanded="$isAreaExpanded" aria-controls="collapse$termId">
              $subCategoryName$subCategoryDescription
            </button>
          </h3>
        </div>
        <div id="collapse$termId" class="collapse $collapseClassName" aria-labelledby="heading%s" data-parent="#accPackages">
          <div class="card-body">
            <div class="packages row justify-content-center row-cols-md-3 mb-3">
    HTML;
  }
}

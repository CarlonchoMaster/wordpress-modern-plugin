<?php
declare(strict_types=1);

namespace Fronpe\Fronpe_Settings\Shared\Infrastructure\Services;

readonly class SeoService
{
  /**
   * Inicializa el servicio de SEO
   */
  public function init(): void
  {
    // Registrar filtros para SEO
    add_filter('rank_math/opengraph/facebook/og_locale', [$this, 'changeOgLocale']);

    // Aquí podrías agregar más filtros o acciones relacionadas con SEO
  }

  /**
   * Cambia el locale de OpenGraph para el plugin rank math
   *
   * @param string $content El locale original
   * @return string El locale personalizado
   */
  public function changeOgLocale(string $content): string
  {
    return 'es_PE';
  }
}

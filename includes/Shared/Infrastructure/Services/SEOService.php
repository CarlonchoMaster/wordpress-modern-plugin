<?php
declare(strict_types=1);

namespace Fronpe\Fronpe_Settings\Shared\Infrastructure\Services;

readonly class SEOService
{
  public function __construct(private LoggerService $logger)
  {
  }

  /**
   * Inicializa el servicio de SEO
   */
  public function init(): void
  {
    // Verificar si Rank Math está activo antes de registrar los filtros
    if ($this->isRankMathActive()) {
      // Registrar filtros para SEO de Rank Math
      add_filter('rank_math/opengraph/facebook/og_locale', [$this, 'changeOgLocale']);

      $this->logger->info('Rank Math SEO filtros registrados correctamente');
    }

    // Aquí podrías agregar más filtros o acciones relacionadas con SEO
  }

  /**
   * Cambia el locale de OpenGraph para el plugin rank math SEO
   *
   * @param string $content El locale original
   *
   * @return string El locale personalizado
   */
  public function changeOgLocale(string $content): string
  {
    return 'es_PE';
  }

  /**
   * Verifica si el plugin Rank Math SEO está activo
   *
   * @return bool Verdadero si el plugin está activado
   */
  private function isRankMathActive(): bool
  {
    // Método 1: Verificar si existe una función específica de Rank Math
    if (function_exists('rank_math')) {
      return true;
    }

    // Método 2: Verificar si existe una clase específica de Rank Math
    if (class_exists('RankMath')) {
      return true;
    }

    // Método 3: Verificar directamente con el API de plugins de WordPress
    if (!function_exists('is_plugin_active')) {
      include_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    return is_plugin_active('seo-by-rank-math/rank-math.php') ||
           is_plugin_active('seo-by-rank-math-pro/rank-math-pro.php');
  }
}

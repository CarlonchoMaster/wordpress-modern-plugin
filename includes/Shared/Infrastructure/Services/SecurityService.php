<?php
declare(strict_types=1);

namespace Fronpe\Fronpe_Settings\Shared\Infrastructure\Services;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_HTTP_Response;

/**
 * Servicio para manejar aspectos de seguridad del plugin
 */
readonly class SecurityService
{
  /**
   * Inicializa los hooks relacionados con seguridad
   */
  public function init(): void
  {
    // Restringir endpoints de la API REST para usuarios no autenticados
    add_filter('rest_request_before_callbacks', [$this, 'restrictRestApiForNonAuthUsers'], 10, 3);
  }

  /**
   * Restringe endpoints de la API REST solo para usuarios no autenticados
   */
  public function restrictRestApiForNonAuthUsers(
    WP_REST_Response|WP_HTTP_Response|WP_Error|null $response,
    array $handler,
    WP_REST_Request $request
  ): WP_HTTP_Response|null|WP_REST_Response|WP_Error {
    // Si ya hay un error o respuesta, devolvemos sin modificar
    if ($response !== null || is_wp_error($response)) {
      return $response;
    }

    // Si el usuario está autenticado, permitir acceso completo
    if (is_user_logged_in()) {
      return null;
    }

    // Obtener la ruta de la solicitud
    $route = $request->get_route();

    // Endpoints sensibles que queremos restringir para usuarios no autenticados
    $restrictedEndpoints = [
      '/wp/v2/users',
      '/wp/v2/plugins',
      '/rankmath/v1',
    ];

    // Endpoints críticos para el editor que siempre deberían estar disponibles
    // incluso para usuarios autenticados en el front-end (ej. para previews)
    $criticalEndpoints = [
      '/wp/v2/posts',
      '/wp/v2/pages',
      '/wp/v2/blocks',
      '/wp/v2/templates',
      '/wp/v2/media',
      '/oembed',
    ];

    // Verificar si la ruta actual contiene alguno de los endpoints restringidos
    foreach ($restrictedEndpoints as $endpoint) {
      if (str_starts_with($route, $endpoint)) {
        return new WP_Error(
          'rest_forbidden',
          __('Acceso restringido a la API para usuarios no autenticados.', 'fronpe-settings'),
          ['status' => 401]
        );
      }
    }

    // Para endpoints críticos del editor, permitimos solo operaciones de lectura (GET)
    // para usuarios no autenticados, como para vistas previas de contenido
    foreach ($criticalEndpoints as $endpoint) {
      if (str_starts_with($route, $endpoint)) {
        $method = $request->get_method();

        // Permitir solo operaciones GET para usuarios no autenticados
        if ($method !== 'GET') {
          return new WP_Error(
            'rest_forbidden_method',
            __('Método no permitido para usuarios no autenticados.', 'fronpe-settings'),
            ['status' => 401]
          );
        }
      }
    }

    return null;
  }
}

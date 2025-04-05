<?php
declare(strict_types=1);

namespace Fronpe\Fronpe_Settings\Shared\Infrastructure\Services;

use WP_Error;

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
    // Verificar autenticación en la API REST
    add_filter('rest_authentication_errors', [$this, 'verifyAuthInDashboard']);

    // Restringir endpoints de la API REST
    add_filter('rest_endpoints', [$this, 'removeRestApis']);
  }

  /**
   * Verifica que el usuario esté autenticado para acceder a la API REST
   *
   * @param mixed $errors Errores existentes o null
   * @return mixed WP_Error Si hay problemas de autenticación, o el valor original
   */
  public function verifyAuthInDashboard($errors)
  {
    if (!empty($errors)) {
      return $errors;
    }

    if (!is_user_logged_in()) {
      return new WP_Error(
        'rest_not_logged_in',
        __('You are not logged in.', 'fronpe-settings'),
        ['status' => 401]
      );
    }

    return $errors;
  }

  /**
   * Elimina endpoints específicos de la API REST por seguridad
   *
   * @param array $endpoints Lista de endpoints disponibles
   * @return array Lista filtrada de endpoints
   */
  public function removeRestApis(array $endpoints): array
  {
    // Endpoints sensibles que queremos restringir
    $restrictedEndpoints = [
      '/wp/v2/users',
      '/wp/v2/posts',
      '/wp/v2/pages',
      '/wp/v2/plugins',
      '/rankmath/v1',
      '/oembed'
    ];

    // Eliminar cada endpoint restringido si existe
    foreach ($restrictedEndpoints as $endpoint) {
      if (isset($endpoints[$endpoint])) {
        unset($endpoints[$endpoint]);
      }
    }

    return $endpoints;
  }
}

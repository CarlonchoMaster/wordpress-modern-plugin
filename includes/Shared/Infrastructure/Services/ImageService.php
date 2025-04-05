<?php
declare(strict_types=1);

namespace Fronpe\Fronpe_Settings\Shared\Infrastructure\Services;

class ImageService
{
  public function init(): void
  {
    add_action('intermediate_image_sizes_advanced', [$this, 'removeSizeImage']);
  }

  /**
   * Elimina tamaños de imagen predeterminados
   *
   * @param array $sizes Tamaños de imagen
   * @return array
   */
  public function removeSizeImage(array $sizes): array
  {
    unset($sizes['medium_large']);
    unset($sizes['1536x1536']);
    unset($sizes['2048x2048']);

    return $sizes;
  }
}

<?php
declare(strict_types=1);

namespace Fronpe\Fronpe_Settings\Shared\Domain\Models;

readonly class AssetFile
{
  public function __construct(
    private string $name,
    private string $assetFileName,
    private bool $isAdmin
  ) {
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function getAssetFileName(): string
  {
    return $this->assetFileName;
  }

  public function isAdmin(): bool
  {
    return $this->isAdmin;
  }


}

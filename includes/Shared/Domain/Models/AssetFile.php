<?php

namespace Fronpe\Fronpe_Settings\Shared\Domain\Models;

class AssetFile
{
  public function __construct(
    private string $name,
    private string $assetFileName,
    private bool $isAdmin
  ) {
  }

  public function get_name(): string
  {
    return $this->name;
  }

  public function get_asset_file_name(): string
  {
    return $this->assetFileName;
  }

  public function isAdmin(): bool
  {
    return $this->isAdmin;
  }


}

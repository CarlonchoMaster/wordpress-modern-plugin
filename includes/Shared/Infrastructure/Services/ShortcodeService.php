<?php
declare(strict_types=1);

namespace Fronpe\Fronpe_Settings\Shared\Infrastructure\Services;

use Fronpe\Fronpe_Settings\Shared\Infrastructure\Shortcodes\MainSliderShortcode;
use Fronpe\Fronpe_Settings\Shared\Infrastructure\Shortcodes\PackagesByPageShortcode;
use Fronpe\Fronpe_Settings\Shared\Infrastructure\Shortcodes\PageClusterShortcode;

readonly class ShortcodeService
{
  public function __construct(
    private MainSliderShortcode $mainSliderShortcode,
    private PackagesByPageShortcode $packagesByPageShortcode,
    private PageClusterShortcode $pageClusterShortcode,
  ) {
  }

  public function init(): void
  {
    $this->mainSliderShortcode->init();
    $this->packagesByPageShortcode->init();
    $this->pageClusterShortcode->init();
  }
}

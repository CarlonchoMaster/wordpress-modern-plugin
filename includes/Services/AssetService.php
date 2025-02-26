<?php

namespace Frontend100p\Frontend100p_Settings\Services;

class AssetService
{
  public function enqueue_style(string $styleName, bool $isAdmin, string $styleSheetName): void
  {
    $partialPath = $isAdmin ? 'admin/css' : 'public/css';
    $fullPath    = plugin_dir_url($this->entry_point) . 'assets/' . $partialPath . $styleSheetName;

    wp_enqueue_style($styleName, $fullPath, [], $this->getVersion());
  }

  public function enqueue_script(string $scriptName, bool $isAdmin, string $scriptFileName, array $deps = []): void
  {
    $partialPath = $isAdmin ? 'admin/js' : 'public/js';
    $fullPath    = plugin_dir_url($this->entry_point) . 'assets/' . $partialPath . $scriptFileName;

    wp_enqueue_script($scriptName, $fullPath, $deps, $this->getVersion(), false);
  }
}

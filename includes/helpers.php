<?php
namespace Frontend100p\Frontend100p_Settings;

use Frontend100p\Frontend100p_Settings\Services\DIContainerService;

function get_container(): DIContainerService
{
  return AppContainer::get();
}

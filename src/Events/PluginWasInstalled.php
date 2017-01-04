<?php

namespace GPlane\PluginsMarket\Events;

use App\Events\Event;
use App\Services\Plugin;

class PluginWasInstalled extends Event
{
    public $plugin;

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }
}

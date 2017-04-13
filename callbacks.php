<?php

return [
    App\Events\PluginWasEnabled::class => function (App\Services\PluginManager $manager) {
        if ($manager->getPlugin('plugins-market')) {
            $manager->uninstall('plugins-market');
        }
    },
    GPlane\PluginsMarket\Events\PluginWasInstalled::class => function () {
        if (file_exists(plugin_assets('unofficial-plugins-market', 'views/config.tpl'))) {
            unlink(plugin_assets('unofficial-plugins-market', 'views/config.tpl'));
        }
        if (file_exists(plugin_assets('unofficial-plugins-market', 'views/market.tpl'))) {
            unlink(plugin_assets('unofficial-plugins-market', 'views/market.tpl'));
        }
    }
];

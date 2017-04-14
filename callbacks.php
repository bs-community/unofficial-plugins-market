<?php

return [
    App\Events\PluginWasEnabled::class => function (App\Services\PluginManager $manager) {
        if ($manager->getPlugin('plugins-market')) {
            $manager->uninstall('plugins-market');
        }
    },
    'PluginWasInstalled' => function () {
        if (file_exists($path = base_path('plugins/unofficial-plugins-market/views/config.tpl'))) {
            unlink($path);
        }
        if (file_exists($path = base_path('plugins/unofficial-plugins-market/views/market.tpl'))) {
            unlink($path);
        }
    }
];

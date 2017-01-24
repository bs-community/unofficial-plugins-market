<?php

return [
    App\Events\PluginWasEnabled::class => function (App\Services\PluginManager $manager) {
        $manager->uninstall('plugins-market');
    }
];

<?php

use App\Services\Hook;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {

    Hook::registerPluginTransScripts('plugins-market');

    //Determine to if replace default market of Blessing Skin Server
    if (option('replace_default_market')) {
        Hook::addRoute(function ($routers) {
            $routers->get('admin/plugins/market', 'GPlane\PluginsMarket\MarketController@show')->middleware(['web', 'admin']);
        });
    } else {
        Hook::addMenuItem('admin', 4, [
            'title' => 'GPlane\PluginsMarket::general.name',
            'link'  => 'admin/plugins-market',
            'icon'  => 'fa-shopping-bag'
        ]);
    }

    Hook::addRoute(function ($routers) {

        $routers->group(['middleware' => ['web', 'admin'],
                        'namespace'  => 'GPlane\PluginsMarket',
                        'prefix'     => 'admin/plugins-market'],
                        function ($router) {
                            $router->get('/', 'MarketController@show');
                            $router->get('/data', 'MarketController@ajaxPluginList');
                            $router->post('/download', 'MarketController@downloadPlugin');
                        });
    });
};
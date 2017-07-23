<?php

use App\Services\Hook;
use Illuminate\Http\Request;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events, Request $request) {

    Hook::registerPluginTransScripts('unofficial-plugins-market');

    if ($request->is('admin/*') || $request->is('admin')) {
        $events->listen(App\Events\RenderingFooter::class, function ($event)
        {
            $event->addContent(
                '<script src="'.plugin_assets('unofficial-plugins-market', 'assets/js/dist/check.js').'"></script>'
            );
        });
    }

    //Determine to if replace default market of Blessing Skin Server
    if (option('replace_default_market')) {
        Hook::addRoute(function ($routers) {
            $routers->get('admin/plugins/market', 'GPlane\PluginsMarket\Controllers\MarketController@show')->middleware(['web', 'admin']);
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
                        'namespace'  => 'GPlane\PluginsMarket\Controllers',
                        'prefix'     => 'admin/plugins-market'],
                        function ($router) {
                            $router->get('/', 'MarketController@show');
                            $router->get('/data', 'MarketController@ajaxPluginList');
                            $router->get('/check', 'PluginController@updateCheck');
                            $router->post('/download', 'PluginController@downloadPlugin');
                        });

    });
};

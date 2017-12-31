<?php

namespace GPlane\PluginsMarket\Controllers;

use Option;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exceptions\PrettyPageException;

class MarketController extends Controller
{
    public function show()
    {
        // Remove old view files
        if (file_exists($path = base_path('plugins/unofficial-plugins-market/views/config.tpl'))) {
            unlink($path);
        }
        if (file_exists($path = base_path('plugins/unofficial-plugins-market/views/market.tpl'))) {
            unlink($path);
        }
        
        return view('GPlane\PluginsMarket::market');
    }

    public function ajaxPluginList()
    {
        $installed_plugins_version_list = self::loadInstalledPluginList();
        $raw_list = self::getPluginList();
        if (empty($raw_list)) {
            return response()->json(array('recordsTotal' => 0, 'data' => array()));
        }
        $plugins_list = array();
        $plugin_name_list = array_keys($raw_list);
        foreach ($plugin_name_list as $plugin_name) {
            $each_plugin = self::getSinglePluginInfo($raw_list[$plugin_name]);
            if (!$each_plugin) {
                continue;
            } else {
                $version_status = '';
                if (
                    (!empty($raw_list[$plugin_name]['isPreview']) && $raw_list[$plugin_name]['isPreview']) ||
                    (stripos(end($each_plugin['version']), 'rc') > 0) ||
                    (stripos(end($each_plugin['version']), 'beta') > 0) ||
                    (stripos(end($each_plugin['version']), 'alpha') > 0)) {
                    $version_status = 'preview';
                } elseif (!empty($installed_plugins_version_list[$each_plugin['name']]) && version_compare(end($each_plugin['version']), $installed_plugins_version_list[$each_plugin['name']]) == 1) {
                    $version_status = 'new';
                }
                $each_plugin['versionStatus'] = $version_status;
                $plugins_list[] = $each_plugin;
            }
        }
        return response()->json($plugins_list);
    }

    public static function loadInstalledPluginList()
    {
        $version_list = array();
        $resource = opendir(base_path('plugins'));
        while ($file_name = @readdir($resource)) {
            if ($file_name == '.' || $file_name == '..')
                continue;
            $plugin_path = base_path('plugins').'/'.$file_name;
            if (is_dir($plugin_path) && file_exists($plugin_path.'/package.json')) {
                $plugin_info = json_decode(file_get_contents($plugin_path.'/package.json'), true);
                $version_list[$plugin_info['name']] = $plugin_info['version'];
            }
        }
        closedir($resource);
        return $version_list;
    }

    private static function getPluginList()
    {
        if (empty(option('market_source'))) {
            //A source maintained by me
            Option::set('market_source', 'https://raw.githubusercontent.com/g-plane/plugins-market-data/master/plugins.json');
        }
        $market_source_path = option('market_source');
        $json_content = '';
        try {
            $json_content = file_get_contents($market_source_path);
        } catch (\Exception $e) {
            return null;
        }
        return json_decode($json_content, true);
    }

    private static function getSinglePluginInfo($plugin)
    {
        if (empty($plugin['name']) || empty($plugin['title']) || empty($plugin['author']) || empty($plugin['url']) || empty($plugin['version'])) {
            return false;
        } else {
            $versions = [];
            if (!empty($plugin['old'])) {
                $versions = array_keys($plugin['old']);
            }
            $versions[] = $plugin['version'];
            return array(
                'name'         =>  $plugin['name'],

                'title'        =>  $plugin['title'],

                'description'  =>  empty($plugin['description']) ? trans('GPlane\PluginsMarket::market.no-description') : $plugin['description'],

                'author'       =>  $plugin['author'],

                'version'      =>  $versions,

                'size'         =>  empty($plugin['size']) ? trans('GPlane\PluginsMarket::market.unknown') : [$plugin['size']],

                'brief'        =>  empty($plugin['brief']) ? '' : $plugin['brief']);
        }
    }
}

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
        $plugin_id_list = array_keys($raw_list);
        foreach ($plugin_id_list as $plugin_id) {
            $each_plugin = self::getSinglePluginInfo($raw_list[$plugin_id]);
            if (!$each_plugin) {
                continue;
            } else {
                $version_btn_class = '';
                $version_status_text = '';
                if (
                    (!empty($raw_list[$plugin_id]['isPreview']) && $raw_list[$plugin_id]['isPreview']) ||
                    (!empty($each_plugin['version']) && stripos($each_plugin['version'], 'rc') > 0) ||
                    (!empty($each_plugin['version']) && stripos($each_plugin['version'], 'beta') > 0) ||
                    (!empty($each_plugin['version']) && stripos($each_plugin['version'], 'alpha') > 0)) {
                    $version_btn_class = 'btn-warning';
                    $version_status_text = trans('GPlane\PluginsMarket::market.operations.version-pre');
                } elseif (version_compare($each_plugin['version'], $installed_plugins_version_list[$each_plugin['id']]) == 1) {
                    $version_btn_class = 'btn-success';
                    $version_status_text = trans('GPlane\PluginsMarket::market.operations.version-new');
                } else
                    $version_btn_class = 'btn-primary';
                $each_plugin['operations'] = sprintf($each_plugin['operations'], $version_btn_class, $version_status_text);
                $plugins_list[] = $each_plugin;
            }
        }
        $datatables_result = array(
            'recordsTotal'    => sizeof($plugins_list),
            'data'            => $plugins_list);
        return response()->json($datatables_result);
    }

    public static function loadInstalledPluginList()
    {
        $version_list = array();
        $resource = opendir(base_path('plugins'));
        while ($file_name = @readdir($resource)) {
            if ($file_name == '.' || $file_name == '..')
                continue;
            $plugin_path = base_path('plugins').'/'.$file_name;
            if (is_dir($plugin_path)) {
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
            Option::set('market_source', 'https://tpedutw-my.sharepoint.com/personal/gplane_tp_edu_tw/_layouts/15/guestaccess.aspx?guestaccesstoken=30xPBw1BGxF2CK8zgmE3ls4u5wJF51p6iW1EIW5jsW8%3d&docid=17414e47e4b494c75a732e75669cb87af&rev=1');
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
        if (empty($plugin['id']) || empty($plugin['display-name']) || empty($plugin['author']) || empty($plugin['url'])) {
            return false;
        } else {
            return array(
                'id'           =>  $plugin['id'],

                'display-name' =>  $plugin['display-name'],

                'description'  =>  empty($plugin['description']) ? trans('GPlane\PluginsMarket::market.no-description') : $plugin['description'],

                'author'       =>  $plugin['author'],

                'version'      =>  empty($plugin['version']) ? trans('GPlane\PluginsMarket::market.unknown') : $plugin['version'],

                'size'         =>  empty($plugin['size']) ? trans('GPlane\PluginsMarket::market.unknown') : $plugin['size'],

                'operations'   =>  '<input type="button" id="plugin_'.
                                    $plugin['id'].
                                    '" class="btn %s btn-sm" title="%s" onclick="readyToDownload(\''.
                                    $plugin['id'].
                                    '\', \''.$plugin['display-name'].'\');" value="'.
                                    trans('GPlane\PluginsMarket::market.operations.download').
                                    '" /><a class="btn btn-default btn-sm" href="'.
                                    (empty($plugin['brief']) ? '' : $plugin['brief']).
                                    '" target="_blank" title="'.trans('GPlane\PluginsMarket::market.operations.brief-hint').'">'.
                                    trans('GPlane\PluginsMarket::market.operations.check-brief').
                                    '</a>'
                                    );
        }
    }
}

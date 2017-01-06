<?php

namespace GPlane\PluginsMarket\Controllers;

use Utils;
use ZipArchive;
use App\Services\Storage;
use Illuminate\Http\Request;
use App\Services\PluginManager;
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
            $eachPlugin = self::getSinglePluginInfo($raw_list[$plugin_id]);
            if (!$eachPlugin) {
                continue;
            } else {
                $version_btn_class = '';
                $version_status_text = '';
                if (
                    (!empty($raw_list[$plugin_id]['isPreview']) && $raw_list[$plugin_id]['isPreview']) ||
                    (!empty($eachPlugin['version']) && stripos($eachPlugin['version'], 'rc') > 0) ||
                    (!empty($eachPlugin['version']) && stripos($eachPlugin['version'], 'beta') > 0) ||
                    (!empty($eachPlugin['version']) && stripos($eachPlugin['version'], 'alpha') > 0)) {
                    $version_btn_class = 'btn-warning';
                    $version_status_text = trans('GPlane\PluginsMarket::market.operations.version-pre');
                } elseif (version_compare($eachPlugin['version'], $installed_plugins_version_list[$eachPlugin['id']]) == 1) {
                    $version_btn_class = 'btn-success';
                    $version_status_text = trans('GPlane\PluginsMarket::market.operations.version-new');
                } else
                    $version_btn_class = 'btn-primary';
                $eachPlugin['operations'] = sprintf($eachPlugin['operations'], $version_btn_class, $version_status_text);
                $plugins_list[] = $eachPlugin;
            }
        }
        $datatables_result = array(
            'recordsTotal'    => sizeof($plugins_list),
            'data'            => $plugins_list);
        return response()->json($datatables_result);
    }

    public function downloadPlugin(Request $request)
    {
        $id = '';
        if ($request->has('id')) {
            $id = $request->input('id');
        } else {
            return response()->json(array('code' => -1, 'message' => 'Permission Denied.'));
        }

        //Prepare download
        $tmp_dir = storage_path('plugin-download-temp');
        $tmp_path = $tmp_dir.'/tmp_'.$id.'.zip';
        if (!is_dir($tmp_dir)) {
            if (false === mkdir($tmp_dir)) {
                return response()->json(array('code' => 1, 'message' => 'Write Permission Denied.'));
            }
        }

        //Gather URL
        $temp_list = self::getPluginList();
        $url = $temp_list[$id]['url'];

        //Connection check
        if (!$fp = @fopen($url, 'rb')) {
            return response()->json(array('code' => 2, 'message' => 'Connection error.'));
        }

        //Start to download
        try {
            Utils::download($url, $tmp_path);
        } catch (\Exception $e) {
            Storage::removeDir($tmp_dir);
            return response()->json(array('code' => 3, 'message' => 'Download error.'));
        }

        //Unzip file
        $zip = new ZipArchive();
        $res = $zip->open($tmp_path);
        if ($res === true) {
            try {
                $zip->extractTo('./plugins');
            } catch (\Exception $e) {
                $zip->close();
                Storage::removeDir($tmp_dir);
                return response()->json(array('code' => 4, 'message' => 'Extract zip error.'));
            }
        } else {
            $zip->close();
            Storage::removeDir($tmp_dir);
            return response()->json(array('code' => 4, 'message' => 'Extract zip error.'));
        }
        $zip->close();

        //Complete
        Storage::removeDir($tmp_dir);
        return response()->json(array('code' => 0, 'enable' => option('auto_enable_plugin')));
    }

    public function firstRunPlugin(Request $request, PluginManager $plugins)
    {
        if (!$request->has('id')) {
            return;
        }
        $id = $request->input('id');
        $plugin = $plugins->getPlugin($id);
        event(new \GPlane\PluginsMarket\Events\PluginWasInstalled($plugin));
    }

    private static function getPluginList()
    {
        $market_source_path = option('market_source');
        if (empty($market_source_path)) {
            //A source maintained by me
            $market_source_path = 'https://tpedutw-my.sharepoint.com/personal/gplane_tp_edu_tw/_layouts/15/guestaccess.aspx?guestaccesstoken=30xPBw1BGxF2CK8zgmE3ls4u5wJF51p6iW1EIW5jsW8%3d&docid=17414e47e4b494c75a732e75669cb87af&rev=1';
        }
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

    private static function loadInstalledPluginList()
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
}

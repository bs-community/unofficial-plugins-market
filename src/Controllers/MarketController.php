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
        $temp_list = self::getPluginList();
        if (empty($temp_list)) {
            return response()->json(array('recordsTotal' => 0, 'data' => array()));
        }
        $plugins_list = array();
        foreach ($temp_list as $plugin) {
            $eachPlugin = self::getSinglePluginInfo($plugin);
            if (!$eachPlugin) {
                continue;
            } else {
                $plugins_list[] = $eachPlugin;
            }
        }
        $result = array(
            'recordsTotal'    => sizeof($plugins_list),
            'data'            => $plugins_list);
        return response()->json($result);
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
        $url = '';
        $temp_list = self::getPluginList();
        foreach ($temp_list as $plugin) {
            if (!empty($plugin['id']) && $plugin['id'] == $id) {
                $url = $plugin['url'];
            }
        }

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
                'id'         =>  $plugin['id'],

                'display-name' =>  $plugin['display-name'],

                'description'  =>  empty($plugin['description']) ? trans('GPlane\PluginsMarket::market.no-description') : $plugin['description'],

                'author'       =>  $plugin['author'],

                'version'      =>  empty($plugin['version']) ? trans('GPlane\PluginsMarket::market.unknown') : $plugin['version'],

                'size'         =>  empty($plugin['size']) ? trans('GPlane\PluginsMarket::market.unknown') : $plugin['size'],

                'operations'   =>  '<input type="button" id="plugin-'.
                                    $plugin['id'].
                                    '" class="btn btn-primary btn-sm" onclick="readyToDownload(\''.
                                    $plugin['id'].
                                    '\', \''.$plugin['display-name'].'\', '.
                                    (!empty($plugin['isPreview']) ? $plugin['isPreview'] : 0).
                                    ');" value="'.
                                    trans('GPlane\PluginsMarket::market.operations.download').
                                    '" /><a class="btn btn-warning btn-sm" href="'.
                                    (empty($plugin['brief']) ? '' : $plugin['brief']).
                                    '" target="_blank" title="'.trans('GPlane\PluginsMarket::market.operations.brief-hint').'">'.
                                    trans('GPlane\PluginsMarket::market.operations.check-brief').
                                    '</a>'
                                    );
        }
    }
}

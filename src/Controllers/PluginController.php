<?php

namespace GPlane\PluginsMarket\Controllers;

use Utils;
use Option;
use ZipArchive;
use App\Services\Storage;
use Illuminate\Http\Request;
use App\Services\PluginManager;
use App\Http\Controllers\Controller;
use App\Exceptions\PrettyPageException;

class PluginController extends Controller
{
    public function downloadPlugin(Request $request)
    {
        $name = '';
        if ($request->has('name')) {
            $name = $request->input('name');
        } else {
            return response()->json(['code' => -1]);
        }

        //Prepare download
        $tmp_dir = storage_path('plugin-download-temp');
        $tmp_path = $tmp_dir.'/tmp_'.$name.'.zip';
        if (!is_dir($tmp_dir)) {
            if (false === mkdir($tmp_dir)) {
                return response()->json(['code' => 1]);
            }
        }

        //Gather URL
        $market_source_path = option('market_source');
        $json_content = '';
        try {
            $json_content = file_get_contents($market_source_path);
        } catch (\Exception $e) {
            return response()->json(['code' => 2]);
        }
        $temp_list = json_decode($json_content, true);
        $url = '';
        if ($request->version == $temp_list[$name]['version'])
            $url = $temp_list[$name]['url'];
        else
            $url = $temp_list[$name]['old'][$request->version];

        //Connection check
        if (!$fp = @fopen($url, 'rb')) {
            return response()->json(['code' => 2]);
        }

        //Start to download
        try {
            Utils::download($url, $tmp_path);
        } catch (\Exception $e) {
            Storage::removeDir($tmp_dir);
            return response()->json(['code' => 3]);
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
                return response()->json(['code' => 4]);
            }
        } else {
            $zip->close();
            Storage::removeDir($tmp_dir);
            return response()->json(['code' => 4]);
        }
        $zip->close();

        //Complete
        Storage::removeDir($tmp_dir);
        return response()->json(array('code' => 0, 'enable' => option('auto_enable_plugin')));
    }

    public function firstRunPlugin(Request $request, PluginManager $plugins)
    {
        if (!$request->has('name')) {
            return;
        }
        $name = $request->input('name');
        $plugin = $plugins->getPlugin($name);
        event(new \GPlane\PluginsMarket\Events\PluginWasInstalled($plugin));
    }

    public function updateCheck()
    {
        if (empty(option('plugin_update_notification')))
            Option::set('plugin_update_notification', 'release_only');
        $notification = option('plugin_update_notification');
        if ($notification == 'none')
            return;
        $new_version_count = array('release' => 0, 'pre' => 0);
        $installed_plugins_version_list = MarketController::loadInstalledPluginList();
        $market_plugins_version_list = array();
        $update_list = array();
        if (empty(option('market_source'))) {
            //A source maintained by me
            Option::set('market_source', 'https://plugin.sealfu.cf/plugin.json');
        }
        $market_source_path = option('market_source');
        $json_content = '';
        try {
            $json_content = file_get_contents($market_source_path);
        } catch (\Exception $e) {
            exit(0);
        }
        $market_plugins_list = json_decode($json_content, true);
        foreach ($installed_plugins_version_list as $name => $current_version) {
            if (empty($market_plugins_list[$name]['version']))
                continue;
            if ((!empty($market_plugins_list[$name]['isPreview']) && $market_plugins_list[$name]['isPreview']) ||
                stripos($market_plugins_list[$name]['version'], 'rc') > 0 ||
                stripos($market_plugins_list[$name]['version'], 'beta') > 0 ||
                stripos($market_plugins_list[$name]['version'], 'alpha') > 0) {
                    $new_version_count['pre']++;
            } elseif (version_compare($market_plugins_list[$name]['version'], $current_version) == 1)
                $new_version_count['release']++;
        }
        if ($notification == 'release_only') {
            $new_version_count['pre'] = 0;
        }
        $market_link = url('admin/plugins-market');
        if (option('replace_default_market')) {
            $market_link = url('admin/plugins/market');
        }
        return response()->json(array('url' => $market_link, 'count' => $new_version_count));
    }
}

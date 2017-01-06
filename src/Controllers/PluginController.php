<?php

namespace GPlane\PluginsMarket\Controllers;

use Utils;
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
        $market_source_path = option('market_source');
        $json_content = '';
        try {
            $json_content = file_get_contents($market_source_path);
        } catch (\Exception $e) {
            return response()->json(array('code' => 2, 'message' => 'Connection error.'));
        }
        $temp_list = json_decode($json_content, true);
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
}

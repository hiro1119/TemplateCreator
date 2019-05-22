<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DOMDocument;
use Maatwebsite\Excel\Facades\Excel;

class HomeController extends Controller
{
    protected $folder;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        // プロジェクト情報取得
        $this->folder = base_path().'/project_info.xml';
    }

    /**
     * トップページ
     */
    public function index()
    {
        // プロジェクト情報取得
        $sites = $this->getSiteInfos();

        return view('dashbord', compact('sites'));
    }

    /**
     * プロジェクト作成処理
     */
    public function createProject(Request $request)
    {
        // プロジェクトフォーム情報取得
        $p = $request->input('project');

        // ファイルネーム初期化
        $fileNames = array();
        // プロジェクトフォルダパス
        $distfolder = base_path().'/dist/'.$p['path'];
        // フォルダ判定
        if (!\File::exists($distfolder)) {
            // フォルダがない場合
            // テンプレートフォルダ作成
            \File::makeDirectory($distfolder.'/templates', 0777, true);
            // モジュールフォルダ作成
            \File::makeDirectory($distfolder.'/modules', 0777, true);
            // サイト情報フォルダ作成
            \File::makeDirectory($distfolder.'/site_infos', 0777, true);
            // パブリッシュフォルダ作成
            \File::makeDirectory($distfolder.'/publish', 0777, true);
        }

        // テンプレート判定
        if ($request->hasFile('templates')) {
            // テンプレートがフォーム情報に含まれている場合
            // テンプレート情報取得
            $files = $request->file('templates');
            // テンプレートフォルダ
            $path = $distfolder.'/templates';
            // サイトフォルダ
            $site_info_path = $distfolder.'/site_infos';
            // テンプレート情報をループ
            foreach ($files as $file) {
                // テンプレートファイル名を配列に追加
                array_push($fileNames, $file->getClientOriginalName());
                // テンプレートフォルダにテンプレートファイルをアップロード
                move_uploaded_file($file->getRealPath(), $distfolder.'/templates'.'/'.$file->getClientOriginalName());
            }
        } else {
            \Session::flash('error', 'プロジェクトの登録が成功しました。');
            return;
        }

        // サイト情報のテンプレートをコピーして移動
        $success = \File::copy(base_path('site_info_temp.xlsx'), $site_info_path.'/site_info.xlsx');
        $siteInfos = $this->getSiteInfos();

        $addInfo['name'] = $p['name'];
        $addInfo['path'] = $p['path'];
        $addInfo['outpath'] = $p['outpath'];
        $addInfo['temps']['temp-name'] = array();
        foreach ($fileNames as $fn) {
            array_push($addInfo['temps']['temp-name'], $fn);
        }

        array_push($siteInfos['info'], $addInfo);
        $dom = new DOMDocument('1.0', 'UTF-8');
        // appendChildでconditions属性作成
        $sites = $dom->appendChild($dom->createElement('sites'));
        //$conditionsに対してappendChildしてcondition属性を作成
        foreach ($siteInfos['info'] as $siteInfo) {
            $info = $sites->appendChild($dom->createElement('info'));

            $info->appendChild($dom->createElement('id', $siteInfo['path'].'1'));
            $info->appendChild($dom->createElement('name', $siteInfo['name']));
            $info->appendChild($dom->createElement('path', $siteInfo['path']));
            $info->appendChild($dom->createElement('outpath', $siteInfo['outpath']));
            $temps = $info->appendChild($dom->createElement('temps'));
            if (!is_array($siteInfo['temps']['temp-name'])) {
                $siteInfo['temps']['temp-name'] = array($siteInfo['temps']['temp-name']);
            }
            foreach ($siteInfo['temps']['temp-name'] as $key => $value) {
                $temps->appendChild($dom->createElement('temp-name', $value));
            }
        }

        //XMLを整形してから出力処理
        $dom->formatOutput = true;
        //保存
        $dom->save('../project_info.xml');

        \Session::flash('message', 'プロジェクトの登録が成功しました。');

        return redirect('/');
    }

    /**
     * プロジェクト詳細表示
     */
    public function projectInfo(Request $request, $id)
    {
        $sites = $this->getSiteInfos();

        $data;
        foreach ($sites['info'] as $info) {
            if ($id === $info['id']) {
                if (!is_array($info['temps']['temp-name'])) {
                    $info['temps']['temp-name'] = array($info['temps']['temp-name']);
                }
                $data = $info;
            }
        }

        // サイト情報エクセル取得
        $site_infos = $this->getSitemap($data);

        if (count($site_infos) > 0) {
            $head = $site_infos[0];
            $headers = array_keys($head);
        } else {
            $headers = array();
        }
        return view('detail', compact('data', 'site_infos', 'headers', 'id'));
    }

    /**
     * プロジェクト出力処理
     */
    public function outPutFiles(Request $request, $id)
    {
        $sites = $this->getSiteInfos();

        $data;
        foreach ($sites['info'] as $info) {
            if ($id === $info['id']) {
                $data = $info;
            }
        }

        $site_infos = $this->getSitemap($data);

        $tempFolder = base_path().'/dist/'.$data['path'].'/templates/';
        $headers = array();
        $cnt = 0;
        $ms = array();
        foreach ($site_infos as $k => $s) {
            if(!empty($s['out']) || $s['out'] === '1'){
                Log::debug(print_r('吐き出し済み', true));
                continue;
            }
            if(empty($s['mod1'])){
                Log::debug(print_r('モジュール指定なし', true));
                continue;
            }
            $pages = array();
            if (count($headers) === 0) {
                $headers = array_keys($s);
                foreach ($headers as $h) {
                    if (\preg_match('/mod/', $h)) {
                        // $cnt++;
                        array_push($ms, $h);
                    }
                }
            }

            if (empty($s['path'])) {
                Log::debug(print_r('URLなし', true));
                continue;
            }
            $tempFile = $tempFolder.$s['layout'];

            if (file_exists($tempFile)) {
                $feed = file($tempFile);
                foreach ($feed as $col) {
                    $f = trim($col);
                    if (preg_match('<!-- title -->', $f)) {
                        $col = str_replace('<!-- title -->', $s['title'], $col);
                        array_push($pages, $col);
                    } elseif (preg_match('<!-- description -->', $f)) {
                        $col = str_replace('<!-- description -->', $s['description'], $col);
                        array_push($pages, $col);
                    } elseif (preg_match('<!-- keywords -->', $f)) {
                        $col = str_replace('<!-- keywords -->', $s['keywords'], $col);
                        array_push($pages, $col);
                    } elseif (preg_match('<!-- og:title -->', $f)) {
                        $col = str_replace('<!-- og:title -->', $s['ogtitle'], $col);
                        array_push($pages, $col);
                    } elseif (preg_match('<!-- og:description -->', $f)) {
                        $col = str_replace('<!-- og:description -->', $s['ogdescription'], $col);
                        array_push($pages, $col);
                    } elseif (preg_match('<!-- og:site_name -->', $f)) {
                        $col = str_replace('<!-- og:site_name -->', $s['ogsite_name'], $col);
                        array_push($pages, $col);
                    } elseif (preg_match('<!-- og:type -->', $f)) {
                        $col = str_replace('<!-- og:type -->', $s['ogtype'], $col);
                        array_push($pages, $col);
                    } elseif (preg_match('<!-- og:url -->', $f)) {
                        $col = str_replace('<!-- og:url -->', $s['ogurl'], $col);
                        array_push($pages, $col);
                    } elseif (preg_match('<!-- og:image -->', $f)) {
                        $col = str_replace('<!-- og:image -->', $s['ogimage'], $col);
                        array_push($pages, $col);
                    } elseif (preg_match('<!-- title_h1 -->', $f)) {
                        $col = str_replace('<!-- title_h1 -->', $s['title_h1'], $col);
                        array_push($pages, $col);
                    } elseif (preg_match('<!-- contents -->', $f)) {
                        foreach ($ms as $l) {
                            if (isset($s[$l])) {
                                $modstr = str_replace("\'","\"",$s[$l]);
                                Log::debug(print_r($modstr, true));
                                $module_datas = json_decode($modstr, true);
                                Log::debug(print_r($module_datas, true));
                                $moduleId = $module_datas['id'];
                                if ($moduleId === 'HDG-1') {
                                    continue;
                                }
                                if (isset($module_datas['datas'])) {
                                    $moduleDatas = $module_datas['datas'];
                                } else {
                                    $moduleDatas = array();
                                }

                                $modurl = base_path().'/dist/'.$data['path'].'/modules/'.$moduleId.'.xml';
                                Log::debug(print_r('################## file #################'.$modurl, true));
                                if (file_exists($modurl)) {
                                    Log::debug(print_r('################## file あり #################', true));
                                    $mods = file($modurl);
                                    foreach ($mods as $m) {
                                        if (count($moduleDatas) > 0 && isset($moduleDatas[0]['data1'])) {
                                            $m = str_replace('<!-- data1 -->', $moduleDatas[0]['data1'], $m);
                                        }
                                        $p = str_replace(array("\r\n", "\r", "\n"), '', $m);
                                        array_push($pages, $p);
                                    }
                                } else {
                                    Log::debug(print_r('################## file_exists false #################', true));
                                }
                            }
                        }
                    } elseif (preg_match('<!-- breadcrumb -->', $f)) {
                        Log::debug(print_r('################## breadcrumb #################', true));
                        $panModule = base_path().'/dist/'.$data['path'].'/modules/PAN-1.xml';
                        $panModules = file($panModule);
                        $panLastModule = base_path().'/dist/'.$data['path'].'/modules/PAN-2.xml';
                        $panLastModules = file($panLastModule);
                        $i = 1;
                        for($i = 1;$i < 10;$i++){
                            Log::debug(print_r('################## breadcrumb_text #################', true));
                            if(!empty($s['breadcrumb_text_'.$i])){
                                Log::debug(print_r('################## breadcrumb_text_'.$i.' '.$s['breadcrumb_text_'.$i], true));
                                foreach ($panModules as $m) {
                                    $m = str_replace('<!-- pantext -->', $s['breadcrumb_text_'.$i], $m);
                                    $m = str_replace('<!-- panurl -->', $s['breadcrumb_href_'.$i], $m);
                                    $m = str_replace('<!-- pannumber -->', $i, $m);
                                    Log::debug(print_r('################## $panModules as $m #################', true));
                                    Log::debug(print_r($m, true));
                                    $col = str_replace(array("\r\n", "\r", "\n"), '', $m);
                                    array_push($pages, $col);
                                }
                            }
                        }
                        foreach ($panLastModules as $ml) {
                            $m = str_replace('<!-- pantext -->', $s['breadcrumb_text_last'], $ml);
                            $m = str_replace('<!-- pannumber -->', $i, $ml);
                            $col = str_replace(array("\r\n", "\r", "\n"), '', $ml);
                            array_push($pages, $col);
                        }
                    } else {
                        $col = str_replace(array("\r\n", "\r", "\n"), '', $col);
                        array_push($pages, $col);
                    }
                }
            } else {
                $sites = array('info'=>array());
            }
            $a = substr($s['path'], 0, mb_strrpos($s['path'], "/"));

            // $outputpath = base_path().'/dist/'.$data['path'].'/publish'.$a;
            $outputpath = pathinfo($data['outpath'].$s['path']);
            Log::debug(print_r($outputpath['dirname'], true));
            if (!\File::exists($outputpath['dirname'])) {
                \File::makeDirectory($outputpath['dirname'], 0777, true);
            }

            // 出力ファイルパス
            // $outputpath = base_path().'/dist/'.$data['path'].'/publish'.$s['path'];
            // 取得したデータをファイルに出力する
            $pages = implode(PHP_EOL, $pages);
            file_put_contents($data['outpath'].$s['path'], $pages);
        }

        // return view('detail', compact('data','site_infos','headers','id'));
        return redirect('/');
    }

    /**
     * モジュールアップロード処理
     */
    public function moduleUp(Request $request, $id)
    {
        $sites = $this->getSiteInfos();

        $data;
        foreach ($sites['info'] as $info) {
            if ($id === $info['id']) {
                $data = $info;
            }
        }


        $modFolder = base_path().'/dist/'.$data['path'].'/modules/';
        $modules = $request->input('modules');
        Log::debug(print_r($modules, true));
        foreach ($modules['id'] as $key => $id) {
            if (empty($id)) {
                continue;
            }
            $mod = $modules['module'][$key];
            $cr = array("\r\n", "\r");   // 改行コード置換用配列を作成しておく

            $mod = trim($mod);         // 文頭文末の空白を削除

            // 改行コードを統一
            //str_replace ("検索文字列", "置換え文字列", "対象文字列");
            $mod = str_replace($cr, "\n", $mod);

            //改行コードで分割（結果は配列に入る）
            $lines_array = explode("\n", $mod);
            Log::debug(print_r($lines_array, true));

            // 出力ファイルパス
            $outputpath = $modFolder.$id.'.xml';
            // 取得したデータをファイルに出力する
            file_put_contents($outputpath, $mod);
        }
        return redirect('/');
    }

    /**
     * モジュール追加画面表示
     */
    public function module(Request $request, $id)
    {
        return view('module', compact('id'));
    }

    /**
     * プロジェクト情報取得処理
     */
    private function getSiteInfos()
    {
        $siteInfos;
        if (\File::exists($this->folder)) {
            $feed = file_get_contents($this->folder);
            $sites = simplexml_load_string($feed);
            $json = json_encode($sites);
            $siteInfos = json_decode($json, true);
            if (array_values($siteInfos['info']) !== $siteInfos['info']) {
                $siteInfos['info'] = array($siteInfos['info']);
            }
        } else {
            $siteInfos = array('info'=>array());
        }

        return $siteInfos;
    }

    /**
     * サイト情報取得処理
     */
    private function getSitemap($data)
    {
        $reader = Excel::selectSheets('projects')->load(base_path().'/dist/'.$data['path'].'/site_infos/site_info.xlsx');
        if ($reader == null) {
            throw new \Exception('error.');
        }
        $sheet = $reader;

        $head;
        $site_infos = array();
        $headers = array();
        $cnt = 0;
        foreach ($sheet->all() as $key => $cells) {
            $cs = $cells->all();
            if (count($headers) === 0) {
                $headers = array_keys($cs);
                $mods = array();
                foreach ($headers as $h) {
                    if (\preg_match('/mod/', $h)) {
                        $cnt++;
                        array_push($mods, 'mod'.$cnt);
                    }
                }
                $headers['mods'] = $mods;
            }

            if (empty($cs['path'])) {
                continue;
            }

            $modules = array();
            for ($i = 1;$i <= $cnt;$i++) {
                array_push($modules, $cs['mod'.$i]);
            }
            $cs['mod_cnt'] = $cnt;

            array_push($site_infos, $cs);
        }

        return $site_infos;
    }

    public function getModules(Request $request, $id)
    {

        $sites = $this->getSiteInfos();

        $data;
        foreach ($sites['info'] as $info) {
            if ($id === $info['id']) {
                $data = $info;
            }
        }
        $modFolder = base_path().'/dist/'.$data['path'].'/modules/';
      //対象URL取得
        $urls = $_POST{'module-url'};

        $cr = array("\r\n", "\r");   // 改行コード置換用配列を作成しておく

        $urls = trim($urls);         // 文頭文末の空白を削除

        // 改行コードを統一
        //str_replace ("検索文字列", "置換え文字列", "対象文字列");
        $urls = str_replace($cr, "\n", $urls);

        //改行コードで分割（結果は配列に入る）
        $urls_array = explode("\n", $urls);
        Log::debug("URL 配列".count($urls_array));

        if (count($urls_array) > 0) {
            foreach($urls_array as $url){
                // HTMLソース取得
                $html = file_get_contents($url);
                if ($html != "") {
                    $dom = \phpQuery::newDocument($html);
                    foreach($dom['textarea'] as $te){
                        $modules = $request->input('modules');
                        Log::debug(print_r($modules, true));

                        // 出力ファイルパス
                        $outputpath = $modFolder.pq($te)->attr("id").'.xml';
                        // 取得したデータをファイルに出力する
                        file_put_contents($outputpath, pq($te)->html());
                    }
                    $info_msg = "ファイルの取得に成功しました";
                } else {
                    $info_msg = "ファイルの取得に失敗しました";
                }
            }
        } else {
            $info_msg = "取得対象URLを入力して下さい";
        }
        return redirect()->route('project.info', ['id' => $id]);
    }
}

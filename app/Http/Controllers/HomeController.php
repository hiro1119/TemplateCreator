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
    function __construct()
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
        if($request->hasFile('templates')){
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
        $success = \File::copy(base_path('site_info_temp.xlsx'),$site_info_path.'/site_info.xlsx');
        $siteInfos = $this->getSiteInfos();

        $addInfo['name'] = $p['name'];
        $addInfo['path'] = $p['path'];
        $addInfo['temps']['temp-name'] = array();
        foreach($fileNames as $fn){
            array_push($addInfo['temps']['temp-name'], $fn);
        }

        array_push($siteInfos['info'], $addInfo);
        $dom = new DOMDocument('1.0', 'UTF-8');
        // appendChildでconditions属性作成
        $sites = $dom->appendChild($dom->createElement('sites'));
        //$conditionsに対してappendChildしてcondition属性を作成
        foreach($siteInfos['info'] as $siteInfo){
            $info = $sites->appendChild($dom->createElement('info'));

            $info->appendChild($dom->createElement('id', $siteInfo['path'].'1'));
            $info->appendChild($dom->createElement('name', $siteInfo['name']));
            $info->appendChild($dom->createElement('path', $siteInfo['path']));
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
        foreach($sites['info'] as $info){
            if($id === $info['id']){
                if (!is_array($info['temps']['temp-name'])) {
                    $info['temps']['temp-name'] = array($info['temps']['temp-name']);
                }
                $data = $info;
            }
        }

        // サイト情報エクセル取得
        $site_infos = $this->getSitemap($data);

        if(count($site_infos) > 0){
            $head = $site_infos[0];
            $headers = array_keys($head);
        } else {
            $headers = array();
        }
        return view('detail', compact('data','site_infos','headers','id'));

    }

    /**
     * プロジェクト出力処理
     */
    public function outPutFiles(Request $request, $id)
    {

        $sites = $this->getSiteInfos();

        $data;
        foreach($sites['info'] as $info){
            if($id === $info['id']){
                $data = $info;
            }
        }

        $site_infos = $this->getSitemap($data);

        $tempFolder = base_path().'/dist/'.$data['path'].'/templates/';
        $headers = array();
        $cnt = 0;
        $ms = array();
        foreach($site_infos as $k => $s){
            $pages = array();
            if(count($headers) === 0){
                $headers = array_keys($s);
                foreach($headers as $h){
                    if(\preg_match('/mod/', $h)){
                        // $cnt++;
                        array_push($ms,$h);
                    }
                }
            }

            if(empty($s['path'])){
                Log::debug(print_r('URLなし',true));
                continue;
            }
            $tempFile = $tempFolder.$s['layout'];
            Log::debug(print_r('No: '.$k,true));
            Log::debug(print_r($tempFile,true));
            Log::debug(print_r(file_exists($tempFile),true));
            if (file_exists($tempFile)) {
                $feed = file($tempFile);
                foreach($feed as $col){
                    $f = trim($col);
                    if(preg_match('<!-- title -->',$f)){
                        $col = str_replace('<!-- title -->',$s['title'],$col);
                        array_push($pages,$col);
                    } else if(preg_match('<!-- description -->',$f)){
                        $col = str_replace('<!-- description -->',$s['description'],$col);
                        array_push($pages,$col);
                    } else if(preg_match('<!-- keywords -->',$f)){
                        $col = str_replace('<!-- keywords -->',$s['keywords'],$col);
                        array_push($pages,$col);
                    } else if(preg_match('<!-- og:title -->',$f)){
                        $col = str_replace('<!-- og:title -->',$s['ogtitle'],$col);
                        array_push($pages,$col);
                    } else if(preg_match('<!-- og:description -->',$f)){
                        $col = str_replace('<!-- og:description -->',$s['ogdescription'],$col);
                        array_push($pages,$col);
                    } else if(preg_match('<!-- og:site_name -->',$f)){
                        $col = str_replace('<!-- og:site_name -->',$s['ogsite_name'],$col);
                        array_push($pages,$col);
                    } else if(preg_match('<!-- og:type -->',$f)){
                        $col = str_replace('<!-- og:type -->',$s['ogtype'],$col);
                        array_push($pages,$col);
                    } else if(preg_match('<!-- og:url -->',$f)){
                        $col = str_replace('<!-- og:url -->',$s['ogurl'],$col);
                        array_push($pages,$col);
                    } else if(preg_match('<!-- og:image -->',$f)){
                        $col = str_replace('<!-- og:image -->',$s['ogimage'],$col);
                        array_push($pages,$col);
                    } else if(preg_match('<!-- title_h1 -->',$f)){
                        $col = str_replace('<!-- title_h1 -->',$s['title_h1'],$col);
                        array_push($pages,$col);
                    } else if(preg_match('<!-- contents -->',$f)){
                        Log::debug(print_r('################## contents #################',true));
                        Log::debug(print_r('################## $ms #################',true));
                        Log::debug(print_r($ms,true));
                        foreach($ms as $l){
                            Log::debug(print_r('################## foreach($ms as $l) #################',true));
                            Log::debug(print_r('################## $s[$l] #################'.$s[$l],true));
                            if(isset($s[$l])){
                                Log::debug(print_r('################## if(isset($s[$l])) #################',true));
                                $module_datas = json_decode($s[$l],true);
                                Log::debug(print_r('################## json_decode #################',true));
                                Log::debug(print_r($s[$l],true));
                                Log::debug(print_r($module_datas,true));

                                $moduleId = $module_datas['id'];
                                if(isset($module_datas['datas'])){
                                    $moduleDatas = $module_datas['datas'];
                                } else {
                                    $moduleDatas = array();
                                }
                                Log::debug(print_r('################## moduleDatas #################',true));
                                Log::debug(print_r($moduleDatas,true));
                                $modurl = base_path().'/dist/'.$data['path'].'/modules/'.$moduleId.'.xml';
                                if (file_exists($modurl)) {
                                    $mods = file($modurl);
                                    foreach ($mods as $m) {
                                        if(count($moduleDatas) > 0 && isset($moduleDatas[0]['data1'])){
                                            Log::debug(print_r('################## count($moduleDatas) > 0 && isset($moduleDatas #################',true));
                                            $m = str_replace('<!-- data1 -->', $moduleDatas[0]['data1'], $m);
                                        }
                                        $p = str_replace(array("\r\n", "\r", "\n"), '', $m);
                                        array_push($pages, $p);
                                        // if (preg_match('<!-- text -->', $m)) {
                                        //     switch (true) {
                                        //         case preg_match('/LINK/', $moduleId):
                                        //             Log::debug(print_r('################## LINK #################', true));
                                        //             $links = $module_datas['datas']['links'];

                                        //             foreach ($links as $link) {
                                        //                 Log::debug(print_r($link, true));
                                        //                 $p = str_replace('<!-- text -->', $link['text'], $m);
                                        //                 $p = str_replace('<!-- arrow-color -->', $link['arrow-color'], $p);
                                        //                 if ($link['type'] === 'target') {
                                        //                     $p = str_replace('<!-- target -->', 'target="_blank"', $p);
                                        //                 } elseif ($link['type'] === 'pdf') {
                                        //                     $p = str_replace('<!-- pdf -->', 'm-pdf', $p);
                                        //                     $p = str_replace('<!-- target -->', 'target="_blank"', $p);
                                        //                 } else {
                                        //                     $p = str_replace('<!-- pdf -->', '', $p);
                                        //                     $p = str_replace('<!-- target -->', '', $p);
                                        //                 }
                                        //                 $p = str_replace(array("\r\n", "\r", "\n"), '', $p);
                                        //                 array_push($pages, $p);
                                        //             }
                                        //             break;
                                        //         case preg_match('/LIST/', $moduleId):
                                        //             Log::debug(print_r('################## LIST #################', true));
                                        //             break;
                                        //         case preg_match('/TXT/', $moduleId):
                                        //             Log::debug(print_r('################## TXT #################', true));
                                        //             break;
                                        //         case preg_match('/HDG/', $moduleId):
                                        //             Log::debug(print_r('################## HDG #################', true));
                                        //             break;
                                        //         case preg_match('/ANC/', $moduleId):
                                        //             Log::debug(print_r('################## ANC #################', true));
                                        //             break;
                                        //         case preg_match('/BOX/', $moduleId):
                                        //             Log::debug(print_r('################## BOX #################', true));
                                        //             break;
                                        //         case preg_match('/BTN/', $moduleId):
                                        //             Log::debug(print_r('################## BTN #################', true));
                                        //             break;
                                        //         case preg_match('/FT/', $moduleId):
                                        //             Log::debug(print_r('################## BTN #################', true));
                                        //             break;
                                        //         case preg_match('/MED/', $moduleId):
                                        //             Log::debug(print_r('################## BTN #################', true));
                                        //             break;
                                        //         case preg_match('/QA/', $moduleId):
                                        //             Log::debug(print_r('################## BTN #################', true));
                                        //             break;
                                        //         case preg_match('/TBL/', $moduleId):
                                        //             Log::debug(print_r('################## BTN #################', true));
                                        //             break;
                                        //     }
                                        // } else {
                                        //     $p = str_replace(array("\r\n", "\r", "\n"), '', $m);
                                        //     array_push($pages, $m);
                                        // }
                                    }
                                } else {
                                    Log::debug(print_r('################## file_exists false #################',true));
                                }
                            }
                        }
                    } else {
                        $col = str_replace(array("\r\n", "\r", "\n"), '', $col);
                        array_push($pages,$col);
                    }
                }

            } else {
                $sites = array('info'=>array());
            }
            $a = substr($s['path'],0,mb_strrpos($s['path'], "/"));

            $outputpath = base_path().'/dist/'.$data['path'].'/publish'.$a;
            if (!\File::exists($outputpath)) {
                \File::makeDirectory($outputpath, 0777, true);
            }

            // 出力ファイルパス
            $outputpath = base_path().'/dist/'.$data['path'].'/publish'.$s['path'];
            // 取得したデータをファイルに出力する
            $pages = implode(PHP_EOL, $pages);
            file_put_contents($outputpath, $pages);
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
        foreach($sites['info'] as $info){
            if($id === $info['id']){
                $data = $info;
            }
        }


        $modFolder = base_path().'/dist/'.$data['path'].'/modules/';
        $modules = $request->input('modules');
        Log::debug(print_r($modules,true));
        foreach($modules['id'] as $key => $id){
            if(empty($id)){
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
            Log::debug(print_r($lines_array,true));

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
            $siteInfos = json_decode($json,TRUE);
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

        $reader = Excel::load(base_path().'/dist/'.$data['path'].'/site_infos/site_info.xlsx');
        if ($reader == null)
        {
            throw new \Exception('error.');
        }
        // ファイル内のシートの枚数によって $reader->all() が返すオブジェクトのクラスが異なる
        if (preg_match('/SheetCollection$/', get_class($reader->all())))
        {
            // シートが複数ある場合
            $sheet = $reader->first();
        }
        else if (preg_match('/RowCollection$/', get_class($reader->all())))
        {
            // シートが1枚の場合
            $sheet = $reader;
        }
        else
        {
            throw new \Exception('error.');
        }

        $head;
        $site_infos = array();
        $headers = array();
        $cnt = 0;
        foreach ($sheet->all() as $key => $cells)
        {
            $cs = $cells->all();
            if(count($headers) === 0){
                $headers = array_keys($cs);
                $mods = array();
                foreach($headers as $h){
                    if(\preg_match('/mod/', $h)){
                        $cnt++;
                        array_push($mods,'mod'.$cnt);
                    }
                }
                $headers['mods'] = $mods;
            }

            if(empty($cs['path'])){
                continue;
            }

            $modules = array();
            for($i = 1;$i <= $cnt;$i++){
                array_push($modules, $cs['mod'.$i]);
            }
            $cs['mod_cnt'] = $cnt;

            array_push($site_infos, $cs);
        }

        return $site_infos;
    }
}

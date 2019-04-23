<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DOMDocument;

class HomeController extends Controller
{
    protected $folder;

    function __construct()
    {
        $this->folder = base_path().'/project_info.xml';
    }
    public function index()
    {
        if (\File::exists($this->folder)) {
            $feed = file_get_contents($this->folder);
            $sites = simplexml_load_string($feed);
            Log::debug(print_r($sites,true));
            $json = json_encode($sites);
            $sites = json_decode($json,TRUE);
            if (array_values($sites['info']) !== $sites['info']) {
                $sites['info'] = array($sites['info']);
            }
        } else {
            $sites = array('info'=>array());
        }

        Log::debug(print_r(count($sites['info']),true));
        Log::debug(print_r($sites,true));
        return view('dashbord', compact('sites'));
    }

    public function createProject(Request $request)
    {
        $p = $request->input('project');
        $reader = \Excel::load(base_path().'/project_infos.xlsx');
        $data = [];
        foreach ($reader->all() as $cells)
        {
            $data[] = $cells->all();
        }

        $fileNames = array();
        $distfolder = base_path().'/dist/'.$p['path'];
        if (!\File::exists($distfolder)) {
            \File::makeDirectory($distfolder.'/templates', 0777, true);
            \File::makeDirectory($distfolder.'/modules', 0777, true);
            \File::makeDirectory($distfolder.'/site_infos', 0777, true);
        }

        if($request->hasFile('templates')){
            $files = $request->file('templates');
            $path = $distfolder.'/templates';
            $site_info_path = $distfolder.'/site_infos';
            foreach ($files as $file) {
                array_push($fileNames, $file->getClientOriginalName());

                move_uploaded_file($file->getRealPath(), $distfolder.'/templates'.'/'.$file->getClientOriginalName());
                $success = \File::copy(base_path('site_info_temp.xlsx'),$site_info_path.'/site_info.xlsx');
            }
        }

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
        $addInfo['name'] = $p['name'];
        $addInfo['path'] = $p['path'];
        $addInfo['temps']['temp-name'] = array();
        foreach($fileNames as $fn){
            array_push($addInfo['temps']['temp-name'], $fn);
        }
        Log::debug(print_r($siteInfos,true));
        Log::debug(print_r($addInfo,true));
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

    public function projectInfo(Request $request, $id)
    {
        $feed = file_get_contents($this->folder);
        $sites = simplexml_load_string($feed);
        Log::debug(print_r($sites,true));
        $json = json_encode($sites);
        $sites = json_decode($json,TRUE);
        if (array_values($sites['info']) !== $sites['info']) {
            $sites['info'] = array($sites['info']);
        }
        $data;
        foreach($sites['info'] as $info){
            if($id === $info['id']){
                $data = $info;
            }
        }
        Log::debug(print_r($data,true));

    }
}

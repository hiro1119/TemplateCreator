<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\File;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $message;
    protected $siteInfos;

    public function __construct()
    {
        $path = base_path() . '/dist';
        if (!file_exists($path)) {
            $result = \File::makeDirectory($path, 0775, true);
        }
    }
}

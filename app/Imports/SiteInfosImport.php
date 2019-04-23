<?php

namespace App\Imports;

use App\SiteInfo;
use Maatwebsite\Excel\Concerns\ToModel;

class SiteInfosImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new SiteInfo([
            //
        ]);
    }
}

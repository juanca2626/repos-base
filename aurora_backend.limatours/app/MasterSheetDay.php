<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterSheetDay extends Model
{
    use SoftDeletes;

    public function services()
    {
        return $this->hasMany('App\MasterSheetService');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnitDuration extends Model
{
    use SoftDeletes;

    public function services()
    {
        return $this->hasMany('App\Service');
    }

    public function translations()
    {
        return $this->hasMany('App\Models\Translation', 'object_id', 'id')
            ->where('translations.type', '=', 'unitduration');
    }


}

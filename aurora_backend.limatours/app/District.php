<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class District extends Model
{
    use SoftDeletes;

    public function city()
    {
        return $this->belongsTo('App\City');
    }

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'district');
    }

    public function hotels()
    {
        return $this->hasMany('App\Hotel');
    }
}

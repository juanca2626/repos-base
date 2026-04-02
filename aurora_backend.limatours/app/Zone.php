<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zone extends Model
{
    use SoftDeletes;

    public function city()
    {
        return $this->belongsTo('App\City');
    }

    public function hotels()
    {
        return $this->hasMany('App\Hotel');
    }

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'zone');
    }
}

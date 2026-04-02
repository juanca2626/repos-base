<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{

    protected $casts = [
        'status' => 'boolean'
    ];

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'facility');
    }

    public function galeries()
    {
        return $this->hasMany('App\Galery', 'object_id', 'id');
    }
}

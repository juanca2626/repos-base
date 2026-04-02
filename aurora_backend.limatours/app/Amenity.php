<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', '=', 'amenity');
    }

    public function galeries()
    {
        return $this->hasMany('App\Galery', 'object_id', 'id');
    }

    public function hotels()
    {
        return $this->belongsToMany('App\Hotel');
    }
}

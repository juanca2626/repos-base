<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MultimediaPhotoFilter extends Model
{
    use SoftDeletes;

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', '=', 'multimedia');
    }

    public function multimedia()
    {
        return $this->belongsToMany('App\Multimedia');
    }
}

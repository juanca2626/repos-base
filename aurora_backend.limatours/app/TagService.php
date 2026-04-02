<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TagService extends Model
{
    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')->where('translations.type', '=', 'tagservices');
    }
}

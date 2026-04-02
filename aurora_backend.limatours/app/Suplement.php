<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Suplement extends Model
{
    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SerieCategory extends Model
{
    use SoftDeletes;

    public function type_class()
    {
        return $this->belongsTo('App\TypeClass');
    }

    public function services()
    {
        return $this->hasMany('App\SerieService');
    }

}

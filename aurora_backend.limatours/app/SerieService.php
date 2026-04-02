<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SerieService extends Model
{
    use SoftDeletes;

    public function prices(){
        return $this->hasMany('App\SerieServicePrice');
    }

}

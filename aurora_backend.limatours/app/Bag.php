<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bag extends Model
{
    use SoftDeletes;

    public function bag_rooms()
    {
        return $this->hasMany('App\BagRoom');
    }
}

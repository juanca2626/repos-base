<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Train extends Model
{
    public function rail_routes()
    {
        return $this->hasMany('App\TrainRailRoute');
    }
}

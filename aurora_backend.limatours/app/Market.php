<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Market extends Model
{
    //
    use SoftDeletes;

    public function clients()
    {
        return $this->hasMany('App\Client');
    }
    public function regions()
    {
        return $this->belongsToMany('App\Market', 'market_regions');
    }
}

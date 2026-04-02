<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    public function markets()
    {
        return $this->belongsToMany('App\Market', 'market_regions');
    }
}

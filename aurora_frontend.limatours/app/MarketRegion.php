<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MarketRegion extends Model
{
    protected $connection = 'mysql2';

    public function region()
    {
        return $this->hasOne('App\Region', 'id', 'region_id');
    }

}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Market extends Model
{
    protected $connection = 'mysql2';

    public function market_region()
    {
        return $this->hasOne('App\MarketRegion', 'market_id', 'id');
    }
}

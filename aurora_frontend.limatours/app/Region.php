<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model
{
    protected $connection = 'mysql2';

    public function market()
    {
        return $this->hasOne('App\Market', 'id', 'market_id');
    }
}

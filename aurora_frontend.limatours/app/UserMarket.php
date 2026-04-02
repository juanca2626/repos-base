<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserMarket extends Model
{
    protected $connection = 'mysql2';

    public function markets()
    {
        return $this->hasMany('App\Market', 'id', 'market_id');
    }
}

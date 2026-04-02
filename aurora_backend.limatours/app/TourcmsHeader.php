<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TourcmsHeader extends Model
{
    use SoftDeletes;

    public function customers()
    {
        return $this->hasMany('App\TourcmsCustomer');
    }
    public function components()
    {
        return $this->hasMany('App\TourcmsComponent');
    }
    public function reserves()
    {
        return $this->hasMany('App\TourcmsReserve', 'booking_id', 'booking_id');
    }
}

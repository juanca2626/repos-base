<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HotelSupplement extends Model
{
    public function supplement()
    {
        return $this->hasOne('App\Suplement', 'id','supplement_id');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TourcmsReserve extends Model
{
    public function reserve_file()
    {
        return $this->belongsTo('App\ReserveFile');
    }
}

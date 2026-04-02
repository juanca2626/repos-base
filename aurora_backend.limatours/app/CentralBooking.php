<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CentralBooking extends Model
{
    use SoftDeletes;

    public function file()
    {
        return $this->hasOne('App\ReserveFile', 'file_number', 'file_number');
    }
}

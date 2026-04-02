<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExtensionGenericOtaReserves extends Model
{
    public function file()
    {
        return $this->belongsTo('App\ReserveFile', 'reserve_file_id');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExtensionPentagramaReserve extends Model
{
    public function file()
    {
        return $this->belongsTo('App\ReserveFile', 'reserve_file_id');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExtensionGYGReserve extends Model
{
    protected $table = 'extension_gyg_reserves';

    public function file()
    {
        return $this->belongsTo('App\ReserveFile', 'reserve_file_id');
    }
}

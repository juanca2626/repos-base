<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExtensionDespegarService extends Model
{

    public function header()
    {
        return $this->belongsTo('App\ExtensionDespegarHeader', 'extension_despegar_header_id');
    }

    public function reserves()
    {
        return $this->hasMany('App\ExtensionDespegarReserve', 'despegar_service_id');
    }

}

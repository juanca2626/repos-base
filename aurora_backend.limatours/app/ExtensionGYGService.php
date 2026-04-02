<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExtensionGYGService extends Model
{
    protected $table = 'extension_gyg_services';

    public function header()
    {
        return $this->belongsTo('App\ExtensionGYGHeader', 'extension_gyg_header_id');
    }

    public function reserves()
    {
        return $this->hasMany('App\ExtensionGYGReserve', 'gyg_service_id');
    }

}

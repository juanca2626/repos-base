<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExtensionExpediaService extends Model
{
    public function header()
    {
        return $this->belongsTo('App\ExtensionExpediaHeader', 'extension_expedia_header_id');
    }

    public function reserves()
    {
        return $this->hasMany('App\ExtensionExpediaReserve', 'expedia_service_id');
    }
}

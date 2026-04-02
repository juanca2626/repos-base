<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExtensionGenericOtaService extends Model
{
    public function header()
    {
        return $this->belongsTo('App\ExtensionGenericOtaHeader', 'generic_ota_header_id');
    }

    public function reserves()
    {
        return $this->hasMany('App\ExtensionGenericOtaReserves', 'generic_ota_service_id');
    }

    public function package()
    {
        return $this->belongsTo('App\Package', 'package_id');
    }
}

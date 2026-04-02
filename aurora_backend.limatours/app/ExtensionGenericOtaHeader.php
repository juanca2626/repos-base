<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExtensionGenericOtaHeader extends Model
{
    public function ota()
    {
        return $this->belongsTo('App\Ota');
    }
}

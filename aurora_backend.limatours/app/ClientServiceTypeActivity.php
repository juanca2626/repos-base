<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientServiceTypeActivity extends Model
{
    public function service_type_activity()
    {
        return $this->belongsTo('App\ServiceTypeActivity');
    }
}

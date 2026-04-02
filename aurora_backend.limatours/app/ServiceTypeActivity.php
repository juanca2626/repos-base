<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceTypeActivity extends Model
{
    use SoftDeletes;

    public function service_operations()
    {
        return $this->hasMany('App\ServiceOperationActivity');
    }

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', '=', 'servicetypeactivity');
    }

    public function client_service_type_activity()
    {
        return $this->hasMany('App\ClientServiceTypeActivity');
    }
}

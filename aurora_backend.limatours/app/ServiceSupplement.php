<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceSupplement extends Model
{
    use SoftDeletes;

    public function services()
    {
        return $this->belongsTo('App\Service');
    }

    public function parent_service()
    {
        return $this->belongsTo('App\Service','service_id');
    }
    public function supplements()
    {
        return $this->belongsTo('App\Service','object_id','id');
    }

    public function getDaysToChargeAttribute($value)
    {
        return json_decode($value);
    }

}

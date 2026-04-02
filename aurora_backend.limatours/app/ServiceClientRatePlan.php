<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ServiceClientRatePlan extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['client'];
    }

    public function serviceRate()
    {
        return $this->belongsTo('App\ServiceRate', 'service_rate_id');
    }

    public function service_rate()
    {
        return $this->belongsTo('App\ServiceRate', 'service_rate_id');
    }

    public function client()
    {
        return $this->hasOne('App\Client', 'id', 'client_id');
    }
}

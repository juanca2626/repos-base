<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ClientRatePlan extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['client'];
    }

    public function ratePlan()
    {
        return $this->belongsTo('App\RatesPlans', 'rate_plan_id');
    }
    public function rate_plan()
    {
        return $this->belongsTo('App\RatesPlans', 'rate_plan_id');
    }

    public function client()
    {
        return $this->hasOne('App\Client', 'id', 'client_id');
    }

    public function businessRegion()
    {
        return $this->belongsTo(BusinessRegion::class);
    }
}

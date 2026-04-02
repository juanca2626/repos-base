<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class MarkupRatePlan extends Model implements Auditable
{
    use SoftDeletes, \OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['client'];
    }

    public function client()
    {
        return $this->belongsTo('App\Client', 'client_id');
    }

    public function hotel_rate_plans()
    {
        return $this->belongsTo('App\RatesPlans', 'rate_plan_id');
    }
}

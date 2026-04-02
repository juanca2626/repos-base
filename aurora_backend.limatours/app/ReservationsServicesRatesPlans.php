<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReservationsServicesRatesPlans extends Model
{
    public function reservationServiceCancelPolicies()
    {
        return $this->hasMany(ReservationsServicesRatesPlansCancellationPolicies::class);
    }

    /**
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function service_rates_plans()
    {
        return $this->hasOne(ServiceRatePlan::class, 'id', 'service_rate_plan_id');
    }    
}

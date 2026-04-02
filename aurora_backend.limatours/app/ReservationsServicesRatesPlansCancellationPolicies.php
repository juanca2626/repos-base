<?php

namespace App;

use App\CustomModel as Model;

class ReservationsServicesRatesPlansCancellationPolicies extends Model
{
    public function reservationServiceRatePlan()
    {
        return $this->belongsTo(ReservationsServicesRatesPlans::class);
    }
}

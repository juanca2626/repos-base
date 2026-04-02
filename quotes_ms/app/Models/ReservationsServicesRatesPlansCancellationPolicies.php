<?php

namespace App\Models;

use App\Models\CustomModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationsServicesRatesPlansCancellationPolicies extends Model
{
    public function reservationServiceRatePlan(): BelongsTo
    {
        return $this->belongsTo(ReservationsServicesRatesPlans::class);
    }
}

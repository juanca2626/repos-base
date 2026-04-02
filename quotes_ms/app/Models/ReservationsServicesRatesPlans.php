<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReservationsServicesRatesPlans extends Model
{
    public function reservationServiceCancelPolicies(): HasMany
    {
        return $this->hasMany(ReservationsServicesRatesPlansCancellationPolicies::class);
    }
}

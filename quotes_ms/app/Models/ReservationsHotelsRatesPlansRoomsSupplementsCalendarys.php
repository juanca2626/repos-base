<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationsHotelsRatesPlansRoomsSupplementsCalendarys extends Model
{
    public function reservations_hotels_rates_plans_rooms_supplements(): BelongsTo
    {
        return $this->belongsTo(ReservationsHotelsRatesPlansRoomsSupplements::class);
    }
}

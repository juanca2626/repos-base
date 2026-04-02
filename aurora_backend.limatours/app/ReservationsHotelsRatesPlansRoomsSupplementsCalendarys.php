<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReservationsHotelsRatesPlansRoomsSupplementsCalendarys extends Model
{
    public function reservations_hotels_rates_plans_rooms_supplements()
    {
        return $this->belongsTo(ReservationsHotelsRatesPlansRoomsSupplements::class);
    }
}

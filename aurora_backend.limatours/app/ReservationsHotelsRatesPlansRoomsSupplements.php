<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReservationsHotelsRatesPlansRoomsSupplements extends Model
{
    public function reservations_hotels_rates_plans_rooms()
    {
        return $this->belongsTo(ReservationsHotelsRatesPlansRooms::class);
    }

    public function calendaries()
    {
        return $this->hasMany(ReservationsHotelsRatesPlansRoomsSupplementsCalendarys::class);
    }
}

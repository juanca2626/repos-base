<?php

namespace App;

use App\CustomModel as Model;

class ReservationsHotelsRatesPlansRoomsCalendarysRates extends Model
{
    public function reservationHotelRoomDate()
    {
        return $this->belongsTo(ReservationsHotelsRatesPlansRoomsCalendarys::class);
    }
}

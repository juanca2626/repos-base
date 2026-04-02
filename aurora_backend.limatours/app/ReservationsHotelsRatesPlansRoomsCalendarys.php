<?php

namespace App;

use App\CustomModel as Model;

class ReservationsHotelsRatesPlansRoomsCalendarys extends Model
{
    const UPDATE_INVENTORY_NOT_FOUND = 2;
    const UPDATE_INVENTORY_SUCCESS = 1;
    const UPDATE_INVENTORY_PENDING = 0; 
    const UPDATE_INVENTORY_NO_ALLOTMENTS = 3;
    const UPDATE_INVENTORY_NO_FITS = 4;

    public function reservationHotel()
    {
        return $this->belongsTo(ReservationsHotelsRatesPlansRooms::class);
    }

    public function reservationHotelRoomDateRate()
    {
        return $this->hasMany(ReservationsHotelsRatesPlansRoomsCalendarysRates::class);
    }
    public function reservations_hotels_rates_plans_rooms()
    {
        return $this->belongsTo(ReservationsHotelsRatesPlansRooms::class);
    }
}

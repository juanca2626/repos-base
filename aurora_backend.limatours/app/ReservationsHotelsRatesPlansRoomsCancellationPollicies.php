<?php

namespace App;

use App\CustomModel as Model;

class ReservationsHotelsRatesPlansRoomsCancellationPollicies extends Model
{
    public function reservationHotel()
    {
        return $this->belongsTo(ReservationsHotelsRatesPlansRooms::class);
    }
}

<?php

namespace App\Models;

use App\Models\CustomModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationsHotelsRatesPlansRoomsCalendarysRates extends Model
{
    public function reservationHotelRoomDate(): BelongsTo
    {
        return $this->belongsTo(ReservationsHotelsRatesPlansRoomsCalendarys::class);
    }
}

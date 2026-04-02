<?php

namespace App\Models;

use App\Models\CustomModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationsHotelsRatesPlansRoomsCancellationPollicies extends Model
{
    public function reservationHotel(): BelongsTo
    {
        return $this->belongsTo(ReservationsHotelsRatesPlansRooms::class);
    }
}

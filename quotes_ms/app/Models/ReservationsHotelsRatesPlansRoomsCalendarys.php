<?php

namespace App\Models;

use App\Models\CustomModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReservationsHotelsRatesPlansRoomsCalendarys extends Model
{
    public const UPDATE_INVENTORY_NOT_FOUND = 2;

    public const UPDATE_INVENTORY_SUCCESS = 1;

    public const UPDATE_INVENTORY_PENDING = 0;

    public function reservationHotel(): BelongsTo
    {
        return $this->belongsTo(ReservationsHotelsRatesPlansRooms::class);
    }

    public function reservationHotelRoomDateRate(): HasMany
    {
        return $this->hasMany(ReservationsHotelsRatesPlansRoomsCalendarysRates::class);
    }

    public function reservations_hotels_rates_plans_rooms(): BelongsTo
    {
        return $this->belongsTo(ReservationsHotelsRatesPlansRooms::class);
    }
}

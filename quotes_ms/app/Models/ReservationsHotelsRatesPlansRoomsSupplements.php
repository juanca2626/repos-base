<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReservationsHotelsRatesPlansRoomsSupplements extends Model
{
    public function reservations_hotels_rates_plans_rooms(): BelongsTo
    {
        return $this->belongsTo(ReservationsHotelsRatesPlansRooms::class);
    }

    public function calendaries(): HasMany
    {
        return $this->hasMany(ReservationsHotelsRatesPlansRoomsSupplementsCalendarys::class);
    }
}

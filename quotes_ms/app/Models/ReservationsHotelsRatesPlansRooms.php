<?php

namespace App\Models;

use App\Models\CustomModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReservationsHotelsRatesPlansRooms extends Model
{
    use SoftDeletes;

    /*
     * Campo: onRequest
     */
    public const ON_REQUEST = 0;

    public const NOT_ON_REQUEST = 1;

    public static function getAll($filters)
    {
        $reservations = (new self());

        if ($filters['file_code'] and ! empty($filters['file_code'])) {
            $reservations = $reservations->whereHas('reservation', function ($reservation) use ($filters) {
                $reservation->where('file_code', '=', $filters['file_code']);
            });
        }

        if (! empty($filters['selected_client'])) {
            $reservations = $reservations->whereHas('reservation', function ($reservation) use ($filters) {
                $reservation->where('client_id', '=', $filters['selected_client']);
            });
        }

        if (! empty($filters['selected_excecutive'])) {
            //            $reservations = $reservations->whereHas('reservation', function ($reservation) use ($filters) {
            //                $reservation->where('executive_id', '=', $filters['selected_excecutive']);
            //            });
            $reservations = $reservations->where('executive_id', '=', $filters['selected_excecutive']);
        }

        if (! empty($filters['from_date'])) {
            $reservations = $reservations->where('created_at', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $reservations = $reservations->where('created_at', '<=', $filters['to_date']);
        }

        return $reservations->with([
            'reservation',
            'reservationHotelCancelPolicies',
            'reservationsHotelsCalendarys' => function ($hotelRoom) {
                $hotelRoom->with([
                    'reservationHotelRoomDateRate',
                ]);
            },
        ])->orderBy('reservation_id')
            ->orderBy('hotel_id')
            ->orderBy('check_in')
            ->orderBy('room_id')
            ->get();
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function reservationHotelCancelPolicies(): HasMany
    {
        return $this->hasMany(ReservationsHotelsRatesPlansRoomsCancellationPollicies::class);
    }

    public function reservationsHotelsCalendarys(): HasMany
    {
        return $this->hasMany(ReservationsHotelsRatesPlansRoomsCalendarys::class);
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function rates_plans_room(): BelongsTo
    {
        return $this->belongsTo(RatesPlansRooms::class);
    }

    public function user_cancel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancel_user_id', 'id');
    }

    public function supplements(): HasMany
    {
        return $this->hasMany(ReservationsHotelsRatesPlansRoomsSupplements::class);
    }

    public function room_type(): BelongsTo
    {
        return $this->belongsTo(RoomType::class, 'room_type_id', 'id');

    }

    public function reservation_hotel(): BelongsTo
    {
        return $this->belongsTo(ReservationsHotel::class, 'reservations_hotel_id', 'id');

    }
}

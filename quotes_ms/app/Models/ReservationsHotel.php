<?php

namespace App\Models;

use App\Models\CustomModel as Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class ReservationsHotel extends Model
{
    use SoftDeletes;

    /*
     * Campo: status
     */
    public const STATUS_CANCELLED = 0;

    public const STATUS_CONFIRMED = 1;

    public const STATUS_NOT_CONFIRMED = 3;

    /*
     * Campo: status_email
     */
    public const STATUS_EMAIL_SENT = 1;

    public const STATUS_EMAIL_NOT_SENT = 0;

    public static function getAll(array $filters): Collection
    {
        $reservationsHotels = (new self());

        if (! empty($filters['file_code']) and $filters['file_code'] != '') {
            $reservation = Reservation::where(
                'file_code',
                $filters['file_code']
            )
                ->where(
                    'status_cron_job_reservation_stella',
                    Reservation::STATUS_CRONJOB_CLOSE_PROCESS
                )->first(['id']);
            //                    throw new \Exception(json_encode($reservation));
            if ($reservation) {
                $reservationsHotels = $reservationsHotels->where('reservation_id', $reservation->id);
            } else {
                return collect();
            }

        }

        if (! empty($filters['reservation_hotel_id'])) {
            if (is_array($filters['reservation_hotel_id'])) {
                $reservationsHotels = $reservationsHotels->whereIn('id', $filters['reservation_hotel_id']);
            } else {
                $reservationsHotels = $reservationsHotels->where('id', '=', $filters['reservation_hotel_id']);
            }
        }

        if (! empty($filters['selected_client']) && empty($filters['option'])) {

            if ($filters['user_type_id'] != 3) {
                $client_id = Client::where('code', $filters['selected_client'])->first()->id;
                $reservationsHotels = $reservationsHotels->whereHas(
                    'reservation',
                    function ($reservation) use ($client_id) {
                        $reservation->where('client_id', '=', $client_id);
                    }
                );
            }
        } else {
            if ($filters['user_type_id'] == 4) {
                $client_id = Client::where('id', $filters['client_id'])->first()->id;
                $reservationsHotels = $reservationsHotels->whereHas(
                    'reservation',
                    function ($reservation) use ($client_id) {
                        $reservation->where('client_id', '=', $client_id);
                    }
                );
            }
        }

        if (! empty($filters['selected_excecutive'])) {
            $reservationsHotels = $reservationsHotels->where('executive_id', '=', $filters['selected_excecutive']);
        }
        if (! empty($filters['from_date'])) {
            $reservationsHotels = $reservationsHotels->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $reservationsHotels = $reservationsHotels->whereDate('created_at', '<=', $filters['to_date']);
        }

        $reservationsHotels->whereHas('reservationsHotelRooms', function ($reservation) {
            // $reservation->where('onRequest', '=', 1);
        });

        $reservationsHotels = $reservationsHotels
            ->whereHas('reservation')
            ->with([
                'reservation',
                'reservationsHotelRooms' => function ($hotelRoom) use ($filters) {
                    if (! empty($filters['selected_excecutive'])) {
                        $hotelRoom->where('executive_id', '=', $filters['selected_excecutive']);
                    }
                    if (! empty($filters['hotel_id'])) {
                        $hotelRoom->where('hotel_id', '=', $filters['hotel_id']);
                    }

                    if (! empty($filters['check_in'])) {
                        $hotelRoom->where('check_in', '>=', $filters['check_in']);
                    }

                    if (! empty($filters['check_out'])) {
                        $hotelRoom->where('check_out', '<=', $filters['check_out']);
                    }

                    $hotelRoom->with([
                        'reservationHotelCancelPolicies',
                        'reservationsHotelsCalendarys' => function ($hotelRoom) {
                            $hotelRoom->with([
                                'reservationHotelRoomDateRate',
                            ]);
                        },
                    ]);
                },
            ])
            ->get();
        //        throw new \Exception(json_encode($reservationsHotels));

        $reservationsHotels->makeHidden('total_amount_base');

        foreach ($reservationsHotels as $reservationsHotel) {
            $reservationsHotel->reservationsHotelRooms->transform(function ($query) {
                $pass_jsons = 0;
                if (! $query['policies_cancellation']) {
                    $query['policies_cancellation'] = [];
                } else {
                    $query['policies_cancellation'] = json_decode($query['policies_cancellation'], true);
                    $pass_jsons++;
                }
                if (! $query['taxes_and_services']) {
                    $query['taxes_and_services'] = [];
                } else {
                    $query['taxes_and_services'] = json_decode($query['taxes_and_services'], true);
                    $pass_jsons++;
                }

                //*******************************************************
                $query['penality_included'] = false;
                $query['penality_amount'] = 0;
                if ($pass_jsons === 2) {
                    $igv = [
                        'percent'      => 0,
                        'total_amount' => 0,
                    ];
                    $extra_fees = $query['taxes_and_services'];
                    if (isset($extra_fees['apply_fees']) and isset($extra_fees['apply_fees']['t'])) {
                        foreach ($extra_fees['apply_fees']['t'] as $extra_fee) {
                            if ($extra_fee['name'] == 'IGV') {
                                $igv['percent'] = $extra_fee['value'];
                                $igv['total_amount'] = $extra_fee['total_amount'];
                            }
                        }
                    }

                    // ***
                    $policies_cancellation = $query['policies_cancellation'];
                    $policy_cancellation = (object) $policies_cancellation[0];
                    $_apply_date = explode('-', $policy_cancellation->apply_date);

                    $apply_date = $_apply_date[2].'-'.$_apply_date[1].'-'.$_apply_date[0];
                    if ($apply_date < Carbon::parse($query['updated_at'])->format('Y-m-d') and $query['onRequest'] != 0 and $query['stella_updated_at'] == null) {
                        $query['penality_included'] = true;
                        if ($query->id === 1784) {
                            dd($policy_cancellation->penalty_price);
                        }

                        $query['penality_amount'] = $igv['total_amount'] + (float) filter_var(
                            $policy_cancellation->penalty_price,
                            FILTER_SANITIZE_NUMBER_FLOAT,
                            FILTER_FLAG_ALLOW_FRACTION
                        );
                    }

                }
                //*******************************************************

                $query->makeHidden('total_amount_base');

                return $query;
            });

        }

        return $reservationsHotels;
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function reservationsHotelRooms(): HasMany
    {
        return $this->hasMany(ReservationsHotelsRatesPlansRooms::class);
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class, 'hotel_id', 'id');
    }
}

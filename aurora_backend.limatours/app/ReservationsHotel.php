<?php

namespace App;

use App\CustomModel as Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReservationsHotel extends Model
{
    use SoftDeletes;

    /*
     * Campo: status
     */
    const STATUS_CANCELLED = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_NOT_CONFIRMED = 3;

    /*
     * Campo: status_email
     */
    const STATUS_EMAIL_SENT = 1;
    const STATUS_EMAIL_NOT_SENT = 0;

    /*
     * Campo: status_stela_reserve
     */
    const STATUS_STELA_RESERVE_SENT = 1;
    const STATUS_STELA_RESERVE_NOT_SENT = 0;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reservationsHotelRooms()
    {
        return $this->hasMany(ReservationsHotelsRatesPlansRooms::class);
    }

    /**
     * @param  array  $filters
     * @return ReservationsHotel|ReservationsHotel[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getAll(array $filters)
    {
        $reservationsHotels = (new self);
        $filterFromAurora = $filters['from_aurora'];
        if (!empty($filters['file_code']) and $filters['file_code'] != '') {
            $reservation = Reservation::where('file_code',
                $filters['file_code'])->where(function($query) use ($filterFromAurora) {
                    if ($filterFromAurora) {
                        return $query->where('status_cron_job_reservation_stella', Reservation::STATUS_CRONJOB_CLOSE_PROCESS);
                    }

                    return $query
                        ->where('status_cron_job_reservation_stella', Reservation::STATUS_CRONJOB_CLOSE_PROCESS)
                        ->orWhere('status_cron_job_reservation_stella', Reservation::STATUS_CRONJOB_CREATE_FILE);
                })
                ->first(['id']);
//                    throw new \Exception(json_encode($reservation));
            if ($reservation) {
                $reservationsHotels = $reservationsHotels->where('reservation_id', $reservation->id);
            } else {
                return collect();
            }

        }

        if (!empty($filters['reservation_hotel_id'])) {
            if (is_array($filters['reservation_hotel_id'])) {
                $reservationsHotels = $reservationsHotels->whereIn('id', $filters['reservation_hotel_id']);
            } else {
                $reservationsHotels = $reservationsHotels->where('id', '=', $filters['reservation_hotel_id']);
            }
        }

        if (!empty($filters['selected_client']) && empty($filters['option'])) {

            if ($filters['user_type_id'] != 3) {
                $client_id = Client::where('code', $filters['selected_client'])->first()->id;
                $reservationsHotels = $reservationsHotels->whereHas('reservation',
                    function ($reservation) use ($client_id) {
                        $reservation->where('client_id', '=', $client_id);
                    });
            }
        } else {
            if ($filters['user_type_id'] == 4) {
                $client_id = Client::where('id', $filters['client_id'])->first()->id;
                $reservationsHotels = $reservationsHotels->whereHas('reservation',
                    function ($reservation) use ($client_id) {
                        $reservation->where('client_id', '=', $client_id);
                    });
            }
        }

        if (!empty($filters['selected_excecutive'])) {
            $reservationsHotels = $reservationsHotels->where('executive_id', '=', $filters['selected_excecutive']);
        }
        if (!empty($filters['from_date'])) {
            $reservationsHotels = $reservationsHotels->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $reservationsHotels = $reservationsHotels->whereDate('created_at', '<=', $filters['to_date']);
        }

        if(empty($filters['from_date']) && empty($filters['to_date']) && empty($filters['file_code']))
        {
            $reservationsHotels = $reservationsHotels->where('check_in', '>=', date("Y-m-d"));
        }

        $order_ = 'asc';
        if (!empty($filters['date_order'])) {
            $order_ = $filters['date_order'];
        }

        $reservationsHotels = $reservationsHotels->orderBy('check_in', $order_);

        // $reservationsHotels->whereHas('reservationsHotelRooms', function ($reservation) use ($filters) {
        //     // $reservation->where('onRequest', '=', 1);
        // });

        $reservationsHotels = $reservationsHotels
            // ->whereHas('reservation')
            ->with([
            'reservation.client.configuration',
            'reservationsHotelRooms' => function ($hotelRoom) use ($filters) {
                if (!empty($filters['selected_excecutive'])) {
                    $hotelRoom->where('executive_id', '=', $filters['selected_excecutive']);
                }
                if (!empty($filters['hotel_id'])) {
                    $hotelRoom->where('hotel_id', '=', $filters['hotel_id']);
                }

                if (!empty($filters['check_in'])) {
                    $hotelRoom->where('check_in', '>=', $filters['check_in']);
                }

                if (!empty($filters['check_out'])) {
                    $hotelRoom->where('check_out', '<=', $filters['check_out']);
                }

                $hotelRoom->with([
                    'reservationHotelCancelPolicies',
                    'reservationsHotelsCalendarys' => function ($hotelRoom) {
                        $hotelRoom->with([
                            'reservationHotelRoomDateRate'
                        ]);
                    }
                ]);
            },
        ]);

        $take = 20; $page = $filters['page']; $skip = $page * $take;

        $count = $reservationsHotels->count(); $pages = ceil($count / $take);

        $reservationsHotels = $reservationsHotels->take($take)->skip($skip);
        $reservationsHotels = $reservationsHotels->get();
//        throw new \Exception(json_encode($reservationsHotels));

        $reservationsHotels->makeHidden('total_amount_base');

        foreach ($reservationsHotels as $reservationsHotel) {
            $business_region_id =  $reservationsHotel->business_region_id;
       
            $reservationsHotel->reservationsHotelRooms->transform(function ($query) use ($business_region_id) {

                // Continuar si es la region de Peru
                $pass_jsons = 0;
                if (!$query['policies_cancellation']) {
                    $query['policies_cancellation'] = [];
                } else {
                    $query['policies_cancellation'] = json_decode($query['policies_cancellation'], true);
                    $pass_jsons++;
                }
                if (!$query['taxes_and_services']) {
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
                        'percent' => 0,
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
            
                    if($business_region_id > 1){
              
                        $policies_cancellation_details = $query['policies_cancellation']['details']; // extructura ordenada con rangos de fechas y penalidades.
                        $policies_cancellation_parameters = $query['policies_cancellation']['cancellationPolicies']; // parametros de hyperguest
                                      
                        $apply_date = $query['first_penalty_date'];
                        
                        if(date('Y-m-d') >= $apply_date){

                            $query['penality_included'] = true;
                         
                            $fecha_now = date('Y-m-d H:i');
                            $fecha = Carbon::parse($fecha_now);

                            // 🔎 Buscar el registro cuyo rango contenga esa fecha
                    
                            $select_cancellation = collect($policies_cancellation_details)->first(function ($item) use ($fecha) {
                                $desde = Carbon::parse($item['desde']);
                                $hasta = Carbon::parse($item['hasta']);
                                // between($start, $end, true) incluye ambos límites
                                return $fecha->between($desde, $hasta, true);
                            });
                        
                            $penality_amount = 0;
                            $message = '';
                            if($select_cancellation){
                                $penality_amount = $select_cancellation['penalizacion_usd'];
                                $message = sprintf( "You will have to pay a penalty of USD %s if you cancel", $penality_amount );                                                                                              
                            }else{
                                //si no encuentra fecha entonces posible que ya se haya pasado la fecha de cancelacion y la fecha actual es mayor a la fecha del chceckin y no lo encuentro entonces hagarramos el ultima paratreo que es la penalidad mas alta
                                if(isset($policies_cancellation_details[count($policies_cancellation_details)-1]))
                                {
                                    $penality_amount = $policies_cancellation_details[count($policies_cancellation_details)-1]['penalizacion_usd'];
                                    $message = sprintf( "You will have to pay a penalty of USD %s if you cancel", $penality_amount );
                                }else{
                                    // si no encuentra nada entonces cobramos cobramos el precio de la habitacion.
                                    $penality_amount = $query['total_amount'];
                                    $message = sprintf( "You will have to pay a penalty of USD %s if you cancel", $penality_amount );
                                }
                                
                            }

                            $query['penality_amount'] = $penality_amount;
                            $query['policies_cancellation'] = [
                                [
                                    'message' => $message
                                ]
                            ];


                        }else{
                            $query['penality_included'] = false;
                            $query['penality_amount'] = 0;
                            $query['policies_cancellation'] = [
                                [
                                    'message' => $query['policies_cancellation']['name']
                                ]
                            ];
                        }


                        // if ($apply_date < Carbon::parse($query["updated_at"])->format('Y-m-d')) {
                        //     $query['penality_included'] = true;
                        //     $query['penality_amount'] = $igv['total_amount'] + (double)filter_var($policy_cancellation->penalty_price,
                        //             FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                        // }
                        
                    }else{
                        
                        if(count($query['policies_cancellation'])>0){
                            // ***
                            $policies_cancellation = $query['policies_cancellation'];
                            $policy_cancellation = (object)$policies_cancellation[0];
                            $_apply_date = explode('-', $policy_cancellation->apply_date);

                            $apply_date = $_apply_date[2].'-'.$_apply_date[1].'-'.$_apply_date[0];
                            if ($apply_date < Carbon::parse($query["updated_at"])->format('Y-m-d') AND $query['onRequest'] != 0 and $query['stella_updated_at'] == null) {
                                $query['penality_included'] = true;
                                $query['penality_amount'] = $igv['total_amount'] + (double)filter_var($policy_cancellation->penalty_price,
                                        FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                            }
                        }
                      

                    }

                    



                }
                //*******************************************************

                $query->makeHidden('total_amount_base');
                $query->makeHidden('markup');
                return $query;
            });

            if (auth_user()->user_type_id == 4) {
                $reservationsHotel->reservation->makeHidden(['total_hotels_taxes','total_hotels_services','total_hotels_discounts','total_hotels_subs','total_hotels','total_services','total_services_subs','total_services_taxes','total_discounts','subtotal_amount','total_tax','markup','status_cron_job_reservation_stella','status_cron_job_send_email','status_cron_job_error','status_cron_job_order_stella','deleted_at']);
                $reservationsHotel->reservation->client->setVisible(['id', 'code', 'name', 'address']);
            }
        }

        return [
            'total' => $count,
            'pages' => $pages,
            'reservations' => $reservationsHotels,
        ];
    }


    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id', 'id');
    }
}

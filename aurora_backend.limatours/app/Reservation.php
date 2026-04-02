<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Reservation extends Model
{
    use SoftDeletes;

    public $consecutive_hotel_prev;
    public $consecutive_service_prev;

    /*
     * Column: status_cron_job_reservation_stella
     */
    //Todo Estado para crear en el file datos de facturacion (solo para el uso de pago con tarjeta)
    const STATUS_CRONJOB_CREATE_BILLING_DATA = 1;
    //Todo Estado para la creacion de file
    const STATUS_CRONJOB_CREATE_FILE = 2;
    //Todo Estado para creacion de asiento contable (solo para el uso de pago con tarjeta)
    const STATUS_CRONJOB_CREATE_ACCOUNTING_SEAT = 3;
    //Todo Estado cuando todos los procesos estan ok
    const STATUS_CRONJOB_CLOSE_PROCESS = 9;


    /*
     * Column: status_cron_job_send_email
     */
    //Todo Estado cuando todavia no se envia el email de reserva
    const STATUS_CRONJOB_WITHOUT_SEND_EMAIL_RESERVE = 0;
    //Todo Estado para el envio de correo
    const STATUS_CRONJOB_SEND_EMAIL_RESERVE = 1;
    //Todo Estado cuando todos los procesos anteriores terminen
    const STATUS_CRONJOB_SEND_EMAIL_CLOSE_PROCESS = 9;


    /*
     * Column: status_cron_job_error
     */
    //Todo Estado del cronjob sin error
    const STATUS_CRONJOB_ERROR_FALSE = 0;
    //Todo Estado del cronjob con error
    const STATUS_CRONJOB_ERROR_TRUE = 1;
    //Todo Estado del cronjob con error que indica que se envio la notificacion a TI
    const STATUS_CRONJOB_SEND_ERROR_NOTIFICATION = 2;
    /*
     * Column: status_cron_job_order_stella
     */
    //Todo Estado cuando hay un numero de orden para relacion con un file en stella
    const STATUS_CRONJOB_CREATE_RELATIONSHIP_ORDER = 1;
    //Todo Estado cuando el proceso de orden para relacion con un file en stella termino
    const STATUS_CRONJOB_CLOSE_RELATIONSHIP_ORDER_PROCESS = 9;

    /*
     * Column: entity
     */

    const ENTITY_PACKAGE = 'Package';
    const ENTITY_QUOTE = 'Quote';
    const ENTITY_CART = 'Cart';

    public function setConsecutiveHotelPrev()
    {
        $this->consecutive_hotel_prev = $this->reservationsHotel->count();
    }

    public function setConsecutiveservicePrev()
    {
        $this->consecutive_service_prev = $this->reservationsService()->count();
    }


    public function getConsecutiveHotelPrev()
    {
        return $this->consecutive_hotel_prev;
    }

    public function getConsecutiveServicePrev()
    {
        return $this->consecutive_service_prev;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reservationsHotel()
    {
        return $this->hasMany(ReservationsHotel::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reservationsService()
    {
        return $this->hasMany(ReservationsService::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reservationsFlight()
    {
        return $this->hasMany(ReservationsFlight::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reservationsDiscount()
    {
        return $this->hasMany(ReservationsDiscounts::class);
    }


    /**
     * @param array|null $filters
     * @param bool $first
     * @return Reservation[]|\Illuminate\Database\Eloquent\Builder[]|Collection|mixed
     */
    public static function getReservations(array $filters = null, bool $first = false)
    {
        if (!empty($filters['reservation_id'])) {
            $reservations = self::where('id', '=', $filters['reservation_id']);
        } elseif (!empty($filters['file_code'])) {
            $reservations = self::where('file_code', '=', $filters['file_code'])->orWhere('booking_code', '=',
                $filters['file_code']);
        } else {
            $reservations = (new self);
        }

        if (!empty($filters['selected_client'])) {
            $reservations = $reservations->where('client_id', '=', $filters['selected_client']);
        }

        if (!empty($filters['selected_excecutive'])) {
            $reservations = $reservations->where('executive_id', '=', $filters['selected_excecutive']);
        }

        if (!empty($filters['create_date']['from_date'])) {
            $reservations = $reservations->where('created_at', '>=', $filters['create_date']['from_date']);
        }

        if (!empty($filters['create_date']['to_date'])) {
            $reservations = $reservations->where('created_at', '<=', $filters['create_date']['to_date']);
        }

        $reservations = $reservations->when(!empty($filters['reservation_hotel_id']), function ($query) use ($filters) {
            return $query->whereHas('reservationsHotel', function ($query) use ($filters) {
                if (!empty($filters['reservation_hotel_id'])) {
                    if (is_array($filters['reservation_hotel_id'])) {
                        $query->whereIn('id', $filters['reservation_hotel_id']);
                    } else {
                        $query->where('id', '=', $filters['reservation_hotel_id']);
                    }
                }
            });
        })->with([
            'reservationsHotel' => function ($resHotel) use ($filters) {
                if (!empty($filters['reservation_hotel_id'])) {
                    if (is_array($filters['reservation_hotel_id'])) {
                        $resHotel->whereIn('id', $filters['reservation_hotel_id']);
                    } else {
                        $resHotel->where('id', '=', $filters['reservation_hotel_id']);
                    }
                }

                if (isset($filters['process_aurora3']) and !empty($filters['process_aurora3'])) {
                    $resHotel->where('process_aurora3', '=', $filters['process_aurora3']);
                }


                if (!empty($filters['hotel_consecutive_from'])) {
                    $resHotel->where('consecutive', '>=', $filters['hotel_consecutive_from']);
                }

                if (!empty($filters['selected_excecutive'])) {
                    $resHotel->where('executive_id', '=', $filters['selected_excecutive']);
                }

                if (!empty($filters['hotel_id'])) {
                    $resHotel->where('hotel_id', '=', $filters['hotel_id']);
                }

                if (!empty($filters['check_in'])) {
                    $resHotel->where('check_in', '>=', $filters['check_in']);
                }

                if (!empty($filters['check_out'])) {
                    $resHotel->where('check_out', '<=', $filters['check_out']);
                }

                $resHotel->with([
                    'hotel' => function ($hotel) {
                        $hotel->select(['id', 'country_id', 'state_id', 'city_id', 'zone_id', 'typeclass_id']);
                        $hotel->with([
                            'country' => function ($query) {
                                $query->select(['id', 'iso']);
                                $query->with([
                                    'translations' => function ($query) {
                                        $query->select(['object_id', 'value']);
                                        $query->where('type', 'country');
                                        $query->where('language_id', 2);
                                    }
                                ]);
                            }
                        ]);
                        $hotel->with([
                            'state' => function ($query) {
                                $query->select(['id', 'iso']);
                                $query->with([
                                    'translations' => function ($query) {
                                        $query->select(['id', 'object_id', 'value']);
                                        $query->where('language_id', 1);
                                    }
                                ]);
                            }
                        ]);
                        $hotel->with([
                            'city' => function ($query) {
                                $query->select('id', 'iso');
                                $query->with([
                                    'translations' => function ($query) {
                                        $query->select('object_id', 'value');
                                        $query->where('type', 'city');
                                        $query->where('language_id', 1);
                                    },
                                ]);
                            },
                        ]);
                        $hotel->with([
                            'zone' => function ($query) {
                                $query->select('id');
                                $query->with([
                                    'translations' => function ($query) {
                                        $query->select('object_id', 'value');
                                        $query->where('type', 'zone');
                                        $query->where('language_id', 1);
                                    },
                                ]);
                            },
                        ]);
                        $hotel->with([
                            'hoteltypeclass' => function ($query) {
                                $query->with([
                                    'typeclass' => function ($query) {
                                        $query->select('id');
                                        $query->with([
                                            'translations' => function ($query) {
                                                $query->select('object_id', 'value');
                                                $query->where('type', 'typeclass');
                                                $query->where('language_id', 1);
                                            },
                                        ]);
                                    },
                                ]);
                                $query->where('year', date('Y'));
                            }
                        ]);
                    }
                ]);
                $resHotel->with([
                    'reservationsHotelRooms' => function ($hotelRoom) use ($filters) {

                        // $hotelRoom->where('onRequest', '=', 1);

                        if (!empty($filters['reservation_hotel_room_id'])) {
                            if (is_array($filters['reservation_hotel_room_id'])) {
                                $hotelRoom->whereIn('id', $filters['reservation_hotel_room_id']);
                            } else {
                                $hotelRoom->where('id', '=', $filters['reservation_hotel_room_id']);
                            }
                        }

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

                        if (!empty($filters['channel_cancel_by_rooms_hyperguest'])) {
                            $hotelRoom->where('channel_id', '=', 6);
                        }

                        $hotelRoom->with([
                            'rate_plan',
                            'rates_plans_room.date_range_hotel',
                            'reservationHotelCancelPolicies',
                            'room_type' => function ($query) {
                                $query->with([
                                    'translations' => function ($query) {
                                        $query->select(['object_id','value']);
                                        $query->where('type', 'roomtype');
                                        $query->where('slug', 'roomtype_name');
                                        $query->where('language_id', 1);
                                    }
                                ]);
                            },
                            'reservationsHotelsCalendarys' => function ($hotelRoom) {
                                $hotelRoom->with([
                                    'reservationHotelRoomDateRate'
                                ]);
                            },
                            'supplements' => function ($hotelRoom) {
                                $hotelRoom->with([
                                    'calendaries'
                                ]);
                            }
                        ]);
                    },
                ]);

                if (isset($filters['status_email']) and ($filters['status_email'] == ReservationsHotel::STATUS_EMAIL_NOT_SENT or $filters['status_email'] == ReservationsHotel::STATUS_EMAIL_SENT)) {
                    $resHotel->where('status_email', $filters['status_email']);
                }
            },
            'reservationsService' => function ($resService) use ($filters) {
                if (!empty($filters['reservation_service_id'])) {
                    if (is_array($filters['reservation_service_id'])) {
                        $resService->whereIn('id', $filters['reservation_service_id']);
                    } else {
                        $resService->where('id', '=', $filters['reservation_service_id']);
                    }
                }

                if (isset($filters['process_aurora3']) and !empty($filters['process_aurora3'])) {
                    $resService->where('process_aurora3', '=', $filters['process_aurora3']);
                }

                if (!empty($filters['service_consecutive_from'])) {
                    $resService->where('consecutive', '>=', $filters['service_consecutive_from']);
                }

                if (!empty($filters['selected_excecutive'])) {
                    $resService->where('executive_id', '=', $filters['selected_excecutive']);
                }

                if (!empty($filters['service_id'])) {
                    $resService->where('service_id', '=', $filters['service_id']);
                }

                if (!empty($filters['date'])) {
                    $resService->where('date', $filters['date']);
                }


                if (isset($filters['status_email']) and ($filters['status_email'] == ReservationsService::STATUS_EMAIL_NOT_SENT or $filters['status_email'] == ReservationsService::STATUS_EMAIL_SENT)) {
                    $resService->where('status_email', $filters['status_email']);
                }

                $resService->with([
                    'service' => function ($query) {
                        $query->select(['id', 'service_type_id','service_sub_category_id']);
                        $query->with('service_translations');
                        $query->with([
                            'serviceOrigin' => function ($query) {
                                $query->select([
                                    'id',
                                    'service_id',
                                    'country_id',
                                    'state_id',
                                    'city_id',
                                    'zone_id',
                                ]);
                                $query->with([
                                    'country' => function ($query) {
                                        $query->select(['id', 'iso']);
                                        $query->with([
                                            'translations' => function ($query) {
                                                $query->select(['object_id', 'value']);
                                                $query->where('type', 'country');
                                                $query->where('language_id', 2);
                                            }
                                        ]);
                                    }
                                ]);
                                $query->with([
                                    'state' => function ($query) {
                                        $query->select('id', 'iso');
                                        $query->with([
                                            'translations' => function ($query) {
                                                $query->select('object_id', 'value');
                                                $query->where('type', 'state');
                                                $query->where('language_id', 1);
                                            },
                                        ]);
                                    },
                                ]);
                                $query->with([
                                    'city' => function ($query) {
                                        $query->select('id', 'iso');
                                        $query->with([
                                            'translations' => function ($query) {
                                                $query->select('object_id', 'value');
                                                $query->where('type', 'city');
                                                $query->where('language_id', 1);
                                            },
                                        ]);
                                    },
                                ]);
                                $query->with([
                                    'zone' => function ($query) {
                                        $query->select('id', 'in_airport');
                                        $query->with([
                                            'translations' => function ($query) {
                                                $query->select('object_id', 'value');
                                                $query->where('type', 'zone');
                                                $query->where('language_id', 1);
                                            },
                                        ]);
                                    },
                                ]);
                            }
                        ]);
                        $query->with([
                            'serviceDestination' => function ($query) {
                                $query->select([
                                    'id',
                                    'service_id',
                                    'country_id',
                                    'state_id',
                                    'city_id',
                                    'zone_id',
                                ]);
                                $query->with([
                                    'country' => function ($query) {
                                        $query->select(['id', 'iso']);
                                        $query->with([
                                            'translations' => function ($query) {
                                                $query->select(['object_id', 'value']);
                                                $query->where('type', 'country');
                                                $query->where('language_id', 2);
                                            }
                                        ]);
                                    }
                                ]);
                                $query->with([
                                    'state' => function ($query) {
                                        $query->select('id', 'iso');
                                        $query->with([
                                            'translations' => function ($query) {
                                                $query->select('object_id', 'value');
                                                $query->where('type', 'state');
                                                $query->where('language_id', 1);
                                            },
                                        ]);
                                    },
                                ]);
                                $query->with([
                                    'city' => function ($query) {
                                        $query->select('id', 'iso');
                                        $query->with([
                                            'translations' => function ($query) {
                                                $query->select('object_id', 'value');
                                                $query->where('type', 'city');
                                                $query->where('language_id', 1);
                                            },
                                        ]);
                                    },
                                ]);
                                $query->with([
                                    'zone' => function ($query) {
                                        $query->select('id', 'in_airport');
                                        $query->with([
                                            'translations' => function ($query) {
                                                $query->select('object_id', 'value');
                                                $query->where('type', 'zone');
                                                $query->where('language_id', 1);
                                            },
                                        ]);
                                    },
                                ]);

                            }
                        ]);
                        $query->with([
                            'serviceSubCategory.serviceCategories.translations' => function ($query)  {
                                $query->select('id', 'value', 'language_id', 'object_id');
                                $query->where('type', 'servicecategory');
                                $query->where('language_id', 1);
                            }
                        ]);
                        $query->with([
                            'serviceType' => function ($query) {
                                $query->with([
                                    'translations' => function ($query) {
                                        $query->select('object_id', 'value');
                                        $query->where('type', 'servicetype');
                                        $query->where('language_id', 1);
                                    },
                                ]);
                            }
                        ]);
                    }
                ]);

                $resService->with([
                    'reservationsServiceRatesPlans' => function ($serviceRatePlan) use ($filters) {
                        if (!empty($filters['selected_excecutive'])) {
                            $serviceRatePlan->where('executive_id', '=', $filters['selected_excecutive']);
                        }

                        if (!empty($filters['service_id'])) {
                            $serviceRatePlan->where('service_id', '=', $filters['service_id']);
                        }

                        if (!empty($filters['date'])) {
                            $serviceRatePlan->where('date', $filters['date']);
                        }

                        $serviceRatePlan->with('reservationServiceCancelPolicies','service_rates_plans');

                    }
                ]);
            },
            'reservationsPackage' => function ($resPackage) use ($filters) {
                $resPackage->with([
                    'package' => function ($query) {
                        $query->select(['id', 'nights', 'tag_id']);
                        $query->with([
                            'translations' => function ($query) {
                                $query->select(['id', 'package_id', 'tradename']);
                                $query->where('language_id', 1);
                            }
                        ]);
                        $query->with([
                            'galleries' => function ($query) {
                                $query->select('object_id', 'slug', 'url');
                                $query->where('type', 'package');
                            },
                        ]);
                        $query->with([
                            'tag' => function ($query) {
                                $query->select(['id', 'color']);
                                $query->with([
                                    'translations' => function ($query) {
                                        $query->select(['object_id', 'value']);
                                        $query->where('type', 'tag');
                                        $query->where('language_id', 1);
                                    }
                                ]);
                            },
                        ]);
                    }
                ]);
                $resPackage->with([
                    'serviceType' => function ($query) {
                        $query->select(['id']);
                        $query->with([
                            'translations' => function ($query) {
                                $query->select(['id', 'object_id', 'value']);
                                $query->where('language_id', 1);
                            }
                        ]);
                    }
                ]);
                $resPackage->with([
                    'typeClass' => function ($query) {
                        $query->select(['id']);
                        $query->with([
                            'translations' => function ($query) {
                                $query->select(['id', 'object_id', 'value']);
                                $query->where('language_id', 1);
                            }
                        ]);
                    }
                ]);
            },
            'executive' => function ($query) {
                $query->select(['id', 'code']);
            },
            'create_user' => function ($query) {
                $query->select(['id', 'code']);
            },
            'client' => function ($resClient) use ($filters) {
                $resClient->select(['id', 'name', 'have_credit' , 'credit_line' , 'language_id', 'executive_code as kam']);
                $resClient->with([
                    'languages' => function ($query) {
                        $query->select(['id','iso']);
                    },
                    'markups' => function ($query) {
                        $query->where('period',  date('Y'));
                    }
                ]);
            },
            'reservationsFlight' => function ($resService) use ($filters) {
                if (isset($filters['status_email']) and ($filters['status_email'] == reservationsFlight::STATUS_EMAIL_NOT_SENT or $filters['status_email'] == reservationsFlight::STATUS_EMAIL_SENT)) {
                    $resService->where('status_email', $filters['status_email']);
                }

                if (isset($filters['process_aurora3']) and !empty($filters['process_aurora3'])) {
                    $resService->where('process_aurora3', '=', $filters['process_aurora3']);
                }
            }
        ])->with([
            'reservationsPassenger' => function ($resPassenger) {
                $resPassenger->with('document_type');
            }
        ])->with([
            'billing' => function ($resPassenger) {
                $resPassenger->with('document_type');
                $resPassenger->with('country');
                $resPassenger->with('state');
            }
        ])->get();

        return $first ? $reservations->first() : $reservations;
    }

    public function reservations_active_count($date_validate)
    {
        $reservation_hotel_rate_plan_room_ids = \App\ReservationsHotelsRatesPlansRooms::where('check_in', '>=',
            $date_validate)
            ->where(function ($query) {
                $query->where('channel_id',  1)
                      ->orWhere('status_in_channel', 1);
            })
            ->where('channel_code', 'AURORA')
            ->where('onRequest', 1)
            ->pluck('id');

        $reservation_hotel_rate_plan_calendars = \App\ReservationsHotelsRatesPlansRoomsCalendarys::where('date', '>=',
            $date_validate)
            ->where('update_inventory_reserve', 0)
            ->whereIn('reservations_hotels_rates_plans_rooms_id', $reservation_hotel_rate_plan_room_ids)
            ->with('reservations_hotels_rates_plans_rooms')
            ->get([
                'id',
                'reservations_hotels_rates_plans_rooms_id',
                'rates_plans_calendary_id',
                'rates_plans_room_id',
                'policies_rate_id',
                'date',
                'update_inventory_reserve',
                'create_user_id',
                'update_inventory_cancelled'
        ]);

        //TODO: agrupar las reservas en la misma fecha y sumar las habitaciones para hacer una sola actualizacion de inventario
        DB::transaction(function () use ($reservation_hotel_rate_plan_calendars) {

            try {
                // agrupamos por hotel para hacer el conteo de pasajeros y vaildar si son fits( pasajeros < 15 decuenta ) pasajeros>15 pasa como  onRequest no debe de descontar
                $reservations_hotels = $reservation_hotel_rate_plan_calendars->groupBy('reservations_hotels_rates_plans_rooms.reservations_hotel_id');
                foreach($reservations_hotels as $reservations_hotel_id => $reservations_hotel){

                    // agrupamos por reservations_hotels_rates_plans
                    $rates_plans_rooms = $reservations_hotel->groupBy('reservations_hotels_rates_plans_rooms_id');

                    $esFits = $this->totalPaxFix($rates_plans_rooms);

                    foreach($rates_plans_rooms as $reservations_hotels_rates_plans_rooms_id => $reservation_hotel_rate_plan_calendars ){

                        $errorALLOTMENTS = false;
                        foreach ($reservation_hotel_rate_plan_calendars as $reservation_hotel_rate_plan_calendar)
                        {

                            $rate_plan_room = \App\RatesPlansRooms::find($reservation_hotel_rate_plan_calendar['rates_plans_room_id']);
                            if ($rate_plan_room !== null) {
                                $user = \App\User::find($reservation_hotel_rate_plan_calendar['create_user_id']);

                                //si esta adentro de una bolsa
                                if ($rate_plan_room->bag == 1) {
                                    $bag_rates = \App\BagRate::where('rate_plan_rooms_id',
                                        $reservation_hotel_rate_plan_calendar['rates_plans_room_id'])->first();
                                    if ($bag_rates) {
                                        $bag_room_id = $bag_rates->bag_room_id;
                                        $inventory_bag = \App\InventoryBag::where('bag_room_id', '=', $bag_room_id)
                                            ->where('date', '=', $reservation_hotel_rate_plan_calendar->date)
                                            ->first();
                                        if ($inventory_bag) {

                                            if($inventory_bag->inventory_num>0){

                                                if($esFits){
                                                    $inventory_bag->inventory_num = $inventory_bag->inventory_num - 1;
                                                    $inventory_bag->total_booking = $inventory_bag->total_booking + 1;
                                                    $inventory_bag->save();

                                                    $this->updateCalendaryInventoryReservation($reservation_hotel_rate_plan_calendar['id'], ReservationsHotelsRatesPlansRoomsCalendarys::UPDATE_INVENTORY_SUCCESS );

                                                    activity()
                                                        ->performedOn(\App\InventoryBag::find($inventory_bag->id))
                                                        ->causedBy($user)
                                                        ->withProperties(['date_inventory' => $reservation_hotel_rate_plan_calendar['date']])
                                                        ->log('El usuario '.$user->name.' ha realizado una reserva de 1 para el dia '.$reservation_hotel_rate_plan_calendar['date']);
                                                }else{
                                                    $errorALLOTMENTS = true;
                                                    $this->updateCalendaryInventoryReservation($reservation_hotel_rate_plan_calendar['id'], ReservationsHotelsRatesPlansRoomsCalendarys::UPDATE_INVENTORY_NO_FITS );
                                                }
                                            }else{
                                                $errorALLOTMENTS = true;
                                                $this->updateCalendaryInventoryReservation($reservation_hotel_rate_plan_calendar['id'], ReservationsHotelsRatesPlansRoomsCalendarys::UPDATE_INVENTORY_NO_ALLOTMENTS );
                                            }

                                        } else {
                                            $errorALLOTMENTS = true;
                                            $this->updateCalendaryInventoryReservation($reservation_hotel_rate_plan_calendar['id'], ReservationsHotelsRatesPlansRoomsCalendarys::UPDATE_INVENTORY_NOT_FOUND );
                                        }
                                    }else{
                                        $errorALLOTMENTS = true;
                                        $this->updateCalendaryInventoryReservation($reservation_hotel_rate_plan_calendar['id'], ReservationsHotelsRatesPlansRoomsCalendarys::UPDATE_INVENTORY_NOT_FOUND );
                                    }

                                }
                                //Si no esta adentro de una bolsa
                                if ($rate_plan_room->bag == 0) {
                                    //Buscar inventario y actualizarlo
                                    $inventory = \App\Inventory::where('date', $reservation_hotel_rate_plan_calendar->date)
                                        ->where('rate_plan_rooms_id',
                                            $reservation_hotel_rate_plan_calendar['rates_plans_room_id'])
                                        ->first();
                                    if ($inventory != null) {
                                        if($inventory->inventory_num>0){
                                            if($esFits){
                                                $inventory->inventory_num = $inventory->inventory_num - 1;
                                                $inventory->total_booking = $inventory->total_booking + 1;
                                                $inventory->save();

                                                $this->updateCalendaryInventoryReservation($reservation_hotel_rate_plan_calendar['id'], ReservationsHotelsRatesPlansRoomsCalendarys::UPDATE_INVENTORY_SUCCESS );

                                                activity()
                                                    ->performedOn(\App\Inventory::find($inventory->id))
                                                    ->causedBy($user)
                                                    ->withProperties(['date_inventory' => $reservation_hotel_rate_plan_calendar['date']])
                                                    ->log('El usuario '.$user->name.' ha realizado una reserva de 1 para el dia '.$reservation_hotel_rate_plan_calendar['date']);

                                            }else{
                                                $errorALLOTMENTS = true;
                                                $this->updateCalendaryInventoryReservation($reservation_hotel_rate_plan_calendar['id'], ReservationsHotelsRatesPlansRoomsCalendarys::UPDATE_INVENTORY_NO_FITS );
                                            }
                                        }else{
                                            $errorALLOTMENTS = true;
                                            $this->updateCalendaryInventoryReservation($reservation_hotel_rate_plan_calendar['id'], ReservationsHotelsRatesPlansRoomsCalendarys::UPDATE_INVENTORY_NO_ALLOTMENTS );
                                        }
                                    } else {
                                        $errorALLOTMENTS = true;
                                        $this->updateCalendaryInventoryReservation($reservation_hotel_rate_plan_calendar['id'], ReservationsHotelsRatesPlansRoomsCalendarys::UPDATE_INVENTORY_NOT_FOUND );
                                    }
                                }
                            }else{
                                // si no existe el rates_plans_room_id esta reserva pasa automaticamente como onRequest = 0
                                $errorALLOTMENTS = true;
                            }
                        }
                        // actualizamos el campo onRequest del ReservationsHotelsRatesPlansRooms si hubo problemas/restricciones al descontar.
                        $this->updateOnRequestReservationsHotelsRatesPlansRooms($reservations_hotels_rates_plans_rooms_id,$errorALLOTMENTS);

                    }

                }


            } catch (\Exception $e) {
                return print_r($e->getMessage().' => '.' data => '.json_encode($reservation_hotel_rate_plan_calendar).' line -> '.$e->getLine());
            }

        });


    }

    public function updateCalendaryInventoryReservation($reservation_hotel_rate_plan_calendar_id, $estado){
        $update_inventory_reservation = ReservationsHotelsRatesPlansRoomsCalendarys::find($reservation_hotel_rate_plan_calendar_id);
        $update_inventory_reservation->update_inventory_reserve = $estado;
        $update_inventory_reservation->save();
    }

    public function updateOnRequestReservationsHotelsRatesPlansRooms($reservations_hotels_rates_plans_rooms_id,$errorALLOTMENTS){

        $reservation_hotel_rate_plan_room = \App\ReservationsHotelsRatesPlansRooms::find($reservations_hotels_rates_plans_rooms_id);
        if($errorALLOTMENTS){
            $reservation_hotel_rate_plan_room->onRequest = 0;
        }
        $reservation_hotel_rate_plan_room->save();
    }

    public function totalPaxFix($rates_plans_rooms){
        $totalPasajeros = 0;
        foreach($rates_plans_rooms as $rates_plans_room){
            // cada item de calendario tiene el mismo rateplanroom a si que el adult_num siempre sera lo mismo
            $totalPasajeros = $totalPasajeros + $rates_plans_room[0]->reservations_hotels_rates_plans_rooms->adult_num;
        }

        if($totalPasajeros<15)
            return true;
        else
            return false;

    }

    public function errorValidateDiscountReserveHotel(){

        $errorDiscountAllotments = false;
        foreach ($this->reservationsHotel as $reservationsHotels) {
            foreach($reservationsHotels->reservationsHotelRooms as $reservationsHotelRooms ){
                if($reservationsHotelRooms->channel_code == "AURORA" and $reservationsHotelRooms->onRequest == "1"){ // verificamos las reservas confirmadas que ayan pasado por el proceso de descuento
                    foreach($reservationsHotelRooms->reservationsHotelsCalendarys as $calendario){
                        if(!$calendario->update_inventory_reserve){
                            $errorDiscountAllotments = true;
                            break 3;
                        }
                    }
                }
            }
        }

        return $errorDiscountAllotments;
    }


    public function reservation_canceled_count()
    {
        $reservations = ReservationsHotelsRatesPlansRoomsCalendarys::where('date', '>=', date('Y-m-d'))
            // $reservations = ReservationsHotelsRatesPlansRoomsCalendarys::where('date', '=', '2020-07-03')
            ->where('update_inventory_cancelled', 0)
            ->with('reservations_hotels_rates_plans_rooms')
            ->get([
                'id',
                'reservations_hotels_rates_plans_rooms_id',
                'rates_plans_calendary_id',
                'rates_plans_room_id',
                'policies_rate_id',
                'date',
                'update_inventory_reserve',
                'create_user_id',
                'update_inventory_cancelled'
            ])
            ->groupBy(['reservations_hotels_rates_plans_rooms.rates_plans_room_id', 'date']);

        $reservations_cancel = [];
        foreach ($reservations as $rates_plans_room_id => $reservation_dates) {
            if (!$rates_plans_room_id) {
                continue;
            }
            foreach ($reservation_dates as $date => $item) {
                if ((
                        $item[0]['reservations_hotels_rates_plans_rooms']['channel_code'] == 'AURORA' and
                        $item[0]['reservations_hotels_rates_plans_rooms']['status'] == 0
                    ) or (
                        $item[0]['reservations_hotels_rates_plans_rooms']['channel_code'] != 'AURORA' and
                        $item[0]['reservations_hotels_rates_plans_rooms']['status_in_channel'] == 0
                    )) {

                    if (!isset($reservations_cancel[$rates_plans_room_id . '|' . $date])) {
                        $reservations_cancel[$rates_plans_room_id . '|' . $date] = [
                            'rate_plan_rooms_id' => $rates_plans_room_id,
                            'reservations_hotels_rates_plans_rooms_calendar_id' => $item[0]['id'],
                            'date' => $date,
                            'create_user_id' => $item[0]['create_user_id'],
                            'total_canceled' => 0
                        ];
                    }


                    $reservations_cancel[$rates_plans_room_id . '|' . $date]['total_canceled'] += $item->count();
                }
            }
        }

        DB::transaction(function () use ($reservations_cancel) {
            foreach ($reservations_cancel as $reservations_cancel_data) {
                try {
                    $rates_plans_rooms = RatesPlansRooms::find($reservations_cancel_data['rate_plan_rooms_id']);
                    if ($rates_plans_rooms !== null) {
                        $user = User::where('id', $reservations_cancel_data['create_user_id'])->withTrashed()->first();
                        if ($rates_plans_rooms->bag == 1) {

                            $bag_rates = BagRate::where('rate_plan_rooms_id',
                                $reservations_cancel_data['rate_plan_rooms_id'])->first();
                            $bag_room_id = $bag_rates->bag_room_id;

                            $affectedRows = \App\InventoryBag::where('bag_room_id', '=', $bag_room_id)
                                ->where('date', '=', $reservations_cancel_data['date'])
                                ->update(array('total_canceled' => $reservations_cancel_data['total_canceled']));
                            if ($affectedRows) {
                                $inventory_bag_id = \App\InventoryBag::where('bag_room_id', '=', $bag_room_id)
                                    ->where('date', '=', $reservations_cancel_data['date'])->first();

                                $inventory = ReservationsHotelsRatesPlansRoomsCalendarys::find($reservations_cancel_data['reservations_hotels_rates_plans_rooms_calendar_id']);
                                $inventory->update_inventory_cancelled = 1;
                                $inventory->save();

                                activity()
                                    ->performedOn(\App\InventoryBag::find($inventory_bag_id->id))
                                    ->causedBy($user)
                                    ->withProperties(['date_inventory' => $reservations_cancel_data['date']])
                                    ->log('El usuario ' . $user->name . ' ha cancelado una reserva de ' . $reservations_cancel_data['total_canceled'] . ' para el dia ' . $reservations_cancel_data['date']);
                            }
                        } else {

                            $affectedRows = \App\Inventory::where('rate_plan_rooms_id', '=',
                                $reservations_cancel_data['rate_plan_rooms_id'])
                                ->where('date', '=', $reservations_cancel_data['date'])
                                ->update(array('total_canceled' => $reservations_cancel_data['total_canceled']));
                            if ($affectedRows) {
                                $inventory_id = \App\Inventory::where('rate_plan_rooms_id', '=',
                                    $reservations_cancel_data['rate_plan_rooms_id'])
                                    ->where('date', '=', $reservations_cancel_data['date'])->first();

                                $inventory = ReservationsHotelsRatesPlansRoomsCalendarys::find($reservations_cancel_data['reservations_hotels_rates_plans_rooms_calendar_id']);
                                $inventory->update_inventory_cancelled = 1;
                                $inventory->save();

                                activity()
                                    ->performedOn(\App\Inventory::find($inventory_id->id))
                                    ->causedBy($user)
                                    ->withProperties(['date_inventory' => $reservations_cancel_data['date']])
                                    ->log('El usuario ' . $user->name . ' ha cancelado una reserva de ' . $reservations_cancel_data['total_canceled'] . ' para el dia ' . $reservations_cancel_data['date']);
                            }

                        }
                    }
                } catch (\Exception $exception) {
                    return print_r($exception->getMessage() . ' => ' . ' data => ' . json_encode($reservations_cancel_data) . ' line -> ' . $exception->getLine());
                }
            }
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reservationsPassenger()
    {
        return $this->hasMany(ReservationPassenger::class);
    }

    public function billing()
    {
        return $this->belongsTo(ReservationBilling::class, 'reservation_billing_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public static function getAllReservationPaginate(array $filters)
    {
        if (!empty($filters['reservation_id'])) {
            $reservations = self::where('id', '=', $filters['reservation_id']);
        } elseif (!empty($filters['file_code'])) {
            $reservations = self::where('file_code', '=', $filters['file_code'])->orWhere('booking_code', '=',
                $filters['file_code']);
        } else {
            $reservations = (new self);
        }

        if (!empty($filters['selected_client'])) {
            $reservations = $reservations->where('client_id', '=', $filters['selected_client']);
        }

        if (!empty($filters['selected_excecutive'])) {
            $reservations = $reservations->where('executive_id', '=', $filters['selected_excecutive']);
        }


        if (!empty($filters['create_date'])) {
            $reservations = $reservations->whereDate('created_at', '=', $filters['create_date']);
        }

        if (!empty($filters['status_reserve'])) {
            $reservations = $reservations->where('status_cron_job_reservation_stella', $filters['status_reserve']);
        }

        if (!empty($filters['status_error'])) {
            $reservations = $reservations->where('status_cron_job_error', $filters['status_error']);
        }

        $reservations = $reservations->when(!empty($filters['reservation_hotel_id']), function ($query) use ($filters) {
            return $query->whereHas('reservationsHotel', function ($query) use ($filters) {
                if (!empty($filters['reservation_hotel_id'])) {
                    if (is_array($filters['reservation_hotel_id'])) {
                        $query->whereIn('id', $filters['reservation_hotel_id']);
                    } else {
                        $query->where('id', '=', $filters['reservation_hotel_id']);
                    }
                }
            });
        })->with([
            'reservationsHotel' => function ($resHotel) use ($filters) {
                if (!empty($filters['reservation_hotel_id'])) {
                    if (is_array($filters['reservation_hotel_id'])) {
                        $resHotel->whereIn('id', $filters['reservation_hotel_id']);
                    } else {
                        $resHotel->where('id', '=', $filters['reservation_hotel_id']);
                    }
                }

                if (!empty($filters['hotel_consecutive_from'])) {
                    $resHotel->where('consecutive', '>=', $filters['hotel_consecutive_from']);
                }


                if (!empty($filters['selected_excecutive'])) {
                    $resHotel->where('executive_id', '=', $filters['selected_excecutive']);
                }

                if (!empty($filters['hotel_id'])) {
                    $resHotel->where('hotel_id', '=', $filters['hotel_id']);
                }

                if (!empty($filters['check_in'])) {
                    $resHotel->where('check_in', '>=', $filters['check_in']);
                }

                if (!empty($filters['check_out'])) {
                    $resHotel->where('check_out', '<=', $filters['check_out']);
                }

                $resHotel->with([
                    'reservationsHotelRooms' => function ($hotelRoom) use ($filters) {

                        // $hotelRoom->where('onRequest', '=', 1);

                        if (!empty($filters['reservation_hotel_room_id'])) {
                            if (is_array($filters['reservation_hotel_room_id'])) {
                                $hotelRoom->whereIn('id', $filters['reservation_hotel_room_id']);
                            } else {
                                $hotelRoom->where('id', '=', $filters['reservation_hotel_room_id']);
                            }
                        }

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
                            'user_cancel',
                            'reservationHotelCancelPolicies',
                            'reservationsHotelsCalendarys' => function ($hotelRoom) {
                                $hotelRoom->with([
                                    'reservationHotelRoomDateRate'
                                ]);
                            },
                            'supplements' => function ($hotelRoom) {
                                $hotelRoom->with([
                                    'calendaries'
                                ]);
                            }
                        ]);
                    },
                ]);
            },
            'reservationsService' => function ($resService) use ($filters) {
                if (!empty($filters['reservation_service_id'])) {
                    if (is_array($filters['reservation_service_id'])) {
                        $resService->whereIn('id', $filters['reservation_service_id']);
                    } else {
                        $resService->where('id', '=', $filters['reservation_service_id']);
                    }
                }

                if (!empty($filters['service_consecutive_from'])) {
                    $resService->where('consecutive', '>=', $filters['service_consecutive_from']);
                }

                if (!empty($filters['selected_excecutive'])) {
                    $resService->where('executive_id', '=', $filters['selected_excecutive']);
                }

                if (!empty($filters['service_id'])) {
                    $resService->where('service_id', '=', $filters['service_id']);
                }

                if (!empty($filters['date'])) {
                    $resService->where('date', $filters['date']);
                }

                $resService->with([
                    'reservationsServiceRatesPlans' => function ($serviceRatePlan) use ($filters) {
                        if (!empty($filters['selected_excecutive'])) {
                            $serviceRatePlan->where('executive_id', '=', $filters['selected_excecutive']);
                        }

                        if (!empty($filters['service_id'])) {
                            $serviceRatePlan->where('service_id', '=', $filters['service_id']);
                        }

                        if (!empty($filters['date'])) {
                            $serviceRatePlan->where('date', $filters['date']);
                        }

                        $serviceRatePlan->with('reservationServiceCancelPolicies');

                    }
                ]);
            },
            'reservationsPackage' => function ($resPackage) use ($filters) {
                $resPackage->with([
                    'package' => function ($query) {
                        $query->select(['id', 'nights', 'tag_id']);
                        $query->with([
                            'translations' => function ($query) {
                                $query->select(['id', 'package_id', 'tradename']);
                                $query->where('language_id', 1);
                            }
                        ]);
                        $query->with([
                            'galleries' => function ($query) {
                                $query->select('object_id', 'slug', 'url');
                                $query->where('type', 'package');
                            },
                        ]);
                        $query->with([
                            'tag' => function ($query) {
                                $query->select(['id', 'color']);
                                $query->with([
                                    'translations' => function ($query) {
                                        $query->select(['object_id', 'value']);
                                        $query->where('type', 'tag');
                                        $query->where('language_id', 1);
                                    }
                                ]);
                            },
                        ]);
                    }
                ]);
                $resPackage->with([
                    'serviceType' => function ($query) {
                        $query->select(['id']);
                        $query->with([
                            'translations' => function ($query) {
                                $query->select(['id', 'object_id', 'value']);
                                $query->where('language_id', 1);
                            }
                        ]);
                    }
                ]);
                $resPackage->with([
                    'typeClass' => function ($query) {
                        $query->select(['id']);
                        $query->with([
                            'translations' => function ($query) {
                                $query->select(['id', 'object_id', 'value']);
                                $query->where('language_id', 1);
                            }
                        ]);
                    }
                ]);
            },
            'reservationsEmailLogs' => function ($resLogs) {
                $resLogs->select(['id', 'reservation_id', 'email_type', 'email_to', 'emails', 'created_at']);
                $resLogs->orderBy('created_at', 'desc');
            },
        ])->with('reservationsFlight')
            ->with([
                'reservationsPassenger' => function ($resPassenger) {
                    $resPassenger->with('document_type');
                }
            ])->with([
                'billing' => function ($resBilling) {
                    $resBilling->with([
                        'document_type' => function ($query) {
                            $query->select(['id', 'iso']);
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select(['object_id', 'value']);
                                    $query->where('type', 'docs');
                                    $query->where('language_id', 1);
                                }
                            ]);
                        }
                    ]);
                    $resBilling->with([
                        'country' => function ($query) {
                            $query->select(['id', 'iso_ifx']);
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select(['object_id', 'value']);
                                    $query->where('type', 'country');
                                    $query->where('language_id', 1);
                                }
                            ]);
                        }
                    ]);
                    $resBilling->with([
                        'state' => function ($query) {
                            $query->select(['id', 'iso']);
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select(['object_id', 'value']);
                                    $query->where('type', 'state');
                                    $query->where('language_id', 1);
                                }
                            ]);
                        }
                    ]);
                }
            ])->with([
                'client' => function ($query) {
                    $query->select(['id', 'code', 'name', 'market_id']);
                }
            ])->with([
                'executive' => function ($query) {
                    $query->select(['id', 'code', 'name', 'email']);
                    $query->with('markets');
                }
            ])->with([
                'create_user' => function ($query) {
                    $query->select(['id', 'code', 'name', 'email']);
                }
            ]);

        $count = $reservations->count();
        if ($filters['page'] === 1) {
            $reservations = $reservations->take($filters['limit'])->orderBy('id', 'desc')->get();
        } else {
            $reservations = $reservations->skip($filters['limit'] * ($filters['page'] - 1))->take($filters['limit'])
                ->orderBy('id', 'desc')->get();
        }

        $reservations = $reservations->each(function ($item, $key) {
            if ($item->reservationsEmailLogs != null) {
                $item->reservationsEmailLogs->each(function ($item, $key) {
                    $item->emails = json_decode($item->emails, true);
                });
            }
            $item->logs = [];
        });

        return [
            'data' => $reservations,
            'count' => $count,
        ];
    }

    public static function getReservationByCode(array $filters)
    {
        if (!empty($filters['reservation_id'])) {
            $reservations = self::where('id', '=', $filters['reservation_id']);
        } elseif (!empty($filters['file_code'])) {

            $reservations = self::where(function ($query) use ($filters) {
                $query->where('file_code', '=', $filters['file_code'])
                    ->orWhere('booking_code', '=', $filters['file_code']);
            });

        } else {
            $reservations = (new self);
        }

        if (!empty($filters['selected_excecutive'])) {
            $reservations = $reservations->where('executive_id', '=', $filters['selected_excecutive']);
        }

        if (!empty($filters['client_id'])) {
            $reservations = $reservations->where('client_id', '=', $filters['client_id']);
        }

        if (!empty($filters['create_date'])) {
            $reservations = $reservations->whereDate('created_at', '=', $filters['create_date']);
        }

        if (!empty($filters['status_reserve'])) {
            $reservations = $reservations->where('status_cron_job_reservation_stella', $filters['status_reserve']);
        }

        if (!empty($filters['status_error'])) {
            $reservations = $reservations->where('status_cron_job_error', $filters['status_error']);
        }

        $reservations = $reservations->when(!empty($filters['reservation_hotel_id']), function ($query) use ($filters) {
            return $query->whereHas('reservationsHotel', function ($query) use ($filters) {
                if (!empty($filters['reservation_hotel_id'])) {
                    if (is_array($filters['reservation_hotel_id'])) {
                        $query->whereIn('id', $filters['reservation_hotel_id']);
                    } else {
                        $query->where('id', '=', $filters['reservation_hotel_id']);
                    }
                }
            });
        })->with([
            'reservationsHotel' => function ($resHotel) use ($filters) {
                if (!empty($filters['reservation_hotel_id'])) {
                    if (is_array($filters['reservation_hotel_id'])) {
                        $resHotel->whereIn('id', $filters['reservation_hotel_id']);
                    } else {
                        $resHotel->where('id', '=', $filters['reservation_hotel_id']);
                    }
                }

                if (!empty($filters['hotel_consecutive_from'])) {
                    $resHotel->where('consecutive', '>=', $filters['hotel_consecutive_from']);
                }


                if (!empty($filters['selected_excecutive'])) {
                    $resHotel->where('executive_id', '=', $filters['selected_excecutive']);
                }

                if (!empty($filters['hotel_id'])) {
                    $resHotel->where('hotel_id', '=', $filters['hotel_id']);
                }

                if (!empty($filters['check_in'])) {
                    $resHotel->where('check_in', '>=', $filters['check_in']);
                }

                if (!empty($filters['check_out'])) {
                    $resHotel->where('check_out', '<=', $filters['check_out']);
                }

                $resHotel->with([
                    'reservationsHotelRooms' => function ($hotelRoom) use ($filters) {

                        // $hotelRoom->where('onRequest', '=', 1);

                        if (!empty($filters['reservation_hotel_room_id'])) {
                            if (is_array($filters['reservation_hotel_room_id'])) {
                                $hotelRoom->whereIn('id', $filters['reservation_hotel_room_id']);
                            } else {
                                $hotelRoom->where('id', '=', $filters['reservation_hotel_room_id']);
                            }
                        }

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
                            'user_cancel',
                            'reservationHotelCancelPolicies',
                            'reservationsHotelsCalendarys' => function ($hotelRoom) {
                                $hotelRoom->with([
                                    'reservationHotelRoomDateRate'
                                ]);
                            },
                            'supplements' => function ($hotelRoom) {
                                $hotelRoom->with([
                                    'calendaries'
                                ]);
                            }
                        ]);
                    },
                ]);
            },
            'reservationsService' => function ($resService) use ($filters) {
                if (!empty($filters['reservation_service_id'])) {
                    if (is_array($filters['reservation_service_id'])) {
                        $resService->whereIn('id', $filters['reservation_service_id']);
                    } else {
                        $resService->where('id', '=', $filters['reservation_service_id']);
                    }
                }

                if (!empty($filters['service_consecutive_from'])) {
                    $resService->where('consecutive', '>=', $filters['service_consecutive_from']);
                }

                if (!empty($filters['selected_excecutive'])) {
                    $resService->where('executive_id', '=', $filters['selected_excecutive']);
                }

                if (!empty($filters['service_id'])) {
                    $resService->where('service_id', '=', $filters['service_id']);
                }

                if (!empty($filters['date'])) {
                    $resService->where('date', $filters['date']);
                }

                $resService->with([
                    'reservationsServiceRatesPlans' => function ($serviceRatePlan) use ($filters) {
                        if (!empty($filters['selected_excecutive'])) {
                            $serviceRatePlan->where('executive_id', '=', $filters['selected_excecutive']);
                        }

                        if (!empty($filters['service_id'])) {
                            $serviceRatePlan->where('service_id', '=', $filters['service_id']);
                        }

                        if (!empty($filters['date'])) {
                            $serviceRatePlan->where('date', $filters['date']);
                        }

                        $serviceRatePlan->with('reservationServiceCancelPolicies');

                    }
                ]);
            },
            'reservationsPackage' => function ($resPackage) use ($filters) {
                $resPackage->with([
                    'package' => function ($query) {
                        $query->select(['id', 'nights', 'tag_id']);
                        $query->with([
                            'translations' => function ($query) {
                                $query->select(['id', 'package_id', 'tradename']);
                                $query->where('language_id', 1);
                            }
                        ]);
                        $query->with([
                            'galleries' => function ($query) {
                                $query->select('object_id', 'slug', 'url');
                                $query->where('type', 'package');
                            },
                        ]);
                        $query->with([
                            'tag' => function ($query) {
                                $query->select(['id', 'color']);
                                $query->with([
                                    'translations' => function ($query) {
                                        $query->select(['object_id', 'value']);
                                        $query->where('type', 'tag');
                                        $query->where('language_id', 1);
                                    }
                                ]);
                            },
                        ]);
                    }
                ]);
                $resPackage->with([
                    'serviceType' => function ($query) {
                        $query->select(['id']);
                        $query->with([
                            'translations' => function ($query) {
                                $query->select(['id', 'object_id', 'value']);
                                $query->where('language_id', 1);
                            }
                        ]);
                    }
                ]);
                $resPackage->with([
                    'typeClass' => function ($query) {
                        $query->select(['id']);
                        $query->with([
                            'translations' => function ($query) {
                                $query->select(['id', 'object_id', 'value']);
                                $query->where('language_id', 1);
                            }
                        ]);
                    }
                ]);
            },
            'reservationsEmailLogs' => function ($resLogs) {
                $resLogs->select(['id', 'reservation_id', 'email_type', 'email_to', 'emails', 'created_at']);
                $resLogs->orderBy('created_at', 'desc');
            },
        ])->with('reservationsFlight')
            ->with([
                'reservationsPassenger' => function ($resPassenger) {
                    $resPassenger->with('document_type');
                }
            ])->with([
                'billing' => function ($resBilling) {
                    $resBilling->with([
                        'document_type' => function ($query) {
                            $query->select(['id', 'iso']);
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select(['object_id', 'value']);
                                    $query->where('type', 'docs');
                                    $query->where('language_id', 1);
                                }
                            ]);
                        }
                    ]);
                    $resBilling->with([
                        'country' => function ($query) {
                            $query->select(['id', 'iso_ifx']);
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select(['object_id', 'value']);
                                    $query->where('type', 'country');
                                    $query->where('language_id', 1);
                                }
                            ]);
                        }
                    ]);
                    $resBilling->with([
                        'state' => function ($query) {
                            $query->select(['id', 'iso']);
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select(['object_id', 'value']);
                                    $query->where('type', 'state');
                                    $query->where('language_id', 1);
                                }
                            ]);
                        }
                    ]);
                }
            ])->with([
                'executive' => function ($query) {
                    $query->select(['id', 'code', 'name', 'email']);
                    $query->with('markets');
                }
            ])->with([
                'create_user' => function ($query) {
                    $query->select(['id', 'code', 'name', 'email']);
                }
            ]);

        $reservations = $reservations->get();
        $reservations = $reservations->each(function ($item, $key) {
            if ($item->reservationsEmailLogs != null) {
                $item->reservationsEmailLogs->each(function ($item, $key) {
                    $item->emails = json_decode($item->emails, true);
                });
            }
            $item->logs = [];
        });

        return $reservations;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reservationsPackage()
    {
        return $this->hasMany(ReservationsPackage::class);
    }

    public function executive()
    {
        return $this->belongsTo(User::class, 'executive_id', 'id');
    }

    public function create_user()
    {
        return $this->belongsTo(User::class, 'create_user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reservationsEmailLogs()
    {
        return $this->hasMany(ReservationsEmailsLog::class);
    }

}

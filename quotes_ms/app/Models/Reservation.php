<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Reservation extends Model
{
    use SoftDeletes;

    public const STATUS_CRONJOB_CREATE_BILLING_DATA = 1;

    public const STATUS_CRONJOB_CREATE_FILE = 2;

    /*
     * Column: status_cron_job_reservation_stella
     */
    //Todo Estado para crear en el file datos de facturacion (solo para el uso de pago con tarjeta)
    public const STATUS_CRONJOB_CREATE_ACCOUNTING_SEAT = 3;

    //Todo Estado para la creacion de file
    public const STATUS_CRONJOB_CLOSE_PROCESS = 9;

    //Todo Estado para creacion de asiento contable (solo para el uso de pago con tarjeta)
    public const STATUS_CRONJOB_WITHOUT_SEND_EMAIL_RESERVE = 0;

    //Todo Estado cuando todos los procesos estan ok
    public const STATUS_CRONJOB_SEND_EMAIL_RESERVE = 1;

    /*
     * Column: status_cron_job_send_email
     */
    //Todo Estado cuando todavia no se envia el email de reserva
    public const STATUS_CRONJOB_SEND_EMAIL_CLOSE_PROCESS = 9;

    //Todo Estado para el envio de correo
    public const STATUS_CRONJOB_ERROR_FALSE = 0;

    //Todo Estado cuando todos los procesos anteriores terminen
    public const STATUS_CRONJOB_ERROR_TRUE = 1;

    /*
     * Column: status_cron_job_error
     */
    //Todo Estado del cronjob sin error
    public const STATUS_CRONJOB_SEND_ERROR_NOTIFICATION = 2;

    //Todo Estado del cronjob con error
    public const STATUS_CRONJOB_CREATE_RELATIONSHIP_ORDER = 1;

    //Todo Estado del cronjob con error que indica que se envio la notificacion a TI
    public const STATUS_CRONJOB_CLOSE_RELATIONSHIP_ORDER_PROCESS = 9;

    /*
     * Column: status_cron_job_order_stella
     */
    //Todo Estado cuando hay un numero de orden para relacion con un file en stella
    public const ENTITY_PACKAGE = 'Package';

    //Todo Estado cuando el proceso de orden para relacion con un file en stella termino
    public const ENTITY_QUOTE = 'Quote';

    /*
     * Column: entity
     */
    public const ENTITY_CART = 'Cart';

    public $consecutive_hotel_prev;

    public $consecutive_service_prev;

    /**
     * @return Reservation[]|Builder[]|Collection|mixed
     */
    public static function getReservations(array $filters = null, bool $first = false): mixed
    {
        if (! empty($filters['reservation_id'])) {
            $reservations = self::where('id', '=', $filters['reservation_id']);
        } elseif (! empty($filters['file_code'])) {
            $reservations = self::where('file_code', '=', $filters['file_code'])->orWhere(
                'booking_code',
                '=',
                $filters['file_code']
            );
        } else {
            $reservations = (new self());
        }

        if (! empty($filters['selected_client'])) {
            $reservations = $reservations->where('client_id', '=', $filters['selected_client']);
        }

        if (! empty($filters['selected_excecutive'])) {
            $reservations = $reservations->where('executive_id', '=', $filters['selected_excecutive']);
        }

        if (! empty($filters['create_date']['from_date'])) {
            $reservations = $reservations->where('created_at', '>=', $filters['create_date']['from_date']);
        }

        if (! empty($filters['create_date']['to_date'])) {
            $reservations = $reservations->where('created_at', '<=', $filters['create_date']['to_date']);
        }

        $reservations = $reservations->when(! empty($filters['reservation_hotel_id']), function ($query) use ($filters) {
            return $query->whereHas('reservationsHotel', function ($query) use ($filters) {
                if (! empty($filters['reservation_hotel_id'])) {
                    if (is_array($filters['reservation_hotel_id'])) {
                        $query->whereIn('id', $filters['reservation_hotel_id']);
                    } else {
                        $query->where('id', '=', $filters['reservation_hotel_id']);
                    }
                }
            });
        })->with([
            'reservationsHotel' => function ($resHotel) use ($filters) {
                if (! empty($filters['reservation_hotel_id'])) {
                    if (is_array($filters['reservation_hotel_id'])) {
                        $resHotel->whereIn('id', $filters['reservation_hotel_id']);
                    } else {
                        $resHotel->where('id', '=', $filters['reservation_hotel_id']);
                    }
                }

                if (! empty($filters['hotel_consecutive_from'])) {
                    $resHotel->where('consecutive', '>=', $filters['hotel_consecutive_from']);
                }

                if (! empty($filters['selected_excecutive'])) {
                    $resHotel->where('executive_id', '=', $filters['selected_excecutive']);
                }

                if (! empty($filters['hotel_id'])) {
                    $resHotel->where('hotel_id', '=', $filters['hotel_id']);
                }

                if (! empty($filters['check_in'])) {
                    $resHotel->where('check_in', '>=', $filters['check_in']);
                }

                if (! empty($filters['check_out'])) {
                    $resHotel->where('check_out', '<=', $filters['check_out']);
                }

                $resHotel->with([
                    'hotel' => function ($hotel) {
                        $hotel->select(['id', 'state_id']);
                        $hotel->with([
                            'state' => function ($query) {
                                $query->select(['id']);
                                $query->with([
                                    'translations' => function ($query) {
                                        $query->select(['id', 'object_id', 'value']);
                                        $query->where('language_id', 1);
                                    },
                                ]);
                            },
                        ]);
                    },
                ]);
                $resHotel->with([
                    'reservationsHotelRooms' => function ($hotelRoom) use ($filters) {

                        // $hotelRoom->where('onRequest', '=', 1);

                        if (! empty($filters['reservation_hotel_room_id'])) {
                            if (is_array($filters['reservation_hotel_room_id'])) {
                                $hotelRoom->whereIn('id', $filters['reservation_hotel_room_id']);
                            } else {
                                $hotelRoom->where('id', '=', $filters['reservation_hotel_room_id']);
                            }
                        }

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

                        if (! empty($filters['channel_cancel_by_rooms_hyperguest'])) {
                            $hotelRoom->where('channel_id', '=', 6);
                        }

                        $hotelRoom->with([
                            'reservationHotelCancelPolicies',
                            'room_type',
                            'reservationsHotelsCalendarys' => function ($hotelRoom) {
                                $hotelRoom->with([
                                    'reservationHotelRoomDateRate',
                                ]);
                            },
                            'supplements' => function ($hotelRoom) {
                                $hotelRoom->with([
                                    'calendaries',
                                ]);
                            },
                        ]);
                    },
                ]);

                if (isset($filters['status_email']) and ($filters['status_email'] == ReservationsHotel::STATUS_EMAIL_NOT_SENT or $filters['status_email'] == ReservationsHotel::STATUS_EMAIL_SENT)) {
                    $resHotel->where('status_email', $filters['status_email']);
                }
            },
            'reservationsService' => function ($resService) use ($filters) {
                if (! empty($filters['reservation_service_id'])) {
                    if (is_array($filters['reservation_service_id'])) {
                        $resService->whereIn('id', $filters['reservation_service_id']);
                    } else {
                        $resService->where('id', '=', $filters['reservation_service_id']);
                    }
                }

                if (! empty($filters['service_consecutive_from'])) {
                    $resService->where('consecutive', '>=', $filters['service_consecutive_from']);
                }

                if (! empty($filters['selected_excecutive'])) {
                    $resService->where('executive_id', '=', $filters['selected_excecutive']);
                }

                if (! empty($filters['service_id'])) {
                    $resService->where('service_id', '=', $filters['service_id']);
                }

                if (! empty($filters['date'])) {
                    $resService->where('date', $filters['date']);
                }

                if (isset($filters['status_email']) and ($filters['status_email'] == ReservationsService::STATUS_EMAIL_NOT_SENT or $filters['status_email'] == ReservationsService::STATUS_EMAIL_SENT)) {
                    $resService->where('status_email', $filters['status_email']);
                }

                $resService->with([
                    'reservationsServiceRatesPlans' => function ($serviceRatePlan) use ($filters) {
                        if (! empty($filters['selected_excecutive'])) {
                            $serviceRatePlan->where('executive_id', '=', $filters['selected_excecutive']);
                        }

                        if (! empty($filters['service_id'])) {
                            $serviceRatePlan->where('service_id', '=', $filters['service_id']);
                        }

                        if (! empty($filters['date'])) {
                            $serviceRatePlan->where('date', $filters['date']);
                        }

                        $serviceRatePlan->with('reservationServiceCancelPolicies');

                    },
                ]);
            },
            'reservationsPackage' => function ($resPackage) {
                $resPackage->with([
                    'package' => function ($query) {
                        $query->select(['id', 'nights', 'tag_id']);
                        $query->with([
                            'translations' => function ($query) {
                                $query->select(['id', 'package_id', 'tradename']);
                                $query->where('language_id', 1);
                            },
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
                                    },
                                ]);
                            },
                        ]);
                    },
                ]);
                $resPackage->with([
                    'serviceType' => function ($query) {
                        $query->select(['id']);
                        $query->with([
                            'translations' => function ($query) {
                                $query->select(['id', 'object_id', 'value']);
                                $query->where('language_id', 1);
                            },
                        ]);
                    },
                ]);
                $resPackage->with([
                    'typeClass' => function ($query) {
                        $query->select(['id']);
                        $query->with([
                            'translations' => function ($query) {
                                $query->select(['id', 'object_id', 'value']);
                                $query->where('language_id', 1);
                            },
                        ]);
                    },
                ]);
            },
            'client' => function ($resClient) {
                $resClient->select(['id', 'name']);
            },
            'reservationsFlight' => function ($resService) use ($filters) {
                if (isset($filters['status_email']) and ($filters['status_email'] == reservationsFlight::STATUS_EMAIL_NOT_SENT
                        or $filters['status_email'] == reservationsFlight::STATUS_EMAIL_SENT)
                ) {
                    $resService->where('status_email', $filters['status_email']);
                }
            },
        ])->with([
            'reservationsPassenger' => function ($resPassenger) {
                $resPassenger->with('document_type');
            },
        ])->with([
            'billing' => function ($resPassenger) {
                $resPassenger->with('document_type');
                $resPassenger->with('country');
                $resPassenger->with('state');
            },
        ])->get();

        return $first ? $reservations->first() : $reservations;
    }

    public static function getAllReservationPaginate(array $filters): array
    {
        if (! empty($filters['reservation_id'])) {
            $reservations = self::where('id', '=', $filters['reservation_id']);
        } elseif (! empty($filters['file_code'])) {
            $reservations = self::where('file_code', '=', $filters['file_code'])->orWhere(
                'booking_code',
                '=',
                $filters['file_code']
            );
        } else {
            $reservations = (new self());
        }

        if (! empty($filters['selected_client'])) {
            $reservations = $reservations->where('client_id', '=', $filters['selected_client']);
        }

        if (! empty($filters['selected_excecutive'])) {
            $reservations = $reservations->where('executive_id', '=', $filters['selected_excecutive']);
        }

        if (! empty($filters['create_date'])) {
            $reservations = $reservations->whereDate('created_at', '=', $filters['create_date']);
        }

        if (! empty($filters['status_reserve'])) {
            $reservations = $reservations->where('status_cron_job_reservation_stella', $filters['status_reserve']);
        }

        if (! empty($filters['status_error'])) {
            $reservations = $reservations->where('status_cron_job_error', $filters['status_error']);
        }

        $reservations = $reservations->when(! empty($filters['reservation_hotel_id']), function ($query) use ($filters) {
            return $query->whereHas('reservationsHotel', function ($query) use ($filters) {
                if (! empty($filters['reservation_hotel_id'])) {
                    if (is_array($filters['reservation_hotel_id'])) {
                        $query->whereIn('id', $filters['reservation_hotel_id']);
                    } else {
                        $query->where('id', '=', $filters['reservation_hotel_id']);
                    }
                }
            });
        })->with([
            'reservationsHotel' => function ($resHotel) use ($filters) {
                if (! empty($filters['reservation_hotel_id'])) {
                    if (is_array($filters['reservation_hotel_id'])) {
                        $resHotel->whereIn('id', $filters['reservation_hotel_id']);
                    } else {
                        $resHotel->where('id', '=', $filters['reservation_hotel_id']);
                    }
                }

                if (! empty($filters['hotel_consecutive_from'])) {
                    $resHotel->where('consecutive', '>=', $filters['hotel_consecutive_from']);
                }

                if (! empty($filters['selected_excecutive'])) {
                    $resHotel->where('executive_id', '=', $filters['selected_excecutive']);
                }

                if (! empty($filters['hotel_id'])) {
                    $resHotel->where('hotel_id', '=', $filters['hotel_id']);
                }

                if (! empty($filters['check_in'])) {
                    $resHotel->where('check_in', '>=', $filters['check_in']);
                }

                if (! empty($filters['check_out'])) {
                    $resHotel->where('check_out', '<=', $filters['check_out']);
                }

                $resHotel->with([
                    'reservationsHotelRooms' => function ($hotelRoom) use ($filters) {

                        // $hotelRoom->where('onRequest', '=', 1);

                        if (! empty($filters['reservation_hotel_room_id'])) {
                            if (is_array($filters['reservation_hotel_room_id'])) {
                                $hotelRoom->whereIn('id', $filters['reservation_hotel_room_id']);
                            } else {
                                $hotelRoom->where('id', '=', $filters['reservation_hotel_room_id']);
                            }
                        }

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
                            'user_cancel',
                            'reservationHotelCancelPolicies',
                            'reservationsHotelsCalendarys' => function ($hotelRoom) {
                                $hotelRoom->with([
                                    'reservationHotelRoomDateRate',
                                ]);
                            },
                            'supplements' => function ($hotelRoom) {
                                $hotelRoom->with([
                                    'calendaries',
                                ]);
                            },
                        ]);
                    },
                ]);
            },
            'reservationsService' => function ($resService) use ($filters) {
                if (! empty($filters['reservation_service_id'])) {
                    if (is_array($filters['reservation_service_id'])) {
                        $resService->whereIn('id', $filters['reservation_service_id']);
                    } else {
                        $resService->where('id', '=', $filters['reservation_service_id']);
                    }
                }

                if (! empty($filters['service_consecutive_from'])) {
                    $resService->where('consecutive', '>=', $filters['service_consecutive_from']);
                }

                if (! empty($filters['selected_excecutive'])) {
                    $resService->where('executive_id', '=', $filters['selected_excecutive']);
                }

                if (! empty($filters['service_id'])) {
                    $resService->where('service_id', '=', $filters['service_id']);
                }

                if (! empty($filters['date'])) {
                    $resService->where('date', $filters['date']);
                }

                $resService->with([
                    'reservationsServiceRatesPlans' => function ($serviceRatePlan) use ($filters) {
                        if (! empty($filters['selected_excecutive'])) {
                            $serviceRatePlan->where('executive_id', '=', $filters['selected_excecutive']);
                        }

                        if (! empty($filters['service_id'])) {
                            $serviceRatePlan->where('service_id', '=', $filters['service_id']);
                        }

                        if (! empty($filters['date'])) {
                            $serviceRatePlan->where('date', $filters['date']);
                        }

                        $serviceRatePlan->with('reservationServiceCancelPolicies');

                    },
                ]);
            },
            'reservationsPackage' => function ($resPackage) {
                $resPackage->with([
                    'package' => function ($query) {
                        $query->select(['id', 'nights', 'tag_id']);
                        $query->with([
                            'translations' => function ($query) {
                                $query->select(['id', 'package_id', 'tradename']);
                                $query->where('language_id', 1);
                            },
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
                                    },
                                ]);
                            },
                        ]);
                    },
                ]);
                $resPackage->with([
                    'serviceType' => function ($query) {
                        $query->select(['id']);
                        $query->with([
                            'translations' => function ($query) {
                                $query->select(['id', 'object_id', 'value']);
                                $query->where('language_id', 1);
                            },
                        ]);
                    },
                ]);
                $resPackage->with([
                    'typeClass' => function ($query) {
                        $query->select(['id']);
                        $query->with([
                            'translations' => function ($query) {
                                $query->select(['id', 'object_id', 'value']);
                                $query->where('language_id', 1);
                            },
                        ]);
                    },
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
                },
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
                                },
                            ]);
                        },
                    ]);
                    $resBilling->with([
                        'country' => function ($query) {
                            $query->select(['id', 'iso_ifx']);
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select(['object_id', 'value']);
                                    $query->where('type', 'country');
                                    $query->where('language_id', 1);
                                },
                            ]);
                        },
                    ]);
                    $resBilling->with([
                        'state' => function ($query) {
                            $query->select(['id', 'iso']);
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select(['object_id', 'value']);
                                    $query->where('type', 'state');
                                    $query->where('language_id', 1);
                                },
                            ]);
                        },
                    ]);
                },
            ])->with([
                'client' => function ($query) {
                    $query->select(['id', 'code', 'name', 'market_id']);
                },
            ])->with([
                'executive' => function ($query) {
                    $query->select(['id', 'code', 'name', 'email']);
                    $query->with('markets');
                },
            ])->with([
                'create_user' => function ($query) {
                    $query->select(['id', 'code', 'name', 'email']);
                },
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
            'data'  => $reservations,
            'count' => $count,
        ];
    }

    public function getConsecutiveHotelPrev()
    {
        return $this->consecutive_hotel_prev;
    }

    public function setConsecutiveHotelPrev(): void
    {
        $this->consecutive_hotel_prev = $this->reservationsHotel->count();
    }

    public function getConsecutiveServicePrev()
    {
        return $this->consecutive_service_prev;
    }

    public function setConsecutiveservicePrev(): void
    {
        $this->consecutive_service_prev = $this->reservationsService()->count();
    }

    public function reservationsService(): HasMany
    {
        return $this->hasMany(ReservationsService::class);
    }

    public function reservationsHotel(): HasMany
    {
        return $this->hasMany(ReservationsHotel::class);
    }

    public function reservationsFlight(): HasMany
    {
        return $this->hasMany(ReservationsFlight::class);
    }

    public function reservationsDiscount(): HasMany
    {
        return $this->hasMany(ReservationsDiscounts::class);
    }

    public function reservations_active_count(): void
    {
        $reservation_hotel_rate_plan_room_ids = ReservationsHotelsRatesPlansRooms::where(
            'check_in',
            '>=',
            date('Y-m-d')
        )
            ->where('channel_id', 1)
            ->Orwhere('status_in_channel', 1)
            ->where('channel_code', 'AURORA')
            ->where('onRequest', 1)
            ->pluck('id');

        $reservation_hotel_rate_plan_calendars = ReservationsHotelsRatesPlansRoomsCalendarys::where(
            'date',
            '>=',
            date('Y-m-d')
        )
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
                'update_inventory_cancelled',
            ]);
        //->groupBy(['reservations_hotels_rates_plans_rooms.rates_plans_room_id', 'date']);
        /*$reservations_active = [];
        foreach ($reservations as $rates_plans_room_id => $reservation_dates) {
            if (!$rates_plans_room_id) {
                continue;
            }
            foreach ($reservation_dates as $date => $item) {
                if ((
                        $item[0]['reservations_hotels_rates_plans_rooms']['channel_code'] == 'AURORA' and
                        $item[0]['reservations_hotels_rates_plans_rooms']['status'] == 1
                    ) or (
                        $item[0]['reservations_hotels_rates_plans_rooms']['channel_code'] != 'AURORA' and
                        $item[0]['reservations_hotels_rates_plans_rooms']['status_in_channel'] == 1
                    )) {

                    if (!isset($reservations_active[$rates_plans_room_id.'|'.$date])) {
                        $reservations_active[$rates_plans_room_id.'|'.$date] = [
                            'rate_plan_rooms_id' => $rates_plans_room_id,
                            'reservations_hotels_rates_plans_rooms_calendar_id' => $item[0]['id'],
                            'date' => $date,
                            'create_user_id' => $item[0]['create_user_id'],
                            'total_booking' => 0
                        ];
                    }


                    $reservations_active[$rates_plans_room_id.'|'.$date]['total_booking'] += $item->count();
                }
            }
        }*/
        /* DB::transaction(function () use ($reservations_active) {
          foreach ($reservations_active as $reservations_active_data) {
              try {
                  $rates_plans_rooms = RatesPlansRooms::find($reservations_active_data['rate_plan_rooms_id']);

                  if ($rates_plans_rooms !== null) {
                      $user = User::find($reservations_active_data['create_user_id']);

                      if ($rates_plans_rooms->bag == 1) {
                          $bag_rates = BagRate::where('rate_plan_rooms_id',
                              $reservations_active_data['rate_plan_rooms_id'])->first();
                          $bag_room_id = $bag_rates->bag_room_id;
                          $affectedRows = \App\Models\InventoryBag::where('bag_room_id', '=', $bag_room_id)
                              ->where('date', '=', $reservations_active_data['date'])
                              ->update(array('total_booking' => $reservations_active_data['total_booking']));

                          if ($affectedRows) {
                              $inventory_bag_id = \App\Models\InventoryBag::where('bag_room_id', '=', $bag_room_id)
                                  ->where('date', '=', $reservations_active_data['date'])->first();

                              $inventory = ReservationsHotelsRatesPlansRoomsCalendarys::find($reservations_active_data['reservations_hotels_rates_plans_rooms_calendar_id']);
                              $inventory->update_inventory_reserve = 1;
                              $inventory->save();

                              activity()
                                  ->performedOn(\App\Models\InventoryBag::find($inventory_bag_id->id))
                                  ->causedBy($user)
                                  ->withProperties(['date_inventory' => $reservations_active_data['date']])
                                  ->log('El usuario '.$user->name.' ha realizado una reserva de '.$reservations_active_data['total_booking'].' para el dia '.$reservations_active_data['date']);
                          }
                      } else {
                          $affectedRows = \App\Models\Inventory::where('rate_plan_rooms_id', '=',
                              $reservations_active_data['rate_plan_rooms_id'])
                              ->where('date', '=', $reservations_active_data['date'])
                              ->update(array('total_booking' => $reservations_active_data['total_booking']));

                          if ($affectedRows) {
                              $inventory_id = \App\Models\Inventory::where('rate_plan_rooms_id', '=',
                                  $reservations_active_data['rate_plan_rooms_id'])
                                  ->where('date', '=', $reservations_active_data['date'])->first();
                              $inventory = ReservationsHotelsRatesPlansRoomsCalendarys::find($reservations_active_data['reservations_hotels_rates_plans_rooms_calendar_id']);
                              $inventory->update_inventory_reserve = 1;
                              $inventory->save();

                              activity()
                                  ->performedOn(\App\Models\Inventory::find($inventory_id->id))
                                  ->causedBy($user)
                                  ->withProperties(['date_inventory' => $reservations_active_data['date']])
                                  ->log('El usuario '.$user->name.' ha realizado una reserva de '.$reservations_active_data['total_booking'].' para el dia '.$reservations_active_data['date']);
                          }
                      }
                  }
              } catch (\Exception $exception) {
                  return print_r($exception->getMessage().' => '.' data => '.json_encode($reservations_active_data).' line -> '.$exception->getLine());
              }
          }
      });*/
        //TODO: agrupar las reservas en la misma fecha y sumar las habitaciones para hacer una sola actualizacion de inventario
        DB::transaction(function () use ($reservation_hotel_rate_plan_calendars) {
            foreach ($reservation_hotel_rate_plan_calendars as $reservation_hotel_rate_plan_calendar) {
                try {
                    $rate_plan_room = RatesPlansRooms::find($reservation_hotel_rate_plan_calendar['rates_plans_room_id']);
                    if ($rate_plan_room !== null) {
                        $user = User::find($reservation_hotel_rate_plan_calendar['create_user_id']);

                        //si esta adentro de una bolsa
                        if ($rate_plan_room->bag == 1) {
                            $bag_rates = BagRate::where(
                                'rate_plan_rooms_id',
                                $reservation_hotel_rate_plan_calendar['rates_plans_room_id']
                            )->first();
                            if ($bag_rates) {
                                $bag_room_id = $bag_rates->bag_room_id;
                                $inventory_bag = InventoryBag::where('bag_room_id', '=', $bag_room_id)
                                    ->where('date', '=', $reservation_hotel_rate_plan_calendar->date)
                                    ->first();
                                if ($inventory_bag) {
                                    $inventory_bag->inventory_num = $inventory_bag->inventory_num - 1;
                                    $inventory_bag->total_booking = $inventory_bag->total_booking + 1;
                                    $inventory_bag->save();

                                    $update_inventory_reservation = ReservationsHotelsRatesPlansRoomsCalendarys::find($reservation_hotel_rate_plan_calendar['id']);
                                    $update_inventory_reservation->update_inventory_reserve = ReservationsHotelsRatesPlansRoomsCalendarys::UPDATE_INVENTORY_SUCCESS;
                                    $update_inventory_reservation->save();

                                    activity()
                                        ->performedOn(InventoryBag::find($inventory_bag->id))
                                        ->causedBy($user)
                                        ->withProperties(['date_inventory' => $reservation_hotel_rate_plan_calendar['date']])
                                        ->log('El usuario '.$user->name.' ha realizado una reserva de 1 para el dia '.$reservation_hotel_rate_plan_calendar['date']);
                                } else {
                                    $update_inventory_reservation = ReservationsHotelsRatesPlansRoomsCalendarys::find($reservation_hotel_rate_plan_calendar['id']);
                                    $update_inventory_reservation->update_inventory_reserve = ReservationsHotelsRatesPlansRoomsCalendarys::UPDATE_INVENTORY_NOT_FOUND;
                                    $update_inventory_reservation->save();
                                }
                            } else {
                                $update_inventory_reservation = ReservationsHotelsRatesPlansRoomsCalendarys::find($reservation_hotel_rate_plan_calendar['id']);
                                $update_inventory_reservation->update_inventory_reserve = ReservationsHotelsRatesPlansRoomsCalendarys::UPDATE_INVENTORY_NOT_FOUND;
                                $update_inventory_reservation->save();
                            }
                        }
                        //Si no esta adentro de una bolsa
                        if ($rate_plan_room->bag == 0) {
                            //Buscar inventario y actualizarlo
                            $inventory = Inventory::where('date', $reservation_hotel_rate_plan_calendar->date)
                                ->where(
                                    'rate_plan_rooms_id',
                                    $reservation_hotel_rate_plan_calendar['rates_plans_room_id']
                                )
                                ->first();
                            if ($inventory != null) {
                                $inventory->inventory_num = $inventory->inventory_num - 1;
                                $inventory->total_booking = $inventory->total_booking + 1;
                                $inventory->save();

                                $update_inventory_reservation = ReservationsHotelsRatesPlansRoomsCalendarys::find($reservation_hotel_rate_plan_calendar['id']);
                                $update_inventory_reservation->update_inventory_reserve = ReservationsHotelsRatesPlansRoomsCalendarys::UPDATE_INVENTORY_SUCCESS;
                                $update_inventory_reservation->save();

                                activity()
                                    ->performedOn(Inventory::find($inventory->id))
                                    ->causedBy($user)
                                    ->withProperties(['date_inventory' => $reservation_hotel_rate_plan_calendar['date']])
                                    ->log('El usuario '.$user->name.' ha realizado una reserva de 1 para el dia '.$reservation_hotel_rate_plan_calendar['date']);
                            } else {
                                $update_inventory_reservation = ReservationsHotelsRatesPlansRoomsCalendarys::find($reservation_hotel_rate_plan_calendar['id']);
                                $update_inventory_reservation->update_inventory_reserve = ReservationsHotelsRatesPlansRoomsCalendarys::UPDATE_INVENTORY_NOT_FOUND;
                                $update_inventory_reservation->save();
                            }
                        }
                    }
                } catch (Exception $e) {
                    return print_r($e->getMessage().' => '.' data => '.json_encode($reservation_hotel_rate_plan_calendar).' line -> '.$e->getLine(), true);
                }
            }
        });

    }

    public function reservation_canceled_count(): void
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
                'update_inventory_cancelled',
            ])
            ->groupBy(['reservations_hotels_rates_plans_rooms.rates_plans_room_id', 'date']);

        $reservations_cancel = [];
        foreach ($reservations as $rates_plans_room_id => $reservation_dates) {
            if (! $rates_plans_room_id) {
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

                    if (! isset($reservations_cancel[$rates_plans_room_id.'|'.$date])) {
                        $reservations_cancel[$rates_plans_room_id.'|'.$date] = [
                            'rate_plan_rooms_id'                                => $rates_plans_room_id,
                            'reservations_hotels_rates_plans_rooms_calendar_id' => $item[0]['id'],
                            'date'                                              => $date,
                            'create_user_id'                                    => $item[0]['create_user_id'],
                            'total_canceled'                                    => 0,
                        ];
                    }

                    $reservations_cancel[$rates_plans_room_id.'|'.$date]['total_canceled'] += $item->count();
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

                            $bag_rates = BagRate::where(
                                'rate_plan_rooms_id',
                                $reservations_cancel_data['rate_plan_rooms_id']
                            )->first();
                            $bag_room_id = $bag_rates->bag_room_id;

                            $affectedRows = InventoryBag::where('bag_room_id', '=', $bag_room_id)
                                ->where('date', '=', $reservations_cancel_data['date'])
                                ->update(['total_canceled' => $reservations_cancel_data['total_canceled']]);
                            if ($affectedRows) {
                                $inventory_bag_id = InventoryBag::where('bag_room_id', '=', $bag_room_id)
                                    ->where('date', '=', $reservations_cancel_data['date'])->first();

                                $inventory = ReservationsHotelsRatesPlansRoomsCalendarys::find($reservations_cancel_data['reservations_hotels_rates_plans_rooms_calendar_id']);
                                $inventory->update_inventory_cancelled = 1;
                                $inventory->save();

                                activity()
                                    ->performedOn(InventoryBag::find($inventory_bag_id->id))
                                    ->causedBy($user)
                                    ->withProperties(['date_inventory' => $reservations_cancel_data['date']])
                                    ->log('El usuario '.$user->name.' ha cancelado una reserva de '.$reservations_cancel_data['total_canceled'].' para el dia '.$reservations_cancel_data['date']);
                            }
                        } else {

                            $affectedRows = Inventory::where(
                                'rate_plan_rooms_id',
                                '=',
                                $reservations_cancel_data['rate_plan_rooms_id']
                            )
                                ->where('date', '=', $reservations_cancel_data['date'])
                                ->update(['total_canceled' => $reservations_cancel_data['total_canceled']]);
                            if ($affectedRows) {
                                $inventory_id = Inventory::where(
                                    'rate_plan_rooms_id',
                                    '=',
                                    $reservations_cancel_data['rate_plan_rooms_id']
                                )
                                    ->where('date', '=', $reservations_cancel_data['date'])->first();

                                $inventory = ReservationsHotelsRatesPlansRoomsCalendarys::find($reservations_cancel_data['reservations_hotels_rates_plans_rooms_calendar_id']);
                                $inventory->update_inventory_cancelled = 1;
                                $inventory->save();

                                activity()
                                    ->performedOn(Inventory::find($inventory_id->id))
                                    ->causedBy($user)
                                    ->withProperties(['date_inventory' => $reservations_cancel_data['date']])
                                    ->log('El usuario '.$user->name.' ha cancelado una reserva de '.$reservations_cancel_data['total_canceled'].' para el dia '.$reservations_cancel_data['date']);
                            }

                        }
                    }
                } catch (Exception $exception) {
                    return print_r($exception->getMessage().' => '.' data => '.json_encode($reservations_cancel_data).' line -> '.$exception->getLine(), true);
                }
            }
        });
    }

    public function reservationsPassenger(): HasMany
    {
        return $this->hasMany(ReservationPassenger::class);
    }

    public function billing(): BelongsTo
    {
        return $this->belongsTo(ReservationBilling::class, 'reservation_billing_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function reservationsPackage(): HasMany
    {
        return $this->hasMany(ReservationsPackage::class);
    }

    public function executive(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executive_id', 'id');
    }

    public function create_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'create_user_id', 'id');
    }

    public function reservationsEmailLogs(): HasMany
    {
        return $this->hasMany(ReservationsEmailsLog::class);
    }
}

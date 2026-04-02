<?php

namespace App\Http\Services\Traits;

use App\Hotel;
use App\Markup;
use App\PoliciesCancelations;
use App\RatePlanRoomDateRange;
use App\RatesPlansCalendarys;
use App\RatesPlansRooms;
use App\Room;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait ClientHotelAvailableTrait
{
    public function getClientHotelsAvail(
        $client_id,
        $period,
        array $hotels_client_hotel_id_list,
        $date_from,
        $date_to, // Fecha de salida para HYPERGUEST
        $from, // Fecha de entrada
        $to, // Fecha de salida
        $reservation_days,
        $rate_plan_room_ids_include,
        $country_iso,
        $state_iso,
        $city_id,
        $district_id,
        $typeclass_id,
        $hotels_id,
        $language_id
    ) {
        $hotels_client = $this->executeClientHotelAvailable(
            $client_id,
            $period,
            $hotels_client_hotel_id_list,
            $date_to, // Fecha de salida para HYPERGUEST
            $from, // Fecha de entrada
            $to, // Fecha de salida
            $reservation_days,
            $rate_plan_room_ids_include,
            $country_iso,
            $state_iso,
            $city_id,
            $district_id,
            $typeclass_id,
            $hotels_id,
            $language_id
        );

        $client_markup = $this->loadClientMarkup($client_id, $period, $country_iso);

        $global_policies = $this->loadGlobalCancellationPolicies();

        $hotels_client = $this->transformHotelRooms($hotels_client, $client_id, $date_from, $date_to, $client_markup, $global_policies);

        $hotels_client = $this->transformRatesChannelsInRatesAurora($hotels_client->toArray());
 
        return $this->deepToArray($hotels_client);
    }

    public function executeClientHotelAvailable(
        $client_id,
        $period,
        array $hotels_client_hotel_id_list,
        $date_to, // Fecha de salida para HYPERGUEST
        $from, // Fecha de entrada
        $to, // Fecha de salida
        $reservation_days,
        $rate_plan_room_ids_include,
        $country_iso,
        $state_iso,
        $city_id,
        $district_id,
        $typeclass_id,
        $hotels_id,
        $language_id
    ) {
        $hotels_query = $this->buildHotelBaseQuery($client_id, $period);

        $hotels_query = $this->applyHotelFilters(
            $hotels_query,
            $hotels_client_hotel_id_list,
            $country_iso,
            $state_iso,
            $city_id,
            $district_id,
            $typeclass_id,
            $from,
            $rate_plan_room_ids_include
        );

        $hotels_query = $this->loadHotelRelations($hotels_query, $from, $typeclass_id, $language_id, $period, $client_id);

        $hotels_query = $this->filterByAvailability($hotels_query, $from, $date_to, $reservation_days, $rate_plan_room_ids_include);

        $hotels_query = $this->loadRoomRelations($hotels_query, $from, $to, $date_to, $reservation_days, $rate_plan_room_ids_include, $period, $client_id, $language_id);

        $hotels_client = (count($hotels_id) > 0)
            ? $hotels_query->whereIn('id', $hotels_id)->get()
            : $hotels_query->get();

        return $hotels_client;
    }

    private function buildHotelBaseQuery($client_id, $period)
    {
        return Hotel::select(
            'id',
            'name',
            'country_id',
            'state_id',
            'city_id',
            'district_id',
            'zone_id',
            'hotel_type_id',
            'typeclass_id',
            'chain_id',
            'latitude',
            'longitude',
            'stars',
            'check_in_time',
            'check_out_time',
            'preferential',
            'min_age_child',
            'max_age_child',
            'allows_child',
            'allows_teenagers',
            'min_age_teenagers',
            'max_age_teenagers',
            'notes',
            'flag_new',
            'date_end_flag_new'
        )
            ->whereDoesntHave('hotelClients', function ($query) use ($client_id, $period) {
                $query->where('client_id', $client_id)
                    ->where('period', $period);
            })
            ->where('status', 1);
    }

    private function applyHotelFilters(
        $query,
        $hotel_ids,
        $country_iso,
        $state_iso,
        $city_id,
        $district_id,
        $typeclass_id,
        $from,
        $rate_plan_room_ids_include
    ) {
        if (!empty($hotel_ids)) {
            $query->whereIn('id', $hotel_ids);
        }

        if (!empty($country_iso)) {
            $query->whereHas('country', function ($q) use ($country_iso) {
                $q->where('iso', $country_iso);
            });
        }

        if (!empty($state_iso)) {
            $query->whereHas('state', function ($q) use ($state_iso) {
                $q->where('iso', $state_iso);
            });
        }

        if (!empty($city_id)) {
            $query->where('city_id', $city_id);
        }

        if (!empty($district_id)) {
            $query->where('district_id', $district_id);
        }

        if (!empty($typeclass_id)) {
            $query->whereHas('hoteltypeclass', function ($q) use ($typeclass_id, $from) {
                $q->where('typeclass_id', $typeclass_id)
                    ->where('year', Carbon::parse($from)->year);
            });
        }

        $query->whereHas('rooms', function ($q) use ($rate_plan_room_ids_include) {
            $q->where('state', 1)
                ->whereHas('rates_plan_room', function ($q) use ($rate_plan_room_ids_include) {
                    $q->whereIn('id', $rate_plan_room_ids_include)
                        ->where('status', 1);
                });
        });

        return $query;
    }

    private function loadHotelRelations($query, $from, $typeclass_id, $language_id, $period, $client_id)
    {
        $year = Carbon::parse($from)->year;

        return $query
            ->with(['country.translations' => function ($q) use ($language_id) {
                $q->where('type', 'country')->where('language_id', $language_id);
            }])
            ->with(['state.translations' => function ($q) use ($language_id) {
                $q->where('type', 'state')->where('language_id', $language_id);
            }])
            ->with(['city.translations' => function ($q) use ($language_id) {
                $q->where('type', 'city')->where('language_id', $language_id);
            }])
            ->with(['district.translations' => function ($q) use ($language_id) {
                $q->where('type', 'district')->where('language_id', $language_id);
            }])
            ->with(['zone.translations' => function ($q) use ($language_id) {
                $q->where('type', 'zone')->where('language_id', $language_id);
            }])
            ->with(['translations' => function ($q) use ($language_id) {
                $q->where('type', 'hotel')->where('language_id', $language_id);
            }])
            ->with(['galeries' => function ($q) {
                $q->where('type', 'hotel')->where('state', 1);
            }])
            ->with(['channels' => function ($q) {
                $q->wherePivot('state', 1)->wherePivot('code', '!=', '')->wherePivot('code', '!=', 'null');
            }])
            ->with([
                'amenity.translations' => function ($q) use ($language_id) {
                    $q->where('type', 'amenity')->where('language_id', $language_id);
                },
                'amenity.galeries' => function ($q) use ($language_id) {
                    $q->select('object_id', 'url')->where('type', 'amenity'); //->where('state', 1);
                }
            ])
            ->with(['hoteltype.translations' => function ($q) use ($language_id) {
                $q->where('type', 'hoteltype')->where('language_id', $language_id);
            }])
            ->with(['hoteltypeclass' => function ($q) use ($year, $typeclass_id, $language_id) {
                $q->where('year', $year);
                if (!empty($typeclass_id)) {
                    $q->where('typeclass_id', $typeclass_id);
                }
                $q->with(['typeclass.translations' => function ($q) use ($language_id) {
                    $q->where('type', 'typeclass')->where('language_id', $language_id);
                }]);
            }])
            ->with(['hotelpreferentials' => function ($q) use ($year) {
                $q->where('year', $year);
            }])
            ->with(['alerts' => function ($q) use ($period, $language_id) {
                //$q->where('year', $period)->where('language_id', $language_id);
               $q->select('id','hotel_id','language_id','remarks','notes','year')
                ->where(function ($w) use ($period, $language_id) {
                    // incluir SIEMPRE idioma 1 del año
                    $w->where(function ($w1) use ($period) {
                        $w1->where('language_id', 1)
                            ->where('year', $period);
                    })
                    // y además el idioma solicitado del mismo año
                    ->orWhere(function ($w2) use ($period, $language_id) {
                        $w2->where('language_id', $language_id)
                            ->where('year', $period);
                    });
                });
            }])
            ->with(['markup' => function ($q) use ($period, $client_id) {
                $q->where('client_id', $client_id)->where('period', '>=', $period);
            }])
            ->with(['taxes' => function ($q) {
                $q->where('status', 1);
            }])
            ->with('chain');
    }

    private function loadRoomRelations($query, $from, $to, $date_to, $reservation_days, $rate_plan_room_ids_include, $period, $client_id, $language_id)
    {
        return $query->with(['rooms' => function ($query) use (
            $from,
            $to,
            $date_to,
            $reservation_days,
            $rate_plan_room_ids_include,
            $period,
            $client_id,
            $language_id
        ) {
            $query->select('id', 'hotel_id', 'room_type_id', 'max_capacity', 'min_adults', 'inventory', 'max_adults', 'max_child', 'max_infants', 'bed_additional');
            $query->where('state', 1);

            $query->with(['galeries' => function ($q) {
                $q->select('object_id', 'slug', 'url')->where('type', 'hotel')->where('state', 1);
            }]);

            $query->with(['channels' => function ($q) {
                $q->wherePivot('state', 1)->wherePivot('code', '!=', '')->wherePivot('code', '!=', 'null');
            }]);

            $query->with(['room_type.translations' => function ($q) use ($language_id) {
                $q->select('object_id', 'value')->where('type', 'roomtype')->where('language_id', $language_id);
            }]);

            $query->with(['translations' => function ($q) use ($language_id) {
                $q->select('object_id', 'value', 'slug')->where('type', 'room')->where('language_id', $language_id);
            }]);

            $query->whereHas('rates_plan_room', function ($q) use ($rate_plan_room_ids_include) {
                $q->where('status', 1)->whereIn('id', $rate_plan_room_ids_include);
            });

            $query->with(['rates_plan_room' => function ($q) use (
                $from,
                $to,
                $date_to,
                $reservation_days,
                $rate_plan_room_ids_include,
                $period,
                $client_id,
                $language_id
            ) {
                $q->select('id', 'rates_plans_id', 'room_id', 'status', 'bag', 'channel_id', 'channel_child_price', 'channel_infant_price');
                $q->whereIn('id', $rate_plan_room_ids_include);
                $q->where('status', 1);

                $q->whereDoesntHave('rate_plan.client', function ($query) use ($period, $client_id) {
                    $query->where('client_id', $this->client_id());
                    $query->where('period', $period);
                    $query->whereNull('client_rate_plans.deleted_at');
                });

                $q->with([
                    'channel',
                    'policies_cancelation.policy_cancellation_parameter.penalty',
                    'descriptions',
                    'calendarys' => function ($query) use ($from, $to, $date_to, $language_id, $reservation_days) {
                        $query->whereBetween('date', [$from, $date_to]);
                        $query->with([
                            'policies_rates.policies_cancelation.policy_cancellation_parameter.penalty',
                            'policies_rates.translations' => function ($q) use ($language_id) {
                                $q->where('type', 'rate_policies')->where('language_id', $language_id);
                            },
                            'policies_cancelation.policy_cancellation_parameter.penalty',
                            'rate',
                        ]);
                    },
                    'rate_plan' => function ($query) use ($period, $language_id, $client_id) {
                        $query->with([
                            'translations' => function ($q) use ($language_id) {
                                $q->where('language_id', $language_id);
                            },
                            'translations_no_show' => function ($q) use ($language_id) {
                                $q->where('language_id', $language_id);
                            },
                            'translations_day_use' => function ($q) use ($language_id) {
                                $q->where('language_id', $language_id);
                            },
                            'translations_notes' => function ($q) use ($language_id) {
                                $q->where('language_id', $language_id);
                            },
                            'meal.translations',
                            'markup' => function ($q) use ($period, $client_id) {
                                $q->where('client_id', $client_id)->where('period', '>=', $period);
                            },
                            'promotionsData',
                        ]);
                        $query->where('status', 1);
                    },
                    'inventories' => function ($q) use ($from, $to) {
                        $q->whereBetween('date', [$from, $to]);
                    },
                    'bag_rate.bag_room.inventory_bags' => function ($q) use ($from, $to) {
                        $q->whereBetween('date', [$from, $to]);
                    },
                    'markup' => function ($q) use ($from, $client_id) {
                        $q->where('client_id', $client_id)
                            ->where('period', Carbon::parse($from)->year);
                    },
                ]);
            }]);
        }]);
    }

    private function filterByAvailability($query, $from, $date_to, $reservation_days, $rate_plan_room_ids_include)
    {
        $dates = $this->generateDateRange($from, $date_to);
        $arrivalDates = array_slice($dates, 0, -1); // Fechas sin la última
        $departureDate = end($dates); // Obtener la última fecha del rango

        $query->whereHas('rooms', function ($q) use ($arrivalDates, $departureDate, $rate_plan_room_ids_include, $from, $reservation_days) {
            $q->where('state', 1)
                ->whereHas('rates_plan_room', function ($query) use ($arrivalDates, $departureDate, $rate_plan_room_ids_include, $reservation_days) {
                    $query->whereHas('calendarys', function ($q) use ($arrivalDates, $departureDate, $rate_plan_room_ids_include, $reservation_days) {

                        // Validar fechas iniciales (excepto la última)
                        if (!empty($arrivalDates)) {
                            $q->where(function ($query) use ($arrivalDates, $departureDate) {
                                $query->whereIn('date', $arrivalDates) // Fechas iniciales
                                    ->where('restriction_status', 1) // Debe estar libre de restricción status
                                    ->where('restriction_arrival', 1); // Debe estar libre de restricción arrival
                            });
                        }

                        // Restricción solo para fecha de salida
                        if ($departureDate) {
                            // OJO: Por alguna razón con OR me devuelve las habitaciones que tienen la fecha de salida abierta
                            $q->orWhere(function ($query) use ($departureDate) {
                                $query->where('date', $departureDate)
                                    ->where('restriction_departure', 1);
                            });
                        }

                        // Verificar políticas de tarifas (para todas las fechas)
                        $q->whereHas('policies_rates', function ($qq) use ($reservation_days) {
                            $qq->where('min_length_stay', '<=', $reservation_days)
                                ->where('max_length_stay', '>=', $reservation_days);
                        });

                        // Filtrar por todos los rate_plan_room_ids
                        $q->whereIn('rates_plans_room_id', $rate_plan_room_ids_include); // Asegurarse de que las fechas estén dentro del rango
                    });
                });
        });

        return $query;
    }

    private function loadClientMarkup($client_id, $period, $country_iso)
    {
        return Markup::whereHas('businessRegion.countries', function($query) use ($country_iso) {
                    $query->where('iso', $country_iso);
                })->where(['client_id' => $client_id, 'period' => $period])->first();

        // return Markup::select('hotel')->where('client_id', $client_id)->where('period', $period)->first();
    }

    private function loadGlobalCancellationPolicies()
    {
        return PoliciesCancelations::where('code', 'CANCELLATION_POLICY_CHANNELS')
            ->with(['policy_cancellation_parameter.penalty'])
            ->first();
    }

    private function transformHotelRooms($hotels_client, $client_id, $date_from, $date_to, $client_markup, $global_policies)
    {
        $data = $hotels_client->transform(function (Hotel $hotel) use (
            $client_id,
            $date_from,
            $date_to,
            $client_markup,
            $global_policies
        ) {
            $hotel->rooms->transform(function (Room $room) use ($global_policies, $date_from, $date_to) {
                $room->rates_plan_room->transform(function (RatesPlansRooms $rate_plan_room) use (
                    $global_policies,
                    $date_from,
                    $date_to
                ) {
                    // Eliminar inventario si el plan es "bag"
                    if ($rate_plan_room->bag == '1') {
                        unset($rate_plan_room->inventories);
                        $rate_plan_room->inventories = [];
                    } else {
                        unset($rate_plan_room->bag_rate);
                        $rate_plan_room->bag_rate = [];
                    }

                    // Removemos la última fecha de calendarys (el check-out) para no considerarlo al reservar la habitación
                    // Tanto en tarifas Hyperguest como en tarifas Aurora
                    if ($rate_plan_room->calendarys && $rate_plan_room->calendarys->count() > 0) {
                        $sorted_calendarys = $rate_plan_room->calendarys->sortBy('date')->values();
                        $filtered = $sorted_calendarys->slice(0, -1);
                        $rate_plan_room->setRelation('calendarys', $filtered);
                    }

                    // Precios por canal si no es Aurora
                    // PRECIOS PARA TARIFA HYPERGUEST
                    if (isset($rate_plan_room->channel) && $rate_plan_room->channel->code != 'AURORA') {
                        $rate_plan_room->channel_child_price = 0;
                        $rate_plan_room->channel_infant_price = 0;

                        $price = RatePlanRoomDateRange::where('rate_plan_room_id', $rate_plan_room->id)
                            ->where(function ($query) use ($date_from, $date_to) {
                                $query->where('date_from', '<=', $date_from)
                                    ->where('date_to', '>=', $date_to);
                            })
                            ->orderBy('group', 'ASC')
                            ->first();

                        if ($price) {
                            $rate_plan_room->channel_child_price = $price->price_child;
                            $rate_plan_room->channel_infant_price = $price->price_infant;
                        }

                        // Si no tiene políticas, se asigna la global
                        if ($rate_plan_room->policies_cancelation->count() == 0) {
                            $rate_plan_room->calendarys->transform(function (RatesPlansCalendarys $calendary) use (
                                $global_policies,
                                $date_from,
                                $date_to
                            ) {
                                if (empty($calendary->policies_cancelation_id) || empty($calendary->policies_cancelation)) {
                                    $calendary->policies_cancelation_id = $global_policies->id;
                                    $calendary->setRelation('policies_cancelation', $global_policies);
                                }

                                return $calendary;
                            });
                        }
                    }

                    return $rate_plan_room;
                });

                return $room;
            });

            $hotel_id = $hotel->id;
            $noches = difDateDays(Carbon::parse($date_from), Carbon::parse($date_to));

            return [
                "client_id" => $client_id,
                "hotel_id" => $hotel_id,
                "check_in" => $date_from,
                "check_out" => $date_to,
                "nights" => $noches,
                "client_markup" => $client_markup,
                "hotel" => $hotel,
            ];
        });

        return $data;
    }
}

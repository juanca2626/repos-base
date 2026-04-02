<?php

namespace App\Http\Traits;

use App\Allotment;
use App\Hotel;
use App\HotelClient;
use App\HotelOptionSupplementCalendar;
use App\Inventory;
use App\Language;
use App\RatesPlansRooms;
use App\RateSupplement;
use App\Room;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

trait ValidateHotelSearch
{
    /**
     * esta funcion retorna un arreglo de ids de hoteles dado un periodo y un client_id
     * @param $client_id
     * @param $period
     * @param $child_min_age_search
     * @param $country_id
     * @param $state_id
     * @param $city_id
     * @param $district_id
     * @param $typeclass_id
     * @return mixed
     */
    public function getArrayIdsByClientHotels(
        $client_id,
        $period,
        $child_min_age_search,
        $country_id,
        $state_id,
        $city_id,
        $district_id,
        $typeclass_id
    )
    {
        $hotels_id = HotelClient::select('hotel_id')->where('client_id', $client_id)->where('period',
            $period)->whereHas('hotel', function (
            $query
        ) use ($child_min_age_search, $country_id, $state_id, $city_id, $district_id, $typeclass_id) {
            $query->where('status', 1);
            if ($country_id != "") {
                $query->whereHas('country', function ($query) use ($country_id) {
                    $query->where('iso', $country_id);
                });
            }
            if ($state_id != "") {
                $query->whereHas('state', function ($query) use ($state_id) {
                    $query->where('iso', $state_id);
                });
            }
            if ($city_id != "") {
                $query->where('city_id', $city_id);
            }
            if ($district_id != "") {
                $query->where('district_id', $district_id);
            }
            if ($typeclass_id != "") {
                $query->where('typeclass_id', $typeclass_id);
            }
        })->pluck('hotel_id')->toArray();

        return $hotels_id;
    }

    public function getArrayIdsAcceptChildHotels(
        $client_id,
        $period,
        $child_min_age_search,
        $country_id,
        $state_id,
        $city_id,
        $district_id,
        $typeclass_id
    )
    {
        $hotels_id = HotelClient::select('hotel_id')->where('client_id', $client_id)->where('period',
            $period)->whereHas('hotel', function (
            $query
        ) use ($child_min_age_search, $country_id, $state_id, $city_id, $district_id, $typeclass_id) {
            $query->where('status', 1);
            $query->where('allows_child', 1);
            $query->where('min_age_child', '<=', $child_min_age_search);
            if ($country_id != "") {
                $query->whereHas('country', function ($query) use ($country_id) {
                    $query->where('iso', $country_id);
                });
            }
            if ($state_id != "") {
                $query->whereHas('state', function ($query) use ($state_id) {
                    $query->where('iso', $state_id);
                });
            }
            if ($city_id != "") {
                $query->where('city_id', $city_id);
            }
            if ($district_id != "") {
                $query->where('district_id', $district_id);
            }
            if ($typeclass_id != "") {
                $query->where('typeclass_id', $typeclass_id);
            }
        })->pluck('hotel_id')->toArray();

        return $hotels_id;
    }

    public function getArrayIdsAcceptInfantsHotels(
        $client_id,
        $period,
        $child_min_age_search,
        $country_id,
        $state_id,
        $city_id,
        $district_id
    )
    {
        $hotels_client = HotelClient::select('hotel_id')->where('client_id', $client_id)->where('period',
            $period)->whereHas('hotel', function (
            $query
        ) use ($child_min_age_search, $country_id, $state_id, $city_id, $district_id) {
            $query->where('status', 1);
            $query->where('allows_teenagers', 1);
            $query->where('min_age_teenagers', '<=', $child_min_age_search);
            $query->where('max_age_teenagers', '>=', $child_min_age_search);

            if ($country_id != "") {
                $query->where('country_id', $country_id);
            }
            if ($state_id != "") {
                $query->where('state_id', $state_id);
            }
            if ($city_id != "") {
                $query->where('city_id', $city_id);
            }
            if ($district_id != "") {
                $query->where('district_id', $district_id);
            }
        })->pluck('hotel_id')->toArray();

        return $hotels_client;
    }

    /**
     * Logica para transformar las tarifas de los channels a la forma en como estan estructuradas en aurora
     * @param $hotels_client
     * @return mixed
     */
    public function transformRatesChannelsInRatesAurora($hotels_client)
    {
        foreach ($hotels_client as $index_hotel => $hotel_client) {
            foreach ($hotel_client["hotel"]["rooms"] as $index_room => $room) {
                foreach ($room["rates_plan_room"] as $index_rate_plan_room => $rate_plan_room) {
                    if ($rate_plan_room["channel_id"] != 1) {
                        $child_rate = null;
                        foreach ($rate_plan_room["calendarys"] as $ind => $calendary) {
                            $collection = collect($calendary["rate"]);

                            $adult_rates = $collection->filter(function ($rate) {
                                return $rate["price_adult"] > 0;
                            });

                            $child_rate = $collection->filter(function ($rate) {
                                return $rate["price_child"] > 0;
                            });

                            $estra_rate = $collection->last(function ($rate) {
                                return $rate["price_extra"] > 0;
                            });

                            $total_room_rate = $collection->last(function ($rate) {
                                return $rate["price_total"] > 0;
                            });

                            if ($total_room_rate) {
                                $total_room_rate["price_adult"] = $total_room_rate["price_total"];
                                $adult_rates->push($total_room_rate);
                            }


                            // $rates_as_aurora = collect();
                            // //travel click u otros retorna id "23540591||" en rates
                            // $adult_rates->each(function ($adult_rate) use ($rates_as_aurora, $child_rate, $estra_rate) {
                            //     $adult_rate['id'] .= '|' . $child_rate['id'];
                            //     $adult_rate["price_child"] = $child_rate["price_child"];

                            //     $adult_rate['id'] .= '|' . $child_rate['id'];
                            //     $adult_rate["price_extra"] = $estra_rate["price_extra"];

                            //     $rates_as_aurora->push($adult_rate);
                            // });

                            // $rate_plan_room = $calendary["rate"];
                            // $rate_plan_room["calendarys"][$ind]["rate"] = $rates_as_aurora->toArray();

                            // $hotels_client[$index_hotel]["hotel"]["rooms"][$index_room]["rates_plan_room"][$index_rate_plan_room]["calendarys"][$ind]["old_rate"] = $calendary["rate"];
                            // $hotels_client[$index_hotel]["hotel"]["rooms"][$index_room]["rates_plan_room"][$index_rate_plan_room]["calendarys"][$ind]["rate"] = $rates_as_aurora->toArray();


                            $hotels_client[$index_hotel]["hotel"]["rooms"][$index_room]["rates_plan_room"][$index_rate_plan_room]["calendarys"][$ind]["old_rate"] = $calendary["rate"];
                            $hotels_client[$index_hotel]["hotel"]["rooms"][$index_room]["rates_plan_room"][$index_rate_plan_room]["calendarys"][$ind]["rate"] = $calendary["rate"];


                        }
                    }
                }
            }
        }

        return $hotels_client;
    }

    /**
     *  Esto es para tarifas que tienen allotment es decir bloqueo de dias
     * @param $client_id
     * @param $reservation_start_date
     * @param $from
     * @return array
     */
    public function getArrayIdsRatesPlansRoomsAllotment($client_id, $reservation_start_date, $from)
    {
        $allotments = Allotment::select('rate_plan_rooms_id', 'num_days_blocked')->where('client_id',
            $client_id)->get();

        $rate_plan_room_ids_include = [];

        foreach ($allotments as $allotment) {
            if ($allotment["num_days_blocked"] != null) {
                $reservation_start_date = $reservation_start_date->addDay($allotment["num_days_blocked"]);

                if ($from > $reservation_start_date->format('Y-m-d')) {
                    array_push($rate_plan_room_ids_include, $allotment["rate_plan_rooms_id"]);
                }
            }
        }

        return $rate_plan_room_ids_include;
    }

    /**
     * //Esto es para tarifas de Aurora validando tiempo de estadia
     * @param $hotels_client_hotel_id_list
     * @param $from
     * @param $reservation_days
     * @return array
     */
    public function getArrayIdsRatesPlansRoomsAuroraTimeStay(
        $hotels_client_hotel_id_list,
        $from,
        $reservation_days,
        $period
    )
    {
        $rate_plan_room_ids_include = [];

        $rate_plans_rooms_aurora = RatesPlansRooms::where('channel_id', 1)->whereHas('rate_plan',
            function ($query) use (
                $hotels_client_hotel_id_list,
                $period
            ) {
                $query->whereDoesntHave('client',
                    function ($query) use ($period) {
                        $query->where('client_id', $this->client_id());
                        $query->where('period', $period);
                        $query->whereNull('client_rate_plans.deleted_at');

                    });
                $query->whereIn('hotel_id', $hotels_client_hotel_id_list);
                $query->whereHas('hotel', function ($query) {
                    $query->where('status', 1);
                });
                $query->where('status', 1);
            })->whereHas('calendarys', function ($query) use ($from) {
            $query->where('date', $from);
        })->with([
            'calendarys' => function ($query) use ($from) {
                $query->with('policies_rates')->with('policies_rates.translations');
                $query->where('date', $from);
            },
        ])->where('status', 1)->get();

        foreach ($rate_plans_rooms_aurora as $aurora) {
            if ($reservation_days >= $aurora["calendarys"][0]["policies_rates"]["min_length_stay"] && $reservation_days <= $aurora["calendarys"][0]["policies_rates"]["max_length_stay"]) {
                array_push($rate_plan_room_ids_include, $aurora["id"]);
            }
        }

        return $rate_plan_room_ids_include;
    }

    /**
     * //Esto es para tarifas de Channels validando tiempo de estadia y dias de anticipacion de reserva
     * @param $hotels_client_hotel_id_list
     * @param $from
     * @param $days_advance_reservation
     * @param $reservation_days
     * @return array
     */
    public function getArrayIdsRatesPlansRoomsChannelsTimeStayAndDaysAdvanceReservationReadonly(
        $hotels_client_hotel_id_list,
        $from,
        $days_advance_reservation,
        $reservation_days,
        array $rate_plan_room_search = []
    ): array
    {
        $query = RatesPlansRooms::on('mysql_read');
        $query->whereIn('id', $rate_plan_room_search);
        $query->where('channel_id', "!=", 1);
        $query->whereHas('rate_plan', function ($q) use ($hotels_client_hotel_id_list) {
            $q->whereIn('hotel_id', $hotels_client_hotel_id_list);
            $q->whereHas('hotel', function ($q) {
                $q->where('status', 1);
            });
            $q->where('status', 1);
        });
        $query->whereHas('room', function ($q) {
            $q->where('state', 1);
        });
        $query->whereHas('calendarys', function ($q) use ($from) {
            $q->where('date', $from);
        });
        $query->with([
            'calendarys' => function ($q) use ($from) {
                $q->where('date', $from);
            },
        ]);
        $query->where('status', 1);

        $rate_plans_rooms_channels = $query->get();
        $rate_plan_room_ids_include = [];
        foreach ($rate_plans_rooms_channels as $rate_plans_rooms_channel) {
            if (
                $days_advance_reservation >= $rate_plans_rooms_channel["calendarys"][0]["min_ab_offset"] ||
                $days_advance_reservation <= $rate_plans_rooms_channel["calendarys"][0]["max_ab_offset"] ||
                $reservation_days >= $rate_plans_rooms_channel["calendarys"][0]["min_length_stay"] ||
                $reservation_days <= $rate_plans_rooms_channel["calendarys"][0]["max_length_stay"]
            ) {
                array_push($rate_plan_room_ids_include, $rate_plans_rooms_channel["id"]);
            }
        }

        return $rate_plan_room_ids_include;
    }

    public function getArrayIdsRatesPlansRoomsChannelsTimeStayAndDaysAdvanceReservation(
        $hotels_client_hotel_id_list,
        $from,
        $days_advance_reservation,
        $reservation_days,
        array $rate_plan_room_search = []
    ): array
    {
        $query = RatesPlansRooms::query();
        $query->whereIn('id', $rate_plan_room_search);
        $query->where('channel_id', "!=", 1);
        $query->whereHas('rate_plan', function ($q) use ($hotels_client_hotel_id_list) {
            $q->whereIn('hotel_id', $hotels_client_hotel_id_list);
            $q->whereHas('hotel', function ($q) {
                $q->where('status', 1);
            });
            $q->where('status', 1);
        });
        $query->whereHas('room', function ($q) {
            $q->where('state', 1);
        });
        $query->whereHas('calendarys', function ($q) use ($from) {
            $q->where('date', $from);
        });
        $query->with([
            'calendarys' => function ($q) use ($from) {
                $q->where('date', $from);
            },
        ]);
        $query->where('status', 1);

        $rate_plans_rooms_channels = $query->get();
        $rate_plan_room_ids_include = [];
        foreach ($rate_plans_rooms_channels as $rate_plans_rooms_channel) {
            if (
                $days_advance_reservation >= $rate_plans_rooms_channel["calendarys"][0]["min_ab_offset"] ||
                $days_advance_reservation <= $rate_plans_rooms_channel["calendarys"][0]["max_ab_offset"] ||
                $reservation_days >= $rate_plans_rooms_channel["calendarys"][0]["min_length_stay"] ||
                $reservation_days <= $rate_plans_rooms_channel["calendarys"][0]["max_length_stay"]
            ) {
                array_push($rate_plan_room_ids_include, $rate_plans_rooms_channel["id"]);
            }
        }

        return $rate_plan_room_ids_include;
    }

    /**
     * Validacion de tarifas de channel para ver si tiene un importe para el numero de adultos de busqueda o tiene un importe total por habitacion
     * @param $hotels_client_hotel_id_list
     * @param $from
     * @param $to
     * @param $rate_plan_room_channels_time_stay_and_days_advance_reservation
     * @param $reservation_days
     * @param $quantity_persons_rooms
     * @return array
     */
    public function getArrayIdsRatesPlansRoomsChannelsCalendarReadonly(
        $hotels_client_hotel_id_list,
        $from,
        $to,
        $rate_plan_room_ids_include,
        $reservation_days,
        array $quantity_persons_rooms
    )
    {
        $query = RatesPlansRooms::on('mysql_read');
        $query->whereIn('id', $rate_plan_room_ids_include);
        $query->whereHas('rate_plan', function ($q) use ($hotels_client_hotel_id_list) {
            $q->whereIn('hotel_id', $hotels_client_hotel_id_list);
            $q->whereHas('hotel', function ($q) {
                $q->where('status', 1);
            });
            $q->where('status', 1);
        });
        $query->whereHas('room', function ($q) {
            $q->where('state', 1);
        });
        $query->whereHas('calendarys', function ($q) use ($from, $to) {
            $q->where('date', '>=', $from);
            $q->where('date', '<=', $to);
        });
        $query->with([
            'calendarys' => function ($q) use ($from, $to) {
                $q->where('date', '>=', $from);
                $q->where('date', '<=', $to);
            },
        ]);
        $query->where('status', 1);

        $rate_plans_rooms_channels = $query->get();

        $rate_plan_room_ids_include = [];
        foreach ($rate_plans_rooms_channels as $rate_plans_rooms_channel) {
            if (count($rate_plans_rooms_channel["calendarys"]) == $reservation_days) {
                $check_calendar = 0;
                foreach ($rate_plans_rooms_channel["calendarys"] as $calendary) {
                    foreach ($calendary["rate"] as $rate) {
                        if (count($quantity_persons_rooms)) {
                            foreach ($quantity_persons_rooms as $quantity_person_room) {
                                if (
                                    ($quantity_person_room["adults"] == $rate["num_adult"] && $rate["price_adult"] > 0) ||
                                    $rate["price_total"]
                                ) {
                                    $check_calendar++;
                                    break 2;
                                }
                            }
                        } else {
                            if (($rate["num_adult"] > 0 && $rate["price_adult"] > 0) || $rate["price_total"]) {
                                $check_calendar++;
                            }
                        }
                    }
                }
                if ($check_calendar == $reservation_days) {
                    array_push($rate_plan_room_ids_include, $rate_plans_rooms_channel["id"]);
                }
            }
        }

        return $rate_plan_room_ids_include;
    }

    public function getArrayIdsRatesPlansRoomsChannelsCalendar(
        $hotels_client_hotel_id_list,
        $from,
        $to,
        $rate_plan_room_ids_include,
        $reservation_days,
        array $quantity_persons_rooms
    )
    {
        $query = RatesPlansRooms::query();
        $query->whereIn('id', $rate_plan_room_ids_include);
        $query->whereHas('rate_plan', function ($q) use ($hotels_client_hotel_id_list) {
            $q->whereIn('hotel_id', $hotels_client_hotel_id_list);
            $q->whereHas('hotel', function ($q) {
                $q->where('status', 1);
            });
            $q->where('status', 1);
        });
        $query->whereHas('room', function ($q) {
            $q->where('state', 1);
        });
        $query->whereHas('calendarys', function ($q) use ($from, $to) {
            $q->where('date', '>=', $from);
            $q->where('date', '<=', $to);
        });
        $query->with([
            'calendarys' => function ($q) use ($from, $to) {
                $q->where('date', '>=', $from);
                $q->where('date', '<=', $to);
            },
        ]);
        $query->where('status', 1);

        $rate_plans_rooms_channels = $query->get();

        $rate_plan_room_ids_include = [];
        foreach ($rate_plans_rooms_channels as $rate_plans_rooms_channel) {
            if (count($rate_plans_rooms_channel["calendarys"]) == $reservation_days) {
                $check_calendar = 0;
                foreach ($rate_plans_rooms_channel["calendarys"] as $calendary) {
                    foreach ($calendary["rate"] as $rate) {
                        if (count($quantity_persons_rooms)) {
                            foreach ($quantity_persons_rooms as $quantity_person_room) {
                                if (
                                    ($quantity_person_room["adults"] == $rate["num_adult"] && $rate["price_adult"] > 0) ||
                                    $rate["price_total"]
                                ) {
                                    $check_calendar++;
                                    break 2;
                                }
                            }
                        } else {
                            if (($rate["num_adult"] > 0 && $rate["price_adult"] > 0) || $rate["price_total"]) {
                                $check_calendar++;
                            }
                        }
                    }
                }
                if ($check_calendar == $reservation_days) {
                    array_push($rate_plan_room_ids_include, $rate_plans_rooms_channel["id"]);
                }
            }
        }

        return $rate_plan_room_ids_include;
    }

    public function getArrayIdsRatesPlansRoomsChannelsValidateOccupancy(
        $hotels_client_hotel_id_list,
        $rate_plan_room_ids_include,
        $from
    )
    {

        $rate_plan_room_ids_include_new = [];
        $rate_plans_rooms_channels = RatesPlansRooms::where('channel_id', "!=", 1)->whereIn('id',
            $rate_plan_room_ids_include)->whereHas('rate_plan', function (
            $query
        ) use ($hotels_client_hotel_id_list) {
            $query->whereIn('hotel_id', $hotels_client_hotel_id_list);
            $query->whereHas('hotel', function ($query) {
                $query->where('status', 1);
            });
            $query->where('status', 1);
        })->whereHas('room', function ($query) {
            $query->where('state', 1);
        })->whereHas('calendarys', function ($query) use ($from) {
            $query->where('date', $from);
        })->with([
            'calendarys' => function ($query) use ($from) {
                $query->where('date', $from);
            },
        ])->where('status', 1)->get();

        foreach ($rate_plans_rooms_channels as $rate_plans_rooms_channel) {

            if ($rate_plans_rooms_channel["calendarys"][0]["max_occupancy"] != null || $rate_plans_rooms_channel["calendarys"][0]["max_occupancy"] > 0) {
                array_push($rate_plan_room_ids_include_new, $rate_plans_rooms_channel["id"]);
            }
        }

        return $rate_plan_room_ids_include_new;
    }

    /**
     * //validacion de tarifas Aurora para ver si tienen una rate_plan_calendary y un importe de adulto o importe total > 0 para el rango de fechas de reservacion
     * @param $hotels_client_hotel_id_list
     * @param $from
     * @param $to
     * @param $rate_plan_room_ids_include
     * @param $reservation_days
     * @return mixed
     */
    public function getArrayIdsRatesPlansRoomsCalendarsInRangeReservationDaysAndAmountAdultOrAmountTotalReadonly(
        $hotels_client_hotel_id_list,
        $from,
        $to,
        $rate_plan_room_ids_include,
        $reservation_days
    )
    {
        $query = RatesPlansRooms::on('mysql_read');
        $query->whereIn('id', $rate_plan_room_ids_include);
        $query->whereHas('rate_plan', function ($q) use ($hotels_client_hotel_id_list) {
            $q->whereIn('hotel_id', $hotels_client_hotel_id_list);
            $q->whereHas('hotel', function ($q) {
                $q->where('status', 1);
            });
            $q->where('status', 1);
        });
        $query->whereHas('room', function ($q) {
            $q->where('state', 1);
        });
        $query->whereHas('calendarys', function ($q) use (
            $from,
            $to,
            $reservation_days
        ) {  // Eliminamos los calendarios que tienen tarifas duplicadas en 1 dia
            $q->select('rates_plans_room_id');
            $q->where('status', 1);
            $q->where('date', '>=', $from);
            $q->where('date', '<=', $to);
            $q->groupBy('rates_plans_room_id');
            $q->havingRaw('COUNT(*) =' . $reservation_days); //$q->havingRaw('COUNT(*) >='.$reservation_days);
        });
        $query->whereHas('calendarys',
            function ($q) use ($from, $to, $reservation_days) { // Aqui eliminamos los precios que no sean mayor a cero
                $q->select('rates_plans_room_id');
                $q->where('status', 1);
                $q->where('date', '>=', $from);
                $q->where('date', '<=', $to);
                $q->whereHas('rate', function ($q) {
                    $q->where('price_adult', '>', 0);
                    $q->orWhere('price_total', '>', 0);
                });
                $q->groupBy('rates_plans_room_id');
                $q->havingRaw('COUNT(*) =' . $reservation_days); //$q->havingRaw('COUNT(*) >='.$reservation_days);
            });
        $query->with([
            'calendarys' => function ($q) use ($from, $to) {
                $q->with('rate');
                $q->where('date', '>=', $from);
                $q->where('date', '<=', $to);
                $q->orderBy('date');
            },
        ]);
        $query->where('status', 1);

        return $query->pluck('id')->toArray();
    }

    public function getArrayIdsRatesPlansRoomsCalendarsInRangeReservationDaysAndAmountAdultOrAmountTotal(
        $hotels_client_hotel_id_list,
        $from,
        $to,
        $reservation_days,
        $date_to,
        $rate_plan_room_ids_AURORA_include,
        $rate_plan_room_ids_HYPERGUEST_include
    ): array
    {
        $dates = $this->generateDateRange($from, $date_to);
        $arrivalDates = array_slice($dates, 0, -1);
        $departureDate = end($dates);

        // ✅ Base query builder
        $baseQuery = function ($ids, $applyRestrictions) use (
            $hotels_client_hotel_id_list,
            $from,
            $to,
            $arrivalDates,
            $departureDate,
            $reservation_days
        ) {
            $query = RatesPlansRooms::query();

            $query->whereIn('id', $ids)
                ->where('status', 1)
                ->whereHas('rate_plan', function ($q) use ($hotels_client_hotel_id_list) {
                    $q->whereIn('hotel_id', $hotels_client_hotel_id_list)
                        ->where('status', 1)
                        ->whereHas('hotel', function ($q) {
                            $q->where('status', 1);
                        });
                })
                ->whereHas('room', function ($q) {
                    $q->where('state', 1);
                });

            $query->whereHas('calendarys', function ($q) use (
                $from, $to, $arrivalDates, $departureDate, $reservation_days, $applyRestrictions
            ) {
                $q->select('rates_plans_room_id')
                    ->where('status', 1)
                    ->whereBetween('date', [$from, $to])
                    ->whereHas('rate', function ($q) {
                        $q->where('price_adult', '>', 0)
                            ->orWhere('price_total', '>', 0);
                    });

                if ($applyRestrictions) {
                    if (!empty($arrivalDates)) {
                        $q->where(function ($query) use ($arrivalDates) {
                            $query->whereIn('date', $arrivalDates)
                                ->where('restriction_status', 1)
                                ->where('restriction_arrival', 1);
                        });
                    }

                    if ($departureDate) {
                        $q->orWhere(function ($query) use ($departureDate) {
                            $query->where('date', $departureDate)
                                ->where('restriction_departure', 0);
                        });
                    }
                }

                $q->groupBy('rates_plans_room_id')
                    ->havingRaw('COUNT(*) = ' . (int) $reservation_days);
            });

            $query->with(['calendarys' => function ($q) use ($from, $to) {
                $q->with('rate')
                    ->whereBetween('date', [$from, $to])
                    ->orderBy('date');
            }]);

            return $query->pluck('id')->toArray();
        };

        // ✅ Ejecutar consultas por canal
        $auroraIds = $baseQuery($rate_plan_room_ids_AURORA_include, false);
        $hyperguestIds = $baseQuery($rate_plan_room_ids_HYPERGUEST_include, true);

        // ✅ Unir y retornar los resultados
        return array_values(array_unique(array_merge($auroraIds, $hyperguestIds)));
    }

    /**
     * validacion de tarifas para ver si tienen inventario de al menos 1 para el rango de fechas de reservacion que no estan en una bolsa
     * @param $rate_plan_room_ids_include
     * @param $hotels_client_hotel_id_list
     * @param $from
     * @param $to
     * @param $reservation_days
     * @return mixed
     */
    public function getArrayIdsRatesPlansRoomsHaveOneInventoryReadonly(
        $rate_plan_room_ids_include,
        $hotels_client_hotel_id_list,
        $from,
        $to,
        $reservation_days
    )
    {
        // no bolsa Aurora
        $query = RatesPlansRooms::on('mysql_read');
        $query->whereIn('id', $rate_plan_room_ids_include);
        $query->whereHas('rate_plan', function ($query) use ($hotels_client_hotel_id_list) {
            $query->whereIn('hotel_id', $hotels_client_hotel_id_list);
            $query->whereHas('hotel', function ($query) {
                $query->where('status', 1);
            });
            $query->where('status', 1);
            $query->where('bag', 0);
        });
        $query->whereHas('room', function ($query) {
            $query->where('state', 1);
            $query->where('inventory', 1);
        });
        $query->whereHas('inventories', function ($query) use (
            $from,
            $to,
            $reservation_days,
            $rate_plan_room_ids_include
        ) {
            $query->select('rate_plan_rooms_id');
            $query->where('date', '>=', $from);
            $query->where('date', '<=', $to);
            $query->where('inventory_num', '>=', 1);
            $query->where('locked', '<>', 1);
            $query->groupBy('rate_plan_rooms_id');
            $query->havingRaw('COUNT(*) >=' . $reservation_days);
        });
        $query->with([
            'inventories' => function ($query) use ($from, $to) {
                $query->where('date', '>=', $from);
                $query->where('date', '<=', $to);
                $query->where('locked', '<>', 1);
                $query->orderBy('date');
            },
        ]);
        $query->where('status', 1);
        $query->where('channel_id', 1);
        $rate_plan_rooms_validate_inventory_range_reservation_days = $query->pluck('id');


        // no bolsa Channels
        $query = RatesPlansRooms::on('mysql_read');
        $query->whereIn('id', $rate_plan_room_ids_include);
        $query->whereHas('rate_plan', function ($query) use ($hotels_client_hotel_id_list) {
            $query->whereIn('hotel_id', $hotels_client_hotel_id_list);
            $query->whereHas('hotel', function ($query) {
                $query->where('status', 1);
            });
            $query->where('status', 1);
            $query->where('bag', 0);
        });
        $query->whereHas('room', function ($query) {
            $query->where('state', 1);
        });
        $query->whereHas('inventories', function ($query) use (
            $from,
            $to,
            $reservation_days,
            $rate_plan_room_ids_include
        ) {
            $query->select('rate_plan_rooms_id');
            $query->where('date', '>=', $from);
            $query->where('date', '<=', $to);
            $query->where('inventory_num', '>=', 1);
            $query->where('locked', '<>', 1);
            $query->groupBy('rate_plan_rooms_id');
            $query->havingRaw('COUNT(*) >=' . $reservation_days);
        });
        $query->with([
            'inventories' => function ($query) use ($from, $to) {
                $query->where('date', '>=', $from);
                $query->where('date', '<=', $to);
                $query->where('locked', '<>', 1);
                $query->orderBy('date');
            },
        ]);
        $query->where('status', 1);
        $query->where('channel_id', '<>', 1);
        $rate_plan_rooms_validate_inventory_range_reservation_days_channels = $query->pluck('id');

        // con bolsa
        $query = RatesPlansRooms::on('mysql_read');
        $query->whereIn('id', $rate_plan_room_ids_include);
        $query->whereHas('rate_plan', function ($query) use ($hotels_client_hotel_id_list) {
            $query->whereIn('hotel_id', $hotels_client_hotel_id_list);
            $query->whereHas('hotel', function ($query) {
                $query->where('status', 1);
            });
            $query->where('bag', 1);
            $query->where('status', 1);
        });
        $query->whereHas('room', function ($query) {
            $query->where('state', 1);

        });
        $query->whereHas('bag_rate.bag_room.inventory_bags', function ($query) use (
            $from,
            $to,
            $reservation_days
        ) {
            $query->select('bag_room_id');
            $query->where('date', '>=', $from);
            $query->where('date', '<=', $to);
            $query->where('inventory_num', '>=', 1);
            $query->where('locked', '<>', 1);
            $query->groupBy('bag_room_id');
            $query->havingRaw('COUNT(*) >=' . $reservation_days);

        });
        $query->with([
            'bag_rate.bag_room.inventory_bags' => function ($query) use ($from, $to, $reservation_days) {
                $query->select('bag_room_id');
                $query->where('date', '>=', $from);
                $query->where('date', '<=', $to);
                $query->where('inventory_num', '>=', 1);
                $query->where('locked', '<>', 1);
                $query->groupBy('bag_room_id');
                $query->havingRaw('COUNT(*) >=' . $reservation_days);
            },
        ]);

        $query->where('status', 1);
        $query->where('channel_id', 1);
        $rate_plan_rooms_validate_inventory_range_reservation_days_bag = $query->pluck('id');

        $result1 = $rate_plan_rooms_validate_inventory_range_reservation_days->toArray();
        $result3 = $rate_plan_rooms_validate_inventory_range_reservation_days_channels->toArray();
        $result2 = $rate_plan_rooms_validate_inventory_range_reservation_days_bag->toArray();

        return array_merge($result1, $result3, $result2);
    }

    public function getArrayIdsRatesPlansRoomsHaveOneInventory(
        $rate_plan_room_ids_include,
        $hotels_client_hotel_id_list,
        $from,
        $to,
        $reservation_days
    )
    {
        // no bolsa Aurora
        $query = RatesPlansRooms::query();
        $query->whereIn('id', $rate_plan_room_ids_include);
        $query->whereHas('rate_plan', function ($query) use ($hotels_client_hotel_id_list) {
            $query->whereIn('hotel_id', $hotels_client_hotel_id_list);
            $query->whereHas('hotel', function ($query) {
                $query->where('status', 1);
            });
            $query->where('status', 1);
            $query->where('bag', 0);
        });
        $query->whereHas('room', function ($query) {
            $query->where('state', 1);
            $query->where('inventory', 1);
        });
        $query->whereHas('inventories', function ($query) use (
            $from,
            $to,
            $reservation_days,
            $rate_plan_room_ids_include
        ) {
            $query->select('rate_plan_rooms_id');
            $query->where('date', '>=', $from);
            $query->where('date', '<=', $to);
            $query->where('inventory_num', '>=', 1);
            $query->where('locked', '<>', 1);
            $query->groupBy('rate_plan_rooms_id');
            $query->havingRaw('COUNT(*) >=' . $reservation_days);
        });
        $query->with([
            'inventories' => function ($query) use ($from, $to) {
                $query->where('date', '>=', $from);
                $query->where('date', '<=', $to);
                $query->where('locked', '<>', 1);
                $query->orderBy('date');
            },
        ]);
        $query->where('status', 1);
        $query->where('channel_id', 1); // AURORA
        $rate_plan_rooms_validate_inventory_range_reservation_days = $query->pluck('id');

        // no bolsa Channels
        $query = RatesPlansRooms::query();
        $query->whereIn('id', $rate_plan_room_ids_include);
        $query->whereHas('rate_plan', function ($query) use ($hotels_client_hotel_id_list) {
            $query->whereIn('hotel_id', $hotels_client_hotel_id_list);
            $query->whereHas('hotel', function ($query) {
                $query->where('status', 1);
            });
            $query->where('status', 1);
            $query->where('bag', 0);
        });
        $query->whereHas('room', function ($query) {
            $query->where('state', 1);
        });
        $query->whereHas('inventories', function ($query) use (
            $from,
            $to,
            $reservation_days,
            $rate_plan_room_ids_include
        ) {
            $query->select('rate_plan_rooms_id');
            $query->where('date', '>=', $from);
            $query->where('date', '<=', $to);
            $query->where('inventory_num', '>=', 1);
            $query->where('locked', '<>', 1);
            $query->groupBy('rate_plan_rooms_id');
            $query->havingRaw('COUNT(*) >=' . $reservation_days);
        });
        $query->with([
            'inventories' => function ($query) use ($from, $to) {
                $query->where('date', '>=', $from);
                $query->where('date', '<=', $to);
                $query->where('locked', '<>', 1);
                $query->orderBy('date');
            },
        ]);
        $query->where('status', 1);
        $query->where('channel_id', '<>', 1); // HYPERGUEST
        $rate_plan_rooms_validate_inventory_range_reservation_days_channels = $query->pluck('id');

        // con bolsa
        $query = RatesPlansRooms::query();
        $query->whereIn('id', $rate_plan_room_ids_include);
        $query->whereHas('rate_plan', function ($query) use ($hotels_client_hotel_id_list) {
            $query->whereIn('hotel_id', $hotels_client_hotel_id_list);
            $query->whereHas('hotel', function ($query) {
                $query->where('status', 1);
            });
            $query->where('bag', 1);
            $query->where('status', 1);
        });
        $query->whereHas('room', function ($query) {
            $query->where('state', 1);
            $query->where('inventory', 1); // SI PERMITE QUE LA HABITACION TENGA INVENTARIO PERO SOLO SE VALIDA DESDE LA BOLSA, NO DESDE LA HABITACION DIRECTAMENTE

        });
        $query->whereHas('bag_rate.bag_room.inventory_bags', function ($query) use (
            $from,
            $to,
            $reservation_days
        ) {
            $query->select('bag_room_id');
            $query->where('date', '>=', $from);
            $query->where('date', '<=', $to);
            $query->where('inventory_num', '>=', 1);
            $query->where('locked', '<>', 1);
            $query->groupBy('bag_room_id');
            $query->havingRaw('COUNT(*) >=' . $reservation_days);

        });
        $query->with([
            'bag_rate.bag_room.inventory_bags' => function ($query) use ($from, $to, $reservation_days) {
                $query->select('bag_room_id');
                $query->where('date', '>=', $from);
                $query->where('date', '<=', $to);
                $query->where('inventory_num', '>=', 1);
                $query->where('locked', '<>', 1);
                $query->groupBy('bag_room_id');
                $query->havingRaw('COUNT(*) >=' . $reservation_days);
            },
        ]);
        $query->where('status', 1);
        $query->where('channel_id', 1); // AURORA
        $rate_plan_rooms_validate_inventory_range_reservation_days_bag = $query->pluck('id');

        // FUSIONAMOS LOS RESULTADOS DE LAS 3 CONSULTAS PARA OBTENER EL RESULTADO FINAL DE LOS ROOMS QUE TIENEN INVENTARIO PARA EL RANGO DE FECHAS DE RESERVACION
        $result1 = $rate_plan_rooms_validate_inventory_range_reservation_days->toArray();
        $result3 = $rate_plan_rooms_validate_inventory_range_reservation_days_channels->toArray();
        $result2 = $rate_plan_rooms_validate_inventory_range_reservation_days_bag->toArray();

        return array_merge($result1, $result3, $result2);
    }

    public function calculateRateSupplementsRequired(
        $rate_plan_id,
        $hotel_id,
        $from,
        $to,
        $client_id,
        $quantity_adults,
        $quantity_child = 0,
        $ages_child = [],
        $markup = [],
        $language_id = 1
    )
    {
        $supplements = [
            "total_amount" => 0,
            "supplements" => [],
            "supplements_optional" => [],
        ];

        //Suplementos Obligatorios que no aplica cargo extra Requeridos
        $supplements_free = RateSupplement::where('rate_plan_id', $rate_plan_id)
            ->where('amount_extra', 0)
            ->where('type', 'required')
            ->whereHas('supplement', function ($q) {
                $q->where('state', 1);
            })->with([
                'supplement' => function ($query) use ($language_id) {
                    $query->select(['id', 'state']);
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select(['value', 'object_id']);
                            $query->where('type', 'suplement');
                            $query->where('language_id', $language_id);
                        },
                    ]);
                },
            ])->get();


        foreach ($supplements_free as $supplement_free) {
            $name = (count($supplement_free["supplement"]["translations"]) > 0 and $supplement_free["supplement"]["translations"] != null) ? $supplement_free["supplement"]["translations"][0]["value"] : '';
            array_push($supplements["supplements"], [
                "total_amount" => 0,
                "id" => $supplement_free["supplement"]['id'],
                "supplement" => $name,
                "calendars" => [],
            ]);
        }


        //Suplementos que si aplica cargo extra y se tiene que hacer el calculo
        $id_supplements = RateSupplement::where('rate_plan_id', $rate_plan_id)
            ->where('type', 'required')
            ->where('amount_extra', 1)
            ->whereHas('supplement', function ($q) {
                $q->where('state', 1);
            })
            ->pluck('supplement_id');

        $calendars = HotelOptionSupplementCalendar::with([
            'supplement' => function ($query) use ($language_id) {
                $query->select(['id', 'per_person', 'per_room', 'state']);
                $query->with([
                    'translations' => function ($query) use ($language_id) {
                        $query->select(['value', 'object_id']);
                        $query->where('type', 'suplement');
                        $query->where('language_id', $language_id);
                    },
                ]);
            },
        ])->where('hotel_id', $hotel_id)
            ->whereIn('supplement_id', $id_supplements)
            ->where('date', '>=', $from)
            ->where('date', '<=', $to)
            ->get()->groupBy('supplement_id');


        foreach ($calendars as $supplement) {
            $total_amount_supplement = 0;
            $supplement_name = "";
            foreach ($supplement as $calendar) {
                $supplement_name = $calendar["supplement"]["translations"][0]["value"];
                if ($calendar["price_per_room"] > 0) {
                    $total_amount_supplement += $calendar["price_per_room"] + ($calendar["price_per_room"] * ($markup["markup"] / 100));

                } else {

                    for ($i = 0; $i < $quantity_adults; $i++) {
                        if ($calendar["min_age"] >= 18 && $calendar["max_age"] <= 100) {
                            $total_amount_supplement += $calendar["price_per_person"] + ($calendar["price_per_person"] * ($markup["markup"] / 100));
                        }
                    }
                    if ($quantity_child > 0) {
                        for ($j = 0; $j < $quantity_child; $j++) {
                            if ($calendar["min_age"] <= $ages_child[$j]["age"] && $calendar["max_age"] >= $ages_child[$j]["age"]) {
                                $total_amount_supplement += $calendar["price_per_person"] + ($calendar["price_per_person"] * ($markup["markup"] / 100));
                            }
                        }
                    }
                }
            }
            array_push($supplements["supplements"], [
                "total_amount" => 0,
                "supplement" => $supplement_name,
                "type" => "required",
                "calendars" => $supplement,
            ]);
        }

        $supplements['total_amount'] = 0;

        return ($supplements);
    }

    public function calculateRatePlanRoomCalendarByPersons(
        $rates_plan_room,
        $num_adults,
        $num_child,
        $num_extras,
        $ages_child,
        $hotel,
        $bed_additional,
        $room
    )
    {
        $room = collect($room);
        $filter_ages_child = collect();
        $children_as_adults = collect();
        $children_as_child = collect();
        $ages_child = collect($ages_child)->sortByDesc('age');

        $min_age_teenagers = $hotel['min_age_teenagers'];
        $max_age_teenagers = $hotel['max_age_teenagers'];
        $min_age_child = $hotel['min_age_child'];
        $max_age_child = $hotel['max_age_child'];
        $bed_additional = $room["bed_additional"];

        if ($num_child > 0) {
            if (count($ages_child) == 0) {
                throw new \Exception(trans('validations.reservations.ages_of_the_children_required'), 1001);
            }
        }

        $room_occupation = $room['room_type']['occupation'];
        for ($i = 0; $i < $num_child; $i++) {
            $age_child = $ages_child[$i];
            if (!isset($age_child["age"])) {
                throw new \Exception(trans('validations.reservations.the_child_ages_field_not_required_format'), 1003);
            }

            if ($hotel['allows_child'] && $age_child["age"] >= $min_age_child && $age_child["age"] <= $max_age_child) {
                $children_as_child->push($age_child);
            } else {
                if ($hotel['allows_teenagers'] && $age_child["age"] >= $min_age_teenagers && $age_child["age"] <= $max_age_teenagers) {
                    $children_as_child->push($age_child);
                } else {
                    $children_as_adults->push($age_child);
                }
            }

            $filter_ages_child->push($age_child);
        }

        foreach ($children_as_child as $age_child) {
            if ($room["max_adults"] > ($num_adults + $children_as_adults->count())) {
                $children_as_adults->push($age_child);

                $children_as_child->shift();
            } else {
                break;
            }
        }

        $total_num_adults = $num_adults + $children_as_adults->count();
        $total_free_slots_adults = $room["max_adults"] - $total_num_adults;

        $rates_plan_room["quantity_adults"] = $num_adults;
        $rates_plan_room["quantity_child"] = $num_child;
        $rates_plan_room["quantity_extras"] = 0;
        $rates_plan_room["ages_child"] = $filter_ages_child;

        $rates_plan_room["total_amount"] = 0;
        $rates_plan_room["total_amount_adult"] = 0;
        $rates_plan_room["total_amount_child"] = 0;
        $rates_plan_room["total_amount_extra"] = 0;
        $rates_plan_room["total_amount_base"] = 0;
        $rates_plan_room["total_amount_adult_base"] = 0;
        $rates_plan_room["total_amount_child_base"] = 0;
        $rates_plan_room["total_amount_extra_base"] = 0;

        $rates_plan_room['show_message_error'] = false;
        $rates_plan_room['message_error'] = "";

        try {
            if (($num_adults + $num_child) > $room['max_capacity']) {
                throw new Exception(trans('validations.reservations.total_number_passengers_maximum_occupancy'), 1003);
            }

            if ($total_num_adults < $room["max_adults"]) {
                throw new Exception(trans('validations.reservations.number_adults_less_number_required_room'), 1003);
            }

            if ($total_num_adults > $room["max_adults"]) {
                throw new Exception(trans('validations.reservations.number_adults_greater_number_allowed_room'), 1003);
            }

            if ($num_child && ($num_child - $children_as_adults->count()) > ($total_free_slots_adults + $room["max_child"])) {
                throw new Exception(trans('validations.reservations.number_children_greater_number_allowed_room'),
                    1003);
            }

            $calendar = collect($rates_plan_room["calendarys"]);
            foreach ($calendar as $index => $day) {
                $total_adult_occupation = $num_adults;

                $rate = $day["rate"][0];

                $rate["price_adult_base"] = $rate["price_adult"];
                $rate["price_adult"] = addMarkup($rate["price_adult"], (float)$rates_plan_room["markup"]["markup"]);
                $rate["total_adult"] = $rate["price_adult"];
                $rate["total_adult_base"] = $rate["price_adult_base"];

                if ($num_child > 0) {
                    if ($rates_plan_room["channel_id"] == 1) {
                        $price_child = 0;
                        foreach ($children_as_child as $age_child) {
                            if ($age_child["age"] >= $min_age_teenagers && $age_child["age"] <= $max_age_teenagers) {
                                // importe de infantes
                                $price_child += $rate["price_infant"];
                            } else {
                                if ($age_child["age"] >= $min_age_child && $age_child["age"] <= $max_age_child) {
                                    // importe de niños
                                    $price_child += $rate["price_child"];
                                } else {
                                    // importe de niños como adulto extra
                                    $price_child += $rate["price_adult"] / $room_occupation;
                                }
                            }
                        }

                        $rate["price_child_base"] = $price_child;
                        $rate["price_child"] = addMarkup((float)$price_child,
                            (float)$rates_plan_room["markup"]["markup"]);
                        $rate["total_child"] = $price_child;
                        $rate["total_child_base"] = $rate["price_child_base"];
                    } else {
                        $rate["price_child_base"] = $rate["price_child"];
                        $rate["price_child"] = addMarkup($rate["price_child"],
                            (float)$rates_plan_room["markup"]["markup"]);
                        $rate["total_child"] = $rate["price_child"] * $num_child;
                        $rate["total_child_base"] = $rate["price_child_base"] * $num_child;
                    }
                } else {
                    $rate["price_child_base"] = 0;
                    $rate["price_child"] = 0;
                    $rate["total_child"] = 0;
                    $rate["total_child_base"] = 0;
                }

                // calculo precio importe extra
                $price_extra = (float)$rate["price_extra"];
                $rate["price_extra"] = addMarkup($price_extra, (float)$rates_plan_room["markup"]["markup"]);
                $rate["price_extra_base"] = $price_extra;

                $rate["total_extra"] = $rate["price_extra"];
                $rate["total_extra_base"] = $rate["price_extra_base"];

                // totales
                $rate["total_amount"] = $rate["total_adult"] + $rate["total_child"] + $rate["total_extra"];
                $rate["total_amount_base"] = $rate["total_adult_base"] + $rate["total_child_base"] + $rate["total_extra_base"];

                // format prices
                $rate["price_adult"] = $this->setHotelPriceFormat($rate["price_adult"], true);
                $rate["price_child"] = $this->setHotelPriceFormat($rate["price_child"], true);
                $rate["price_extra"] = $this->setHotelPriceFormat($rate["price_extra"], true);

                $rate["price_adult_base"] = $this->setHotelPriceFormat($rate["price_adult_base"]);
                $rate["price_child_base"] = $this->setHotelPriceFormat($rate["price_child_base"]);
                $rate["price_extra_base"] = $this->setHotelPriceFormat($rate["price_extra_base"]);

                $rate["total_adult"] = $this->setHotelPriceFormat($rate["total_adult"], true);
                $rate["total_child"] = $this->setHotelPriceFormat($rate["total_child"], true);
                $rate["total_extra"] = $this->setHotelPriceFormat($rate["total_extra"], true);
                $rate["total_amount"] = $this->setHotelPriceFormat($rate["total_amount"], true);

                $rate["total_adult_base"] = $this->setHotelPriceFormat($rate["total_adult_base"]);
                $rate["total_child_base"] = $this->setHotelPriceFormat($rate["total_child_base"]);
                $rate["total_extra_base"] = $this->setHotelPriceFormat($rate["total_extra_base"]);
                $rate["total_amount_base"] = $this->setHotelPriceFormat($rate["total_amount_base"]);

                $day["rate"][0] = $rate;

                // format prices
                $day["total_adult"] = $this->setHotelPriceFormat($day["rate"][0]["total_adult"], true);
                $day["total_child"] = $this->setHotelPriceFormat($day["rate"][0]["total_child"], true);
                $day["total_extra"] = $this->setHotelPriceFormat($day["rate"][0]["total_extra"], true);
                $day["total_amount"] = $this->setHotelPriceFormat($day["rate"][0]["total_amount"], true);

                $day["total_adult_base"] = $this->setHotelPriceFormat($day["rate"][0]["total_adult_base"]);
                $day["total_child_base"] = $this->setHotelPriceFormat($day["rate"][0]["total_child_base"]);
                $day["total_extra_base"] = $this->setHotelPriceFormat($day["rate"][0]["total_extra_base"]);
                $day["total_amount_base"] = $this->setHotelPriceFormat($day["rate"][0]["total_amount_base"]);

                $calendar->offsetSet($index, $day);
            }

            $rates_plan_room["calendarys"] = $calendar->toArray();

            $rates_plan_room["total_amount"] = $calendar->sum("total_adult") + $calendar->sum("total_child") + $calendar->sum("total_extra");
            $rates_plan_room["total_amount_adult"] = $calendar->sum("total_adult");
            $rates_plan_room["total_amount_child"] = $calendar->sum("total_child");
            $rates_plan_room["total_amount_extra"] = $calendar->sum("total_extra");

            $rates_plan_room["total_amount_base"] = $calendar->sum("total_adult_base") + $calendar->sum("total_child_base") + $calendar->sum("total_extra_base");
            $rates_plan_room["total_amount_adult_base"] = $calendar->sum("total_adult_base");
            $rates_plan_room["total_amount_child_base"] = $calendar->sum("total_child_base");
            $rates_plan_room["total_amount_extra_base"] = $calendar->sum("total_extra_base");

            // format prices
            $rates_plan_room["total_amount"] = (float)$this->setHotelPriceFormat($rates_plan_room["total_amount"],
                true);
            $rates_plan_room["total_amount_adult"] = (float)$this->setHotelPriceFormat($rates_plan_room["total_amount_adult"],
                true);
            $rates_plan_room["total_amount_child"] = (float)$this->setHotelPriceFormat($rates_plan_room["total_amount_child"],
                true);
            $rates_plan_room["total_amount_extra"] = (float)$this->setHotelPriceFormat($rates_plan_room["total_amount_extra"],
                true);

            $rates_plan_room["total_amount_base"] = (float)$this->setHotelPriceFormat($rates_plan_room["total_amount_base"]);
            $rates_plan_room["total_amount_adult_base"] = (float)$this->setHotelPriceFormat($rates_plan_room["total_amount_adult_base"]);
            $rates_plan_room["total_amount_child_base"] = (float)$this->setHotelPriceFormat($rates_plan_room["total_amount_child_base"]);
            $rates_plan_room["total_amount_extra_base"] = (float)$this->setHotelPriceFormat($rates_plan_room["total_amount_extra_base"]);

        } catch (Exception $e) {
            $rates_plan_room['code_error'] = $e->getCode();
            $rates_plan_room['message_error'] = $e->getMessage();
            $rates_plan_room['show_message_error'] = true;
        }

        return $rates_plan_room;
    }

    public function setHotelPriceFormat($price, $round = false): string
    {
        if ($round) {
            return roundLito($this->setPriceFormat($price), 'hotel');
        }
        return $this->setPriceFormat($price);
    }

    public function setPriceFormat($price): string
    {
        return number_format($price, 2, '.', '');
    }

    public function calculateRatePlanRoomCalendarByPersonsNew(
        $markup,
        $rates_plan_room,
        $num_adults,
        $num_child
    )
    {
        $rates_plan_room["quantity_adults"] = $num_adults;
        $rates_plan_room["quantity_child"] = $num_child;
        $rates_plan_room["quantity_extras"] = 0;

        $rates_plan_room["total_amount"] = 0;
        $rates_plan_room["total_amount_adult"] = 0;
        $rates_plan_room["total_amount_child"] = 0;
        $rates_plan_room["total_amount_extra"] = 0;

        $rates_plan_room["total_amount_base"] = 0;
        $rates_plan_room["total_amount_adult_base"] = 0;
        $rates_plan_room["total_amount_child_base"] = 0;
        $rates_plan_room["total_amount_extra_base"] = 0;

        for ($j = 0; $j < count($rates_plan_room["calendarys"]); $j++) {
            if ($rates_plan_room["channel_id"] == 1) {
                $rates_plan_room["calendarys"][$j]["rate"][0]["price_adult_base"] = $rates_plan_room["calendarys"][$j]["rate"][0]["price_adult"];
                $rates_plan_room["calendarys"][$j]["rate"][0]["price_adult"] = ($rates_plan_room["calendarys"][$j]["rate"][0]["price_adult_base"] * ($markup / 100)) + $rates_plan_room["calendarys"][$j]["rate"][0]["price_adult_base"];
                $rates_plan_room["calendarys"][$j]["rate"][0]["total_adult"] = $rates_plan_room["calendarys"][$j]["rate"][0]["price_adult"];
                $rates_plan_room["calendarys"][$j]["rate"][0]["total_adult_base"] = $rates_plan_room["calendarys"][$j]["rate"][0]["price_adult_base"];
            }
            if ($rates_plan_room["channel_id"] == 2) {
                foreach ($rates_plan_room["calendarys"][$j]["rate"] as $rate) {
                    if ($rate["num_adult"] == $num_adults) {
                        $rates_plan_room["calendarys"][$j]["rate"][0]["price_adult_base"] = $rate["price_adult"];
                        $rates_plan_room["calendarys"][$j]["rate"][0]["price_adult"] = ($rate["price_adult"] * ($markup / 100)) + $rate["price_adult"];
                        $rates_plan_room["calendarys"][$j]["rate"][0]["total_adult"] = ($rate["price_adult"] * ($markup / 100)) + $rate["price_adult"];
                        $rates_plan_room["calendarys"][$j]["rate"][0]["total_adult_base"] = $rates_plan_room["calendarys"][$j]["rate"][0]["price_adult_base"];
                    }
                }
            }

            if ($rates_plan_room["channel_id"] == 1) {
                $rates_plan_room["calendarys"][$j]["rate"][0]["price_child_base"] = $rates_plan_room["calendarys"][$j]["rate"][0]["price_child"];
                $rates_plan_room["calendarys"][$j]["rate"][0]["price_child"] = ($rates_plan_room["calendarys"][$j]["rate"][0]["price_child_base"] * ($markup / 100)) + $rates_plan_room["calendarys"][$j]["rate"][0]["price_child_base"];
                $rates_plan_room["calendarys"][$j]["rate"][0]["total_child"] = $rates_plan_room["calendarys"][$j]["rate"][0]["price_child"] * $num_child;
                $rates_plan_room["calendarys"][$j]["rate"][0]["total_child_base"] = $rates_plan_room["calendarys"][$j]["rate"][0]["price_child_base"] * $num_child;
            }

            if ($rates_plan_room["channel_id"] == 2) {
                foreach ($rates_plan_room["calendarys"][$j]["rate"] as $index => $rate) {
                    if ($rate["num_child"] > 0) {
                        $rates_plan_room["calendarys"][$j]["rate"][0]["price_child_base"] = $rate["price_child"];
                        $rates_plan_room["calendarys"][$j]["rate"][0]["price_child"] = ($rates_plan_room["calendarys"][$j]["rate"][0]["price_child_base"] * ($markup / 100)) + $rates_plan_room["calendarys"][$j]["rate"][0]["price_child_base"];
                        $rates_plan_room["calendarys"][$j]["rate"][0]["total_child"] = $rates_plan_room["calendarys"][$j]["rate"][0]["price_child"] * $num_child;
                        $rates_plan_room["calendarys"][$j]["rate"][0]["total_child_base"] = $rates_plan_room["calendarys"][$j]["rate"][0]["price_child_base"] * $num_child;
                    }
                }
            }

            if ($rates_plan_room["channel_id"] == 1) {
                $rates_plan_room["calendarys"][$j]["rate"][0]["price_extra_base"] = $rates_plan_room["calendarys"][$j]["rate"][0]["price_extra"];
                $rates_plan_room["calendarys"][$j]["rate"][0]["price_extra"] = ($rates_plan_room["calendarys"][$j]["rate"][0]["price_extra_base"] * ($markup / 100)) + $rates_plan_room["calendarys"][$j]["rate"][0]["price_extra_base"];
                $rates_plan_room["calendarys"][$j]["rate"][0]["total_extra"] = ($rates_plan_room["calendarys"][$j]["rate"][0]["price_extra_base"] * ($markup / 100)) + $rates_plan_room["calendarys"][$j]["rate"][0]["price_extra_base"];
                $rates_plan_room["calendarys"][$j]["rate"][0]["total_extra_base"] = $rates_plan_room["calendarys"][$j]["rate"][0]["price_extra_base"];
            }
            if ($rates_plan_room["channel_id"] != 1) {
                $rates_plan_room["calendarys"][$j]["rate"][0]["price_extra_base"] = 0;
                $rates_plan_room["calendarys"][$j]["rate"][0]["price_extra"] = 0;
                $rates_plan_room["calendarys"][$j]["rate"][0]["total_extra"] = 0;
                $rates_plan_room["calendarys"][$j]["rate"][0]["total_extra_base"] = 0;
            }

            $rates_plan_room["calendarys"][$j]["rate"][0]["total_amount"] = $rates_plan_room["calendarys"][$j]["rate"][0]["total_adult"] + $rates_plan_room["calendarys"][$j]["rate"][0]["total_child"] + $rates_plan_room["calendarys"][$j]["rate"][0]["total_extra"];
            $rates_plan_room["calendarys"][$j]["rate"][0]["total_amount_base"] = $rates_plan_room["calendarys"][$j]["rate"][0]["total_adult_base"] + $rates_plan_room["calendarys"][$j]["rate"][0]["total_child_base"] + $rates_plan_room["calendarys"][$j]["rate"][0]["total_extra_base"];

            $rates_plan_room["calendarys"][$j]["total_amount"] = $rates_plan_room["calendarys"][$j]["rate"][0]["total_adult"] + $rates_plan_room["calendarys"][$j]["rate"][0]["total_child"] + $rates_plan_room["calendarys"][$j]["rate"][0]["total_extra"];
            $rates_plan_room["calendarys"][$j]["total_amount_base"] = $rates_plan_room["calendarys"][$j]["rate"][0]["total_adult_base"] + $rates_plan_room["calendarys"][$j]["rate"][0]["total_child_base"] + $rates_plan_room["calendarys"][$j]["rate"][0]["total_extra_base"];

            $rates_plan_room["total_amount"] += $rates_plan_room["calendarys"][$j]["rate"][0]["total_adult"] + $rates_plan_room["calendarys"][$j]["rate"][0]["total_child"] + $rates_plan_room["calendarys"][$j]["rate"][0]["total_extra"];
            $rates_plan_room["total_amount_adult"] += $rates_plan_room["calendarys"][$j]["rate"][0]["total_adult"];
            $rates_plan_room["total_amount_child"] += $rates_plan_room["calendarys"][$j]["rate"][0]["total_child"];
            $rates_plan_room["total_amount_extra"] += $rates_plan_room["calendarys"][$j]["rate"][0]["total_extra"];

            $rates_plan_room["total_amount_base"] += $rates_plan_room["calendarys"][$j]["rate"][0]["total_adult_base"] + $rates_plan_room["calendarys"][$j]["rate"][0]["total_child_base"] + $rates_plan_room["calendarys"][$j]["rate"][0]["total_extra_base"];
            $rates_plan_room["total_amount_adult_base"] += $rates_plan_room["calendarys"][$j]["rate"][0]["total_adult_base"];
            $rates_plan_room["total_amount_child_base"] += $rates_plan_room["calendarys"][$j]["rate"][0]["total_child_base"];
            $rates_plan_room["total_amount_extra_base"] += $rates_plan_room["calendarys"][$j]["rate"][0]["total_extra_base"];
        }

        return $rates_plan_room;
    }

    public function calculateRatePlanRoomChannelsCalendarByPersons(
        $rate_plan_room,
        $rate_plan_room_id,
        $room_capacity,
        $num_adults,
        $num_child,
        $num_extras
    )
    {
        $selected_rates = collect();
        if ($rate_plan_room["id"] == $rate_plan_room_id) {
            foreach ($rate_plan_room["calendarys"] as $ind => $calendarys) {
                if ($num_adults > 0 && $num_child == 0) {
                    foreach ($calendarys["rate"] as $importe_tarifa) {
                        if ($importe_tarifa["num_adult"] == $num_adults) {
                            if ($num_child > 0 and $importe_tarifa["price_child"] == null) {
                                continue;
                            }
                            if ($num_extras > 0 and $importe_tarifa["price_extra"] == null) {
                                continue;
                            }

                            $importe_tarifa['price_adult'] = !$importe_tarifa['price_adult'] ? 0 : $importe_tarifa['price_adult'];
                            $importe_tarifa['price_child'] = !$importe_tarifa['price_child'] ? 0 : $importe_tarifa['price_child'];
                            $importe_tarifa['price_infant'] = !$importe_tarifa['price_infant'] ? 0 : $importe_tarifa['price_infant'];
                            $importe_tarifa['price_extra'] = !$importe_tarifa['price_extra'] ? 0 : $importe_tarifa['price_extra'];
                            $importe_tarifa['price_total'] = !$importe_tarifa['price_total'] ? 0 : $importe_tarifa['price_total'];

                            $selected_rates->offsetSet($ind, $importe_tarifa);
                            break;
                        }
                    }
                }

                if ($num_adults > 0 && $num_child > 0) {
                    $check_price_child = false;

                    $price_child = 0;
                    foreach ($calendarys["rate"] as $importe_tarifa) {
                        if ($importe_tarifa["num_child"] == $num_child) {
                            $price_child = !$importe_tarifa['price_child'] ? 0 : $importe_tarifa['price_child'];
                            $check_price_child = true;
                        }
                    }
                    foreach ($calendarys["rate"] as $importe_tarifa) {
                        if ($importe_tarifa["num_adult"] == $num_adults) {
                            $importe_tarifa['price_adult'] = !$importe_tarifa['price_adult'] ? 0 : $importe_tarifa['price_adult'];
                            $importe_tarifa['price_child'] = $price_child;
                            $importe_tarifa['price_infant'] = !$importe_tarifa['price_infant'] ? 0 : $importe_tarifa['price_infant'];
                            $importe_tarifa['price_extra'] = !$importe_tarifa['price_extra'] ? 0 : $importe_tarifa['price_extra'];
                            $importe_tarifa['price_total'] = !$importe_tarifa['price_total'] ? 0 : $importe_tarifa['price_total'];

                            if ($check_price_child) {
                                $selected_rates->offsetSet($ind, $importe_tarifa);
                                break;
                            }
                        }
                    }
                }
                if (!$selected_rates->offsetExists($ind)) {
                    foreach ($calendarys["rate"] as $importe_tarifa) {
                        if ($importe_tarifa["price_total"] > 0) {
                            if ($num_extras > 0 and $importe_tarifa["price_extra"] == null) {
                                continue;
                            }

                            $importe_tarifa['price_adult'] = !$importe_tarifa['price_adult'] ? 0 : $importe_tarifa['price_adult'];
                            $importe_tarifa['price_child'] = !$importe_tarifa['price_child'] ? 0 : $importe_tarifa['price_child'];
                            $importe_tarifa['price_infant'] = !$importe_tarifa['price_infant'] ? 0 : $importe_tarifa['price_infant'];
                            $importe_tarifa['price_extra'] = !$importe_tarifa['price_extra'] ? 0 : $importe_tarifa['price_extra'];
                            $importe_tarifa['price_total'] = !$importe_tarifa['price_total'] ? 0 : $importe_tarifa['price_total'];

                            $selected_rates->offsetSet($ind, $importe_tarifa);
                            break;
                        }
                    }
                }
            }
        }

        // si el count de $selected_rates es menor al de  $rate_plan_room["calendarys"] no hay tarifas adecuadas
        // para todos los dias
        if ($selected_rates->count() < count($rate_plan_room["calendarys"])) {
            return false;
        }

        return $this->calculateRatePlanRoomChannelsCalendar($selected_rates, $rate_plan_room, $num_adults, $num_child,
            $num_extras);
    }

    public function getRateGuestNumsByType($calendars, $guestType)
    {
        $rateGetsNums = collect($calendars)
            ->pluck('rate')
            ->map(function ($rates) use ($guestType) {
                return collect($rates)->pluck($guestType);
            });

        $result = collect($rateGetsNums[0]);
        for ($i = 1; $i < $rateGetsNums->count(); $i++) {
            $result = $result->intersect($rateGetsNums[$i]);
        }

        return $result->sort()->reverse()->values();
    }

    public function getAppropriateRateGuestNumber($rateGetsNums, $guestNum)
    {
        $result = $rateGetsNums->first(function ($item) use ($guestNum) {
            return $item == $guestNum;
        });

        if (!$result) {
            $result = $rateGetsNums->first(function ($item) use ($guestNum) {
                return $item > $guestNum;
            });
        }

        return $result;
    }

    // Función para saber si un niño tendra tarifa gratis, osea se le ignorara el precio
    // Requerimiento especificamente solicitado por Hotel Casa Andina
    public function isOneChildrenFree(int $ignore, int $i, bool $flat = false): bool
    {
        if ($flat) {
            if ($ignore != 1 || $i !== 0) {
                return true;
            } else {
                return false;
            }
        } else {
            if ($ignore && ($i === 0)) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function getChannelsAvailableRates(
        $rates_plan_room,
        $num_adults,
        $num_child,
        $ages_child,
        $hotel,
        $room,
        $forReservation = false
    )
    {
        $room = collect($room);
        $filter_ages_child = collect();
        $children_as_adults = collect();
        $children_as_child = collect();
        $ages_child = collect($ages_child)->sortByDesc('age')->values(); // Ordenar por campo e indices

        $min_age_teenagers = $hotel['min_age_teenagers'];
        $max_age_teenagers = $hotel['max_age_teenagers'];
        $min_age_child = $hotel['min_age_child'];
        $max_age_child = $hotel['max_age_child'];

        $childrenAsNumAdults = 0;
        $childrensRateFree = [];

        $ignore_rate_child = Room::where('id', $room['id'])->value('ignore_rate_child');

        for ($i = 0; $i < $num_child; $i++) {
            $childrensRateFree[$i] = false; // Tarifa niño inicializado en false
            $age_child = $ages_child[$i];

            if ($hotel['allows_child'] && $age_child["age"] >= $min_age_child && $age_child["age"] <= $max_age_child) {

                if ($this->isOneChildrenFree($ignore_rate_child, $i)) {
                    $childrensRateFree[$i] = true; // Tarifa niño ignorado
                    $childrenAsNumAdults = $num_adults;

                    $children_as_child->push($ages_child[$i]);
                } else {
                    if ($this->isOneChildrenFree($ignore_rate_child, $i, true)) {
                        $children_as_child->push($ages_child[$i]);
                    }
                }

                $filter_ages_child->push($age_child);
            } else {
                if ($hotel['allows_teenagers'] && $age_child["age"] >= $min_age_teenagers && $age_child["age"] <= $max_age_teenagers) {
                    if ($this->isOneChildrenFree($ignore_rate_child, $i, true)) {
                        $children_as_child->push($ages_child[$i]);
                    }

                    $filter_ages_child->push($age_child);
                } else {
                    $children_as_adults->push($ages_child[$i]);
                }
            }
        }

        $total_num_adults = $num_adults + $children_as_adults->count();

        $rates_plan_room["quantity_adults"] = $total_num_adults;
        $rates_plan_room["quantity_child"] = $num_child;
        $rates_plan_room["quantity_extras"] = 0;
        $rates_plan_room["ages_child"] = $filter_ages_child;
        $rates_plan_room['has_channel_child_rate'] = false;

        $rates_plan_room["total_amount"] = 0;
        $rates_plan_room["total_amount_adult"] = 0;
        $rates_plan_room["total_amount_child"] = 0;
        $rates_plan_room["total_amount_extra"] = 0;
        $rates_plan_room["total_amount_base"] = 0;
        $rates_plan_room["total_amount_adult_base"] = 0;
        $rates_plan_room["total_amount_child_base"] = 0;
        $rates_plan_room["total_amount_extra_base"] = 0;

        $rates_plan_room['show_message_error'] = false;
        $rates_plan_room['message_error'] = "";

        try {
            if (($num_adults + $num_child) > $room['max_capacity']) {
                throw new Exception(trans('validations.reservations.total_number_passengers_maximum_occupancy'), 1003);
            }

            if ($total_num_adults > $room["max_adults"]) {
                if (!$ignore_rate_child || !($total_num_adults === $childrenAsNumAdults)) {
                    throw new Exception(trans('validations.reservations.number_adults_greater_number_allowed_room'), 1003);
                }
            }

            $total_free_slots_adults = 0;
            if (count($ages_child) > 0) {
                // Iterar tarifas de niños
                for ($i = 0; $i < count($childrensRateFree); $i++) {
                    $isFree = $childrensRateFree[$i]; // Identificar que tarifa de niño se va a ignorar
                    if ($isFree) {
                        $total_free_slots_adults = 1; // Se agrega un slot de adulto para saltar este niño
                        $children_as_child->forget(0); // Se elimina un niño en la posición "cero" para ajustar precio
                    } else {
                        $total_free_slots_adults = $room["max_adults"] - $total_num_adults;
                    }
                }
            } else {
                $total_free_slots_adults = $room["max_adults"] - $total_num_adults;
            }

            if ($children_as_child->count()) {
                $total_max_child = $total_free_slots_adults + $room["max_child"];
                if ($children_as_child->count() > $total_max_child) {
                    throw new Exception(trans('validations.reservations.number_children_greater_number_allowed_room'),
                        1003);
                }
            }

            $rateAdultNums = $this->getRateGuestNumsByType($rates_plan_room['calendarys'], 'num_adult');

            $numAdultRate = null;
            if ($children_as_child->count() && $total_free_slots_adults) {
                if ($children_as_child->count() >= $total_free_slots_adults) {
                    $newTotalNumAdults = $total_num_adults + $total_free_slots_adults;
                } else {
                    $newTotalNumAdults = $total_num_adults + $children_as_child->count();
                }

                for ($i = $newTotalNumAdults; $i > $total_num_adults; $i--) {
                    $numAdultRate = $this->getAppropriateRateGuestNumber($rateAdultNums, $i);
                    if ($numAdultRate) {
                        if ($i < $newTotalNumAdults) {
                            for ($a = $newTotalNumAdults - $i; $a > 0; $a--) {
                                $children_as_child->shift();
                            }
                        } else {
                            $children_as_child = collect();
                        }

                        $total_num_adults = $i;
                        break;
                    }
                }
            }

            $rateProviderMethod = isset($rates_plan_room['rateProviderMethod']) ? $rates_plan_room['rateProviderMethod'] : 0;

            if ($rateProviderMethod<2 and ($total_num_adults < $room["max_adults"] )) {
                throw new Exception(trans('validations.reservations.number_adults_less_number_required_room'), 1003);
            }

            if ($forReservation) {
                $rates_plan_room["quantity_adults"] = $total_num_adults;
                $rates_plan_room["quantity_child"] = $children_as_child->count();
                $rates_plan_room["ages_child"] = $children_as_child;
            }

            if (!$numAdultRate) {
                $numAdultRate = $this->getAppropriateRateGuestNumber($rateAdultNums, $total_num_adults);
            }

            if ($children_as_child->count()) {
                $rateChildNums = $this->getRateGuestNumsByType($rates_plan_room['calendarys'], 'num_child');
                $numChildRate = $this->getAppropriateRateGuestNumber($rateChildNums, $children_as_child->count());
            } else {
                $numChildRate = null;
            }

            $calendar = collect($rates_plan_room["calendarys"]);
            foreach ($calendar as $index => $day) {
                if ($numAdultRate) {
                    $rate = collect($day['rate'])->first(function ($item) use ($numAdultRate) {
                        return $item['num_adult'] === $numAdultRate;
                    });
                } else {
                    $rate = collect($day['rate'])->first(function ($item) use ($numAdultRate) {
                        return $item['price_total'] > 0;
                    });

                    if (!$rate) {
                        throw new Exception(trans('validations.reservations.no_appropriate_amount_for_this_fee'), 1003);
                    }

                    $rate["price_adult"] = $rate['price_total'];
                }

                $rate["price_adult_base"] = $rate["price_adult"];
                $rate["price_adult"] = addMarkup($rate["price_adult"], (float)$rates_plan_room["markup"]["markup"]);
                $rate["total_adult"] = $rate["price_adult"];
                $rate["total_adult_base"] = $rate["price_adult_base"];

                // Obtener el precio de los niños o infantes
                if ($children_as_child->count()) {
                    $childRate = null;
                    if ($numChildRate) {
                        $childRate = collect($day['rate'])->first(function ($item) use ($numChildRate) {
                            return $item['num_child'] === $numChildRate;
                        });
                    }

                    if ($childRate) {
                        $rate['channel_child_rate_id'] = $childRate['id'];

                        $valor_price_child = floatval($childRate['price_child']);

                        if ($valor_price_child === 0.0 && isset($rates_plan_room["channel_child_price"]) && isset($rates_plan_room["channel_infant_price"])) {
                            $rate['price_child'] = $rates_plan_room['channel_child_price'] ?? 0;
                            $rate['price_infant'] = $rates_plan_room['channel_infant_price'] ?? 0;
                        } else {
                            $rate['price_child'] = $childRate['price_child'] ?? 0;
                            $rate['price_infant'] = $childRate['price_child'] ?? 0;
                        }

                        $rate['num_child'] = $childRate['num_child'];
                        $rate['num_infant'] = $childRate['num_child'];

                        $rates_plan_room['has_channel_child_rate'] = true;
                    } else {
                        $rate['channel_child_rate_id'] = 'default';

                        $rate['price_child'] = $rates_plan_room['channel_child_price'] ?? 0;
                        $rate['price_infant'] = $rates_plan_room['channel_infant_price'] ?? 0;

                        $rate['num_child'] = $children_as_child->count();
                        $rate['num_infant'] = $children_as_child->count();
                    }
                }

                // Calcular el importe de los niños o infantes
                if (($children_as_child->count()) > 0) {
                    $price_child = 0;
                    foreach ($children_as_child as $age_child) {
                        if ($age_child["age"] >= $min_age_teenagers && $age_child["age"] <= $max_age_teenagers) {
                            // importe de infantes
                            $price_child += $rate["price_infant"];
                        } else {
                            if ($age_child["age"] >= $min_age_child && $age_child["age"] <= $max_age_child) {
                                // importe de niños
                                $price_child += $rate["price_child"];
                            }
                        }
                    }

                    $rate["price_child_base"] = $price_child;
                    $rate["price_child"] = addMarkup((float)$price_child, (float)$rates_plan_room["markup"]["markup"]);
                    $rate["total_child"] = $rate['price_child'];
                    $rate["total_child_base"] = $rate["price_child_base"];
                } else {
                    $rate["price_child_base"] = 0;
                    $rate["price_child"] = 0;
                    $rate["total_child"] = 0;
                    $rate["total_child_base"] = 0;
                }

                $rate['total_child_kluizsv'] = $children_as_child->count();
                $rate['childs'] = $children_as_child;

                // calculo precio importe extra
                $price_extra = (float)$rate["price_extra"];
                $rate["price_extra"] = addMarkup($price_extra, (float)$rates_plan_room["markup"]["markup"]);
                $rate["price_extra_base"] = $price_extra;

                $rate["total_extra"] = $rate["price_extra"];
                $rate["total_extra_base"] = $rate["price_extra_base"];

                // totales
                $rate["total_amount"] = $rate["total_adult"] + $rate["total_child"] + $rate["total_extra"];
                $rate["total_amount_base"] = $rate["total_adult_base"] + $rate["total_child_base"] + $rate["total_extra_base"];

                // format prices
                $rate["price_adult"] = $this->setHotelPriceFormat($rate["price_adult"], true);
                $rate["price_child"] = $this->setHotelPriceFormat($rate["price_child"], true);
                $rate["price_extra"] = $this->setHotelPriceFormat($rate["price_extra"], true);

                $rate["price_adult_base"] = $this->setHotelPriceFormat($rate["price_adult_base"]);
                $rate["price_child_base"] = $this->setHotelPriceFormat($rate["price_child_base"]);
                $rate["price_extra_base"] = $this->setHotelPriceFormat($rate["price_extra_base"]);

                $rate["total_adult"] = $this->setHotelPriceFormat($rate["total_adult"], true);
                $rate["total_child"] = $this->setHotelPriceFormat($rate["total_child"], true);
                $rate["total_extra"] = $this->setHotelPriceFormat($rate["total_extra"], true);
                $rate["total_amount"] = $this->setHotelPriceFormat($rate["total_amount"], true);

                $rate["total_adult_base"] = $this->setHotelPriceFormat($rate["total_adult_base"]);
                $rate["total_child_base"] = $this->setHotelPriceFormat($rate["total_child_base"]);
                $rate["total_extra_base"] = $this->setHotelPriceFormat($rate["total_extra_base"]);
                $rate["total_amount_base"] = $this->setHotelPriceFormat($rate["total_amount_base"]);

                $day["rate"] = [$rate];

                // format prices
                $day["total_adult"] = $this->setHotelPriceFormat($day["rate"][0]["total_adult"], true);
                $day["total_child"] = $this->setHotelPriceFormat($day["rate"][0]["total_child"], true);
                $day["total_extra"] = $this->setHotelPriceFormat($day["rate"][0]["total_extra"], true);
                $day["total_amount"] = $this->setHotelPriceFormat($day["rate"][0]["total_amount"], true);

                $day["total_adult_base"] = $this->setHotelPriceFormat($day["rate"][0]["total_adult_base"]);
                $day["total_child_base"] = $this->setHotelPriceFormat($day["rate"][0]["total_child_base"]);
                $day["total_extra_base"] = $this->setHotelPriceFormat($day["rate"][0]["total_extra_base"]);
                $day["total_amount_base"] = $this->setHotelPriceFormat($day["rate"][0]["total_amount_base"]);

                $calendar->offsetSet($index, $day);
            }

            $rates_plan_room["calendarys"] = $calendar->toArray();

            $rates_plan_room["total_amount"] = $calendar->sum("total_adult") + $calendar->sum("total_child") + $calendar->sum("total_extra");
            $rates_plan_room["total_amount_adult"] = $calendar->sum("total_adult");
            $rates_plan_room["total_amount_child"] = $calendar->sum("total_child");
            $rates_plan_room["total_amount_extra"] = $calendar->sum("total_extra");

            $rates_plan_room["total_amount_base"] = $calendar->sum("total_adult_base") + $calendar->sum("total_child_base") + $calendar->sum("total_extra_base");
            $rates_plan_room["total_amount_adult_base"] = $calendar->sum("total_adult_base");
            $rates_plan_room["total_amount_child_base"] = $calendar->sum("total_child_base");
            $rates_plan_room["total_amount_extra_base"] = $calendar->sum("total_extra_base");

            // format prices
            $rates_plan_room["total_amount"] = (float)$this->setHotelPriceFormat($rates_plan_room["total_amount"],
                true);
            $rates_plan_room["total_amount_adult"] = (float)$this->setHotelPriceFormat($rates_plan_room["total_amount_adult"],
                true);
            $rates_plan_room["total_amount_child"] = (float)$this->setHotelPriceFormat($rates_plan_room["total_amount_child"],
                true);
            $rates_plan_room["total_amount_extra"] = (float)$this->setHotelPriceFormat($rates_plan_room["total_amount_extra"],
                true);

            $rates_plan_room["total_amount_base"] = (float)$this->setHotelPriceFormat($rates_plan_room["total_amount_base"]);
            $rates_plan_room["total_amount_adult_base"] = (float)$this->setHotelPriceFormat($rates_plan_room["total_amount_adult_base"]);
            $rates_plan_room["total_amount_child_base"] = (float)$this->setHotelPriceFormat($rates_plan_room["total_amount_child_base"]);
            $rates_plan_room["total_amount_extra_base"] = (float)$this->setHotelPriceFormat($rates_plan_room["total_amount_extra_base"]);
        } catch (Exception $e) {
            $rates_plan_room['code_error'] = $e->getCode();
            $rates_plan_room['message_error'] = $e->getMessage();
            $rates_plan_room['show_message_error'] = true;
        }

        return $rates_plan_room;
    }

    public function getChannelsFirstAvailRate($rate_plan_room, $rate_plan_room_id)
    {
        if ($rate_plan_room["id"] == $rate_plan_room_id) {
            for ($num_adults = 1; $num_adults <= 14; $num_adults++) {
                $selected_rates = collect();
                foreach ($rate_plan_room["calendarys"] as $ind => $calendarys) {
                    foreach ($calendarys["rate"] as $importe_tarifa) {
                        if ($importe_tarifa["num_adult"] == $num_adults) {
                            $importe_tarifa['price_adult'] = !$importe_tarifa['price_adult'] ? 0 : $importe_tarifa['price_adult'];
                            $importe_tarifa['price_child'] = !$importe_tarifa['price_child'] ? 0 : $importe_tarifa['price_child'];
                            $importe_tarifa['price_infant'] = !$importe_tarifa['price_infant'] ? 0 : $importe_tarifa['price_infant'];
                            $importe_tarifa['price_extra'] = !$importe_tarifa['price_extra'] ? 0 : $importe_tarifa['price_extra'];
                            $importe_tarifa['price_total'] = !$importe_tarifa['price_total'] ? 0 : $importe_tarifa['price_total'];

                            $selected_rates->offsetSet($ind, $importe_tarifa);
                            break;
                        }
                    }
                    if (!$selected_rates->offsetExists($ind)) {
                        foreach ($calendarys["rate"] as $importe_tarifa) {
                            if ($importe_tarifa["price_total"] > 0) {
                                $importe_tarifa['price_adult'] = !$importe_tarifa['price_adult'] ? 0 : $importe_tarifa['price_adult'];
                                $importe_tarifa['price_child'] = !$importe_tarifa['price_child'] ? 0 : $importe_tarifa['price_child'];
                                $importe_tarifa['price_infant'] = !$importe_tarifa['price_infant'] ? 0 : $importe_tarifa['price_infant'];
                                $importe_tarifa['price_extra'] = !$importe_tarifa['price_extra'] ? 0 : $importe_tarifa['price_extra'];
                                $importe_tarifa['price_total'] = !$importe_tarifa['price_total'] ? 0 : $importe_tarifa['price_total'];

                                $selected_rates->offsetSet($ind, $importe_tarifa);
                                break;
                            }
                        }
                    }
                }
                // si el count de $selected_rates es menor al de  $rate_plan_room["calendarys"] no hay tarifas adecuadas
                // para todos los dias
                if ($selected_rates->count() < count($rate_plan_room["calendarys"])) {
                    continue;
                }

                return $this->calculateRatePlanRoomChannelsCalendar($selected_rates, $rate_plan_room, $num_adults, 0, 0,
                    true);
            }
        }

        return false;
    }

    public function calculateRatePlanRoomChannelsCalendar(
        Collection $selected_rates,
                   $rate_plan_room,
                   $num_adults,
                   $num_child,
                   $num_extras,
                   $cho_algo = false
    )
    {
        $rate_plan_room["quantity_adults"] = $num_adults;
        $rate_plan_room["quantity_child"] = $num_child;
        $rate_plan_room["quantity_extras"] = $num_extras;

        $rate_plan_room["total_amount"] = 0;
        $rate_plan_room["total_amount_adult"] = 0;
        $rate_plan_room["total_amount_child"] = 0;
        $rate_plan_room["total_amount_extra"] = 0;

        $rate_plan_room["total_amount_base"] = 0;
        $rate_plan_room["total_amount_adult_base"] = 0;
        $rate_plan_room["total_amount_child_base"] = 0;
        $rate_plan_room["total_amount_extra_base"] = 0;

        foreach ($rate_plan_room["calendarys"] as $ind => $calendarys) {
            $rate = $selected_rates->offsetGet($ind);
            $calendarys["rate"] = [$rate];

            $calendarys["rate"][0]["price_adult_base"] = number_format($rate["price_adult"], 2, '.', '');
            $calendarys["rate"][0]["price_adult"] = number_format(addMarkup($rate["price_adult"],
                (float)$rate_plan_room["markup"]["markup"]), 2, '.', '');
            $calendarys["rate"][0]["total_adult"] = $calendarys["rate"][0]["price_adult"];
            $calendarys["rate"][0]["total_adult_base"] = $calendarys["rate"][0]["price_adult_base"];

            if ($num_child > 0) {
                if ($rate_plan_room["channel_id"] == 1) {
                    $calendarys["rate"][0]["price_child_base"] = number_format($rate["price_child"], 2, '.', '');
                    $calendarys["rate"][0]["price_child"] = number_format(addMarkup($rate["price_child"],
                        (float)$rate_plan_room["markup"]["markup"]), 2, '.', '');
                    $calendarys["rate"][0]["total_child"] = number_format($calendarys["rate"][0]["price_child"] * $num_child,
                        2, '.', '');
                    $calendarys["rate"][0]["total_child_base"] = number_format($calendarys["rate"][0]["price_child_base"] * $num_child,
                        2, '.', '');
                } else {
                    $calendarys["rate"][0]["price_child_base"] = number_format($rate["price_child"], 2, '.', '');
                    $calendarys["rate"][0]["price_child"] = number_format(addMarkup($rate["price_child"],
                        (float)$rate_plan_room["markup"]["markup"]), 2, '.', '');
                    $calendarys["rate"][0]["total_child"] = number_format($calendarys["rate"][0]["price_child"], 2, '.',
                        '');
                    $calendarys["rate"][0]["total_child_base"] = number_format($calendarys["rate"][0]["price_child_base"],
                        2, '.', '');
                }

            } else {
                $calendarys["rate"][0]["price_child_base"] = 0;
                $calendarys["rate"][0]["price_child"] = 0;
                $calendarys["rate"][0]["total_child"] = 0;
                $calendarys["rate"][0]["total_child_base"] = 0;
            }

            $calendarys["rate"][0]["price_extra_base"] = number_format($rate["price_extra"], 2, '.', '');
            $calendarys["rate"][0]["price_extra"] = number_format(addMarkup($rate["price_extra"],
                (float)$rate_plan_room["markup"]["markup"]), 2, '.', '');
            $calendarys["rate"][0]["total_extra"] = number_format($calendarys["rate"][0]["price_extra"] * $num_extras,
                2, '.', '');
            $calendarys["rate"][0]["total_extra_base"] = number_format($calendarys["rate"][0]["price_extra_base"] * $num_extras,
                2, '.', '');

            $calendarys["rate"][0]["total_amount"] = number_format($calendarys["rate"][0]["total_adult"] + $calendarys["rate"][0]["total_child"] + $calendarys["rate"][0]["total_extra"],
                2, '.', '');
            $calendarys["rate"][0]["total_amount_base"] = number_format($calendarys["rate"][0]["total_adult_base"] + $calendarys["rate"][0]["total_child_base"] + $calendarys["rate"][0]["total_extra_base"],
                2, '.', '');

            $calendarys["total_amount"] = number_format($calendarys["rate"][0]["total_adult"] + $calendarys["rate"][0]["total_child"] + $calendarys["rate"][0]["total_extra"],
                2, '.', '');
            $calendarys["total_amount_base"] = number_format($calendarys["rate"][0]["total_adult_base"] + $calendarys["rate"][0]["total_child_base"] + $calendarys["rate"][0]["total_extra_base"],
                2, '.', '');

            $rate_plan_room["calendarys"][$ind] = $calendarys;

            $rate_plan_room["total_amount"] += number_format($calendarys["rate"][0]["total_adult"] + $calendarys["rate"][0]["total_child"] + $calendarys["rate"][0]["total_extra"],
                2, '.', '');
            $rate_plan_room["total_amount_adult"] += number_format($calendarys["rate"][0]["total_adult"], 2, '.', '');
            $rate_plan_room["total_amount_child"] += number_format($calendarys["rate"][0]["total_child"], 2, '.', '');
            $rate_plan_room["total_amount_extra"] += number_format($calendarys["rate"][0]["total_extra"], 2, '.', '');

            $rate_plan_room["total_amount_base"] += number_format($calendarys["rate"][0]["total_adult_base"] + $calendarys["rate"][0]["total_child_base"] + $calendarys["rate"][0]["total_extra_base"],
                2, '.', '');
            $rate_plan_room["total_amount_adult_base"] += number_format($calendarys["rate"][0]["total_adult_base"], 2,
                '.', '');
            $rate_plan_room["total_amount_child_base"] += number_format($calendarys["rate"][0]["total_child_base"], 2,
                '.', '');
            $rate_plan_room["total_amount_extra_base"] += number_format($calendarys["rate"][0]["total_extra_base"], 2,
                '.', '');
        }

        return $rate_plan_room;
    }

    public function getQuantityPersons(
        $num_adults,
        $num_child,
        $room_occupancy,
        $rate_occupancy,
        $room_min_adults,
        $room_max_adults,
        $room_max_child,
        $ages_child = [],
        $min_age_child = 0,
        $max_age_child = 0
    )
    {
        $return = [
            "quantity_adults" => $num_adults,
            "quantity_child" => $num_child,
            "quantity_extras" => 0,
        ];

        return $return;
    }

    public function checkInventoryAvailabilityRoomInRates($reservations)
    {
        // Agrupar las reservas por hotel_id, rate_plan_room_id, date_from y date_to
        $reservationsHotels = collect($reservations)->groupBy(function ($item) {
            return $item['hotel_id'] . '*' . $item['rate_plan_room_id'] . '*' . $item['date_from'] . '*' . $item['date_to'];
        });

        $hotelsOnRequest = collect();
        $languageIso = App::getLocale();
        $language = Language::where('iso', $languageIso)->first();
        $language_id = 1;
        if ($language) {
            $language_id = $language->id;
        }

        foreach ($reservationsHotels as $groupKey => $reservationsGroup) {
            // Extraer detalles del grupo
            [$hotelId, $ratesPlansRoomId, $dateFrom, $dateTo] = explode('*', $groupKey);
            $dateFrom = Carbon::parse($dateFrom);
            $dateTo = Carbon::parse($dateTo)->subDay(1); // Ajustar fecha final

            $ratePlanRoom = RatesPlansRooms::with(['room' => function ($query) use ($language_id) {
                $query->with(['translations' => function ($query) use ($language_id) {
                    $query->select(['object_id', 'value']);
                    $query->where('slug', 'room_name');
                    $query->where('type', 'room');
                    $query->where('language_id', $language_id);
                }]);
            }])->find($ratesPlansRoomId);

            if (!$ratePlanRoom) {
                continue; // Saltar si no se encuentra el plan de tarifa
            }

            $hotel = Hotel::find($hotelId, ['id', 'name']);
            if (!$hotel) {
                continue; // Saltar si no se encuentra el hotel
            }

            // Verificar inventario
            if ($ratePlanRoom->bag == 1) {
                $this->processInventoryBag($reservationsGroup, $ratePlanRoom, $hotel, $hotelsOnRequest, $dateFrom, $dateTo);
            } else {
                $this->processInventoryWithoutBag($reservationsGroup, $ratePlanRoom, $hotel, $hotelsOnRequest, $dateFrom, $dateTo);
            }
        }

        return $hotelsOnRequest;
    }

    /* ALEX_QUISPE */

    /**
     * Valida inventario SOLO para el item recibido.
     * Internamente usa la misma lógica que checkInventoryAvailabilityRoomInRates,
     * pero restringida al groupKey del item.
     *
     * @param array $reservationItem   Un elemento de $reservations
     * @param array $contextReservations Opcional: lista para tomar el "grupo" completo (si hay varios iguales)
     * @param array &$cache            Cache por key para evitar repetir consultas
     * @return \Illuminate\Support\Collection  Errores (misma forma que checkInventoryAvailabilityRoomInRates)
     */
    public function checkInventoryAvailabilityRoomInRatesForItem(
        array $reservationItem,
        array $contextReservations = [],
        array &$cache = []
    ) {
        $hotelsOnRequest = collect();

        // Key igual al groupBy actual
        $groupKey = ($reservationItem['hotel_id'] ?? '') . '*'
            . ($reservationItem['rate_plan_room_id'] ?? '') . '*'
            . ($reservationItem['date_from'] ?? '') . '*'
            . ($reservationItem['date_to'] ?? '');

        // Si ya validamos esta combinación, devolvemos lo cacheado
        if (array_key_exists($groupKey, $cache)) {
            return $cache[$groupKey];
        }

        // Construir el "grupo" a validar:
        // - si nos pasan el contexto completo, filtramos solo los iguales
        // - si no, validamos solo el item
        $reservationsGroup = !empty($contextReservations)
            ? collect($contextReservations)->filter(function ($r) use ($reservationItem) {
                return ($r['hotel_id'] ?? null) == ($reservationItem['hotel_id'] ?? null)
                    && ($r['rate_plan_room_id'] ?? null) == ($reservationItem['rate_plan_room_id'] ?? null)
                    && ($r['date_from'] ?? null) == ($reservationItem['date_from'] ?? null)
                    && ($r['date_to'] ?? null) == ($reservationItem['date_to'] ?? null);
            })->values()
            : collect([$reservationItem]);

        // Locale / language (igual que tu función original)
        $languageIso = App::getLocale();
        $language = Language::where('iso', $languageIso)->first();
        $language_id = $language ? $language->id : 1;

        // Extraer datos del grupo
        [$hotelId, $ratesPlansRoomId, $dateFrom, $dateTo] = explode('*', $groupKey);

        $dateFrom = Carbon::parse($dateFrom);
        $dateTo = Carbon::parse($dateTo)->subDay(1);

        $ratePlanRoom = RatesPlansRooms::with(['room' => function ($query) use ($language_id) {
            $query->with(['translations' => function ($query) use ($language_id) {
                $query->select(['object_id', 'value']);
                $query->where('slug', 'room_name');
                $query->where('type', 'room');
                $query->where('language_id', $language_id);
            }]);
        }])->find($ratesPlansRoomId);

        if (!$ratePlanRoom) {
            // Cache: sin errores pero también sin hotel/room (para no repetir esta consulta)
            return $cache[$groupKey] = $hotelsOnRequest;
        }

        $hotel = Hotel::find($hotelId, ['id', 'name']);
        if (!$hotel) {
            return $cache[$groupKey] = $hotelsOnRequest;
        }


        // Validar inventario según tipo (bag o no bag) y retornar errores (en vez de modificar $hotelsOnRequest directamente)
        if ((int)$ratePlanRoom->bag === 1) {
            $errors = $this->processInventoryBagForItem($reservationsGroup, $ratePlanRoom, $hotel, $dateFrom, $dateTo);
        } else {
            $errors = $this->processInventoryWithoutBagForItem($reservationsGroup, $ratePlanRoom, $hotel, $dateFrom, $dateTo);
        }

        // Cachear el resultado para este groupKey
        return $cache[$groupKey] = $errors;
    }

    // Procesar inventario dentro de una bolsa (POR ITEM / KEY)
    // Retorna errores (Collection) en vez de depender de $hotelsOnRequest externo.
    private function processInventoryBagForItem($reservationsGroup, $ratePlanRoom, $hotel, $dateFrom, $dateTo)
    {
        $errors = collect();

        $bagRate = \App\BagRate::where('rate_plan_rooms_id', $ratePlanRoom->id)->first();
        if (!$bagRate) {
            return $errors;
        }

        $inventoryBag = \App\InventoryBag::where('bag_room_id', $bagRate->bag_room_id)
            ->where('date', '>=', $dateFrom)
            ->where('date', '<=', $dateTo)
            ->orderBy('date')
            ->get();

        // Reutiliza tu lógica de prod (sin tocarla)
        $this->evaluateInventory($reservationsGroup, $inventoryBag, $hotel, $errors, $dateFrom, $dateTo, $ratePlanRoom->room);

        return $errors;
    }

    // Procesar inventario fuera de una bolsa (POR ITEM / KEY)
    // Retorna errores (Collection) en vez de depender de $hotelsOnRequest externo.
    private function processInventoryWithoutBagForItem($reservationsGroup, $ratePlanRoom, $hotel, $dateFrom, $dateTo)
    {
        $errors = collect();

        $inventoryWithoutBag = Inventory::where('rate_plan_rooms_id', $ratePlanRoom->id)
            ->where('date', '>=', $dateFrom)
            ->where('date', '<=', $dateTo)
            ->orderBy('date')
            ->get();

        // Reutiliza tu lógica de prod (sin tocarla)
        $this->evaluateInventory($reservationsGroup, $inventoryWithoutBag, $hotel, $errors, $dateFrom, $dateTo, $ratePlanRoom->room);

        return $errors;
    }
    
    /* ALEX_QUISPE */

    // Procesar inventario dentro de una bolsa
    private function processInventoryBag($reservationsGroup, $ratePlanRoom, $hotel, $hotelsOnRequest, $dateFrom, $dateTo)
    {
        $bagRate = \App\BagRate::where('rate_plan_rooms_id', $ratePlanRoom->id)->first();
        if (!$bagRate) {
            return;
        }

        $inventoryBag = \App\InventoryBag::where('bag_room_id', $bagRate->bag_room_id)
            ->where('date', '>=', $dateFrom)
            ->where('date', '<=', $dateTo)
            ->orderBy('date')
            ->get();

        $this->evaluateInventory($reservationsGroup, $inventoryBag, $hotel, $hotelsOnRequest, $dateFrom, $dateTo, $ratePlanRoom->room);
    }

    // Procesar inventario fuera de una bolsa
    private function processInventoryWithoutBag($reservationsGroup, $ratePlanRoom, $hotel, $hotelsOnRequest, $dateFrom, $dateTo)
    {
        $inventoryWithoutBag = Inventory::where('rate_plan_rooms_id', $ratePlanRoom->id)
            ->where('date', '>=', $dateFrom)
            ->where('date', '<=', $dateTo)
            ->orderBy('date')
            ->get();

        $this->evaluateInventory($reservationsGroup, $inventoryWithoutBag, $hotel, $hotelsOnRequest, $dateFrom, $dateTo, $ratePlanRoom->room);
    }

    // Evaluar el inventario y agregar al resultado
    private function evaluateInventory($reservationsGroup, $inventoryCollection, $hotel, $hotelsOnRequest, $dateFrom, $dateTo, $room)
    {
        if ($inventoryCollection->isEmpty()) {
            $roomName = $room->translations[0]['value'];
            $dateFrom = $dateFrom->format('Y-m-d');
            $dateTo = $dateTo->format('Y-m-d');
            $hotelsOnRequest->push([
                trans('validations.reservations.hotel_room_days_has_no_inventory',
                    ['hotel_name' => $hotel->name, 'room_name' => $roomName, 'date_from' => $dateFrom, 'date_to' => $dateTo]),
            ]);
            return;
        }

        $inventoryNotAvailable = collect();
        foreach ($inventoryCollection as $inventory) {
            if ($inventory->inventory_num < $reservationsGroup->count() || $inventory->locked == 1) {
                $days = Carbon::parse($inventory->date)->format('d');
                $inventoryNotAvailable->push($days);
            }
        }

        if ($inventoryNotAvailable->isNotEmpty()) {
            $days = implode(', ', $inventoryNotAvailable->toArray());
            $roomName = $room->translations[0]['value'];
            $hotelsOnRequest->push(trans('validations.reservations.hotel_room_days_availability', ['hotel_name' => $hotel->name, 'room_name' => $roomName, 'days' => $days]));
        }
    }
}

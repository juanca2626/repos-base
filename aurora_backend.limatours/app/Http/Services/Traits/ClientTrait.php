<?php


namespace App\Http\Services\Traits;

use App\Client;
use App\ClientExecutive;
use App\ClientRatePlan;
use App\Galery;
use App\Hotel;
use App\HotelClient;
use App\HotelFavoriteUser;
use App\Markup;
use App\Package;
use App\PackageDestination;
use App\PoliciesCancelations;
use App\RatePlanRoomDateRange;
use App\RatesPlans;
use App\RatesPlansCalendarys;
use App\RatesPlansRooms;
use App\Room;
use App\Service;
use App\ServiceDestination;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

trait ClientTrait
{

    /** @var Client */
    public $_client;
    public $_has_client_ecommerce;

    protected $type_pax = [
        1 => 'simple',
        2 => 'double',
        3 => 'triple',
    ];

    public function setClientReadonly($client_id)
    {
        $this->_client = Client::on('mysql_read')->find($client_id);
    }

    public function setClient($client_id)
    {
        $this->_client = Client::with([
            'configuration' => function ($query) {
                $query->select([
                    'id',
                    'client_id',
                    'hotel_allowed_on_request',
                    'service_allowed_on_request'
                ]);
            }
        ])->where('id', '=', $client_id)->first();
    }

    /**
     * @return Client
     */
    public function client()
    {
        return $this->_client;
    }

    /**
     * @return Client
     */
    public function client_id()
    {
        return !empty($this->client()) ? $this->client()->id : null;
    }

    /**
     * esta funcion retorna un arreglo de ids de hoteles dado un periodo y un client_id
     *
     * @param $client_id
     * @param null $period
     * @return mixed
     */
    public function getClientHotels($client_id, $period, $hotel_id = null, $regionIds)
    {
        $hotels_client = Hotel::select('id')
            // Excluir los hoteles que no se quieren trabajar
            ->whereDoesntHave('hotelClients', function ($query) use ($client_id, $period, $regionIds) {
                $query->where('client_id', $client_id);
                $query->where('period', $period);
                $query->whereIn('business_region_id', $regionIds);
            })
            ->when(!empty($hotel_id), function ($query) use ($hotel_id) {
                if (is_array($hotel_id)) {
                    return $query->whereIn('id', $hotel_id);
                } else {
                    return $query->where('id', '=', $hotel_id);
                }
            })
            ->select('id', 'name', 'country_id', 'state_id', 'city_id', 'district_id')
            ->where('status', 1)
            ->with([
                'country' => function ($query) {
                    $query->select('id', 'iso');

                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'country');
                            $query->where('language_id', 1);
                        },
                    ]);
                },
            ])
            ->with([
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
            ])
            ->with([
                'city' => function ($query) {
                    $query->select('id');

                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'city');
                            $query->where('language_id', 1);
                        },
                    ]);
                },
            ])
            ->with([
                'district' => function ($query) {
                    $query->select('id');

                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'district');
                            $query->where('language_id', 1);
                        },
                    ]);
                },
            ])->get();

        $hotels_client->transform(function ($query) use ($client_id, $period) {
            return collect([
                "client_id" => $client_id,
                "period" => $period,
                "hotel" => $query,
            ]);
        });

        return $hotels_client;
    }

    public function getQuantityPersonsRoom(array $rooms, bool $allow_children = false): Collection
    {
        $rooms = collect($rooms);
        $total_people_adults = $rooms->sum('adults');
        $total_people_child = $rooms->sum('child');

        $children_ages = collect();
        // logica que verifica si en la búsqueda tienen niños
        foreach ($rooms as $room) {
            if ($room["child"] > 0) {
                foreach ($room["ages_child"] as $child) {
                    $children_ages->push((int)$child["age"]);
                }
            }
        }

        // estas dos variables se usan si en la búsqueda se necesitan niños child_min_age se usa para
        // identificar la edad minima de los niños de la búsqueda y accept_child se usa para verificar si el hotel acepta niños
        $accept_child = $rooms->count() ? !!$children_ages->count() : $allow_children;
        $child_min_age = $children_ages->count() ? $children_ages->min() : 0;
        $child_max_age = $children_ages->count() ? $children_ages->max() : 0;

        return collect([
            'total_people_adults' => $total_people_adults,
            'total_people_child' => $total_people_child,
            'total_people_search' => $total_people_adults + $total_people_child,
            'accept_child' => $accept_child,
            'children_ages' => $children_ages,
            'child_min_age' => $child_min_age,
            'child_max_age' => $child_max_age,
        ]);
    }

    /**
     * esta funcion retorna un arreglo de ids de hoteles dado un periodo y un client_id
     *
     * @param $country_iso
     * @param null $state_iso
     * @param null $city_id
     * @param null $district_id
     * @param null $typeclass_id
     * @param null $period
     * @param int $child_min_age_search
     * @return mixed
     */
    public function getClientHotelsIdsReadonly(
        $country_iso,
        $state_iso = null,
        $city_id = null,
        $district_id = null,
        $typeclass_id = null,
        $period = null,
        $hotels_id = [],
        $hotels_search_code = null,
        bool $accept_child = false,
        bool $preferential = false
    )
    {
        $hotel_clients = HotelClient::on('mysql_read')
            ->where('hotel_clients.client_id', $this->client_id())
            ->where('hotel_clients.period', $period)
            ->whereNull('deleted_at');

        $select = Hotel::on('mysql_read')
            ->leftJoin('channel_hotel', 'hotels.id', '=', 'channel_hotel.hotel_id')
            ->leftJoin('countries', 'hotels.country_id', '=', 'countries.id')
            ->leftJoin('states', 'hotels.state_id', '=', 'states.id')
            ->leftJoinSub($hotel_clients, 'hotel_clients', function ($join) {
                $join->on('hotels.id', '=', 'hotel_clients.hotel_id');
            })
            ->whereNull('hotel_clients.hotel_id')
            ->select('hotels.id')
            ->when(!empty($typeclass_id), function ($query) use ($period, $typeclass_id) {

                return $query->join('hotel_type_classes', function ($query2) use ($period, $typeclass_id) {
                    $query2->on('hotels.id', '=', 'hotel_type_classes.hotel_id');
                    $query2->where('hotel_type_classes.typeclass_id', $typeclass_id);
                    $query2->where('hotel_type_classes.year', $period);

                });
            })
            ->where('status', 1)
            ->where('channel_hotel.channel_id', 1)
            ->when($hotels_search_code <> '', function ($query) use ($hotels_search_code) {
                return $query->where(DB::raw("CONCAT(hotels.name,channel_hotel.code)"), 'like',
                    '%' . $hotels_search_code . '%');
            })
            ->when(count($hotels_id) > 0, function ($query) use ($hotels_id) {
                return $query->whereIn('hotels.id', $hotels_id);
            })
            // si se buscan niños se excluyen los hoteles que no admintan niños
            ->when($accept_child, function ($query) {
                return $query->where(function ($query) {
                    $query->where('allows_child', 1);
                    $query->orWhere('allows_teenagers', 1);
                });
            })
            // filtrar destino
            ->when(!empty($country_iso), function ($query) use ($country_iso) {
                return $query->where('countries.iso', $country_iso);
            })
            ->when(!empty($state_iso), function ($query) use ($state_iso) {
                return $query->where('states.iso', $state_iso);
            })
            ->when(!empty($city_id), function ($query) use ($city_id) {
                return $query->where('city_id', $city_id);
            })
            ->when(!empty($district_id), function ($query) use ($district_id) {
                return $query->where('district_id', $district_id);
            })
            // ->when(!empty($typeclass_id), function ($query) use ($typeclass_id) {
            //     return $query->where('typeclass_id', $typeclass_id);
            // })
            ->when($preferential, function ($query) {
                return $query->where('preferential', '>', 0);
            });

        $result = $select->pluck('id')->toArray();

        // print_r($result);
        // die('..');

        return $result;
    }

    public function getClientHotelsIds(
        $country_iso,
        $state_iso = null,
        $city_id = null,
        $district_id = null,
        $typeclass_id = null,
        $period = null,
        $hotels_id = [],
        $hotels_search_code = null,
        bool $accept_child = false,
        bool $preferential = false
    )
    {

        $hotel_clients = DB::table('hotel_clients')
            ->where('hotel_clients.client_id', $this->client_id())
            ->where('hotel_clients.period', $period)
            ->whereNull('deleted_at');

        $select = DB::table('hotels')
            ->leftJoin('channel_hotel', 'hotels.id', '=', 'channel_hotel.hotel_id')
            ->leftJoin('countries', 'hotels.country_id', '=', 'countries.id')
            ->leftJoin('states', 'hotels.state_id', '=', 'states.id')
            ->leftJoinSub($hotel_clients, 'hotel_clients', function ($join) {
                $join->on('hotels.id', '=', 'hotel_clients.hotel_id');
            })
            ->whereNull('hotel_clients.hotel_id')
            ->select('hotels.id')
            ->when(!empty($typeclass_id), function ($query) use ($period, $typeclass_id) {

                return $query->join('hotel_type_classes', function ($query2) use ($period, $typeclass_id) {
                    $query2->on('hotels.id', '=', 'hotel_type_classes.hotel_id');
                    $query2->where('hotel_type_classes.typeclass_id', $typeclass_id);
                    $query2->where('hotel_type_classes.year', $period);

                });
            })
            ->where('status', 1)
            ->where('channel_hotel.channel_id', 1)
            ->when((count($hotels_id) == 0 and $hotels_search_code <> ''), function ($query) use ($hotels_search_code) {
                return $query->where(DB::raw("CONCAT(hotels.name,channel_hotel.code)"), 'like',
                    '%' . $hotels_search_code . '%');
            })
            ->when(count($hotels_id) > 0, function ($query) use ($hotels_id) {
                return $query->whereIn('hotels.id', $hotels_id);
            })
            // si se buscan niños se excluyen los hoteles que no admintan niños
            ->when($accept_child, function ($query) {
                return $query->where(function ($query) {
                    $query->where('allows_child', 1);
                    $query->orWhere('allows_teenagers', 1);
                });
            })
            // filtrar destino
            ->when(!empty($country_iso), function ($query) use ($country_iso) {
                return $query->where('countries.iso', $country_iso);
            })
            ->when(!empty($state_iso), function ($query) use ($state_iso) {
                return $query->where('states.iso', $state_iso);
            })
            ->when(!empty($city_id), function ($query) use ($city_id) {
                return $query->where('city_id', $city_id);
            })
            ->when(!empty($district_id), function ($query) use ($district_id) {
                return $query->where('district_id', $district_id);
            })
            ->when($preferential, function ($query) use ($period) {
                return $query
                    ->leftJoin('hotel_preferentials', function ($join) use ($period) {
                        $join->on('hotels.id', '=', 'hotel_preferentials.hotel_id')
                            ->where('hotel_preferentials.year', $period);
                    })
                    ->leftJoin('hotel_backups', function ($join) use ($period) {
                        $join->on('hotels.id', '=', 'hotel_backups.hotel_id')
                            ->where('hotel_backups.year', $period);
                    })
                    ->where(function ($q) {
                        $q->where('hotel_preferentials.value', 1)
                        ->orWhere(function ($q2) {
                            $q2->where(function ($inner) {
                                $inner->whereNull('hotel_preferentials.value')
                                        ->orWhere('hotel_preferentials.value', '!=', 1);
                            })->where('hotel_backups.value', 1);
                        });
                    });
            })

            // ->when(!empty($typeclass_id), function ($query) use ($typeclass_id) {
            //     return $query->where('typeclass_id', $typeclass_id);
            // })
        ;

        $result = $select->pluck('id')->toArray();

        return $result;
    }

    public function getClientHotelsIdsBK(
        $country_iso,
        $state_iso = null,
        $city_id = null,
        $district_id = null,
        $typeclass_id = null,
        $period = null,
        $hotels_id = [],
        $hotels_search_code = null,
        int $child_min_age_search = null
    )
    {
        $select = Hotel::select('id')
            // Excluir los hoteles que no se quieren trabajar
            ->whereDoesntHave('hotelClients', function ($query) use ($period) {
                $query->where('client_id', $this->client_id());
                $query->where('period', $period);
            })
            ->when(count($hotels_id) > 0, function ($query) use ($hotels_id) {
                return $query->whereIn('id', $hotels_id);
            })
            ->when($hotels_search_code <> '', function ($query) use ($hotels_search_code) {
                return $query->whereHas('channel', function ($query) use ($hotels_search_code) {
                    $query->where('code', $hotels_search_code);
                });
                // return $query->where(DB::raw("CONCAT(name)"), 'like', '%'.$hotels_search_code.'%');
            })
            // excluir los hoteles inactivos
            ->where('status', 1)
            // si se buscan niños se excluyen los hoteles que no admintan niños
            ->when($child_min_age_search != null, function ($query) use ($child_min_age_search) {
                return $query->where('allows_child', 1)
                    ->where('min_age_child', '<=', $child_min_age_search);
            })
            // filtrar destino
            ->when(!empty($country_iso), function ($query) use ($country_iso) {
                return $query->whereHas('country', function ($query) use ($country_iso) {
                    $query->where('iso', $country_iso);
                });
            })
            ->when(!empty($state_iso), function ($query) use ($state_iso) {
                return $query->whereHas('state', function ($query) use ($state_iso) {
                    $query->where('iso', $state_iso);
                });
            })
            ->when(!empty($city_id), function ($query) use ($city_id) {
                return $query->where('city_id', $city_id);
            })
            ->when(!empty($district_id), function ($query) use ($district_id) {
                return $query->where('district_id', $district_id);
            })
            ->when(!empty($typeclass_id), function ($query) use ($typeclass_id) {
                return $query->where('typeclass_id', $typeclass_id);
            });

        return $select->pluck('id')->toArray();
    }

    /**
     * //Esto es para tarifas de Aurora validando tiempo de estadia
     * @param int $length_stay
     * @param $check_in
     * @param array $hotels_id
     * @return array
     */
    public function getRatesPlansRoomsIdsByLengthStayAurora(int $length_stay, $check_in, array $hotels_id = [])
    {
//        return RatesPlansRooms::where('channel_id', 1)
//            ->whereDoesntHave('rates_plan.client', function ($query) use ($period, $client_id) {
//                $query->where('client_id', $this->client_id());
//                $query->where('rate_plans', $client_id);
//            })
//            ->when(count($hotel_id) > 0, function ($query) use ($hotel_id) {
//                return $query->whereHas('rate_plan', function ($query) use ($hotel_id) {
//                    $query->whereIn('hotel_id', $hotel_id);
//                    $query->whereHas('hotel', function ($query) {
//                        $query->where('status', 1);
//                    });
//                });
//            })
//            ->whereHas('calendarys', function ($query) use ($check_in, $length_stay) {
//                $query->where('date', $check_in);
//                $query->whereHas('policies_rates', function ($query) use ($length_stay) {
//                    $query->where('min_length_stay', '<=', $length_stay);
//                    $query->where('max_length_stay', '>=', $length_stay);
//                });
//            })
//            ->where('status', 1)
//            ->pluck('id')
////            ->get()
//            ->toArray();
    }

    /**
     * Esto es para tarifas que tienen allotment es decir bloqueo de dias
     * @param int $client_id
     * @param int $advancedOffsetDays
     * @param array $hotel_id
     * @param array $rate_plan_rooms_id
     * @return array
     */
    public function getRatesPlansRoomsIdsNotAllotment(
        int   $client_id,
        int   $advancedOffsetDays,
        array $hotel_id = [],
        array $rate_plan_rooms_id = []
    )
    {
//        return Allotment::select('rate_plan_rooms_id', 'num_days_blocked')
//            ->when(count($rate_plan_rooms_id) > 0, function ($query) use ($rate_plan_rooms_id) {
//                return $query->whereIn('rate_plan_rooms_id', $rate_plan_rooms_id);
//            })
//            ->when(count($hotel_id) > 0, function ($query) use ($hotel_id) {
//                return $query->whereHas('rate_plan_room.rate_plan', function ($query) use ($hotel_id) {
//                        $query->whereIn('hotel_id', $hotel_id);
////                    $query->whereHas('rate_plan', function ($query) use ($hotel_id) {
////                    });
//                });
//            })
//            ->where('num_days_blocked', '>', $advancedOffsetDays)->get()
//            ->where('client_id', $client_id)
//            ->pluck('id')->toArray();
    }


    /**
     * esta funcion retorna un arreglo de ids de hoteles dado un periodo y un client_id
     *
     * @param $client_id
     * @param $period
     * @return mixed
     */
    public function getClientHotelsList($client_id, $period, $hotel_id, $language_id)
    {

        /** @var Collection $hotels_client */
        $hotels_client = Hotel::select('id', 'name', 'country_id', 'state_id', 'city_id', 'district_id', 'zone_id',
            'hotel_type_id', 'typeclass_id', 'chain_id', 'latitude', 'longitude', 'stars', 'check_in_time',
            'check_out_time', 'preferential', 'min_age_child', 'max_age_child')
//            // Excluir los hoteles que no se quieren trabajar
            ->whereDoesntHave('hotelClients', function ($query) use ($period, $client_id) {
                //$query->where('client_id', $this->client_id());
                //$query->where('period', $client_id);
                $query->where('client_id', $client_id);
                $query->where('period', $period);
            })
            ->where('status', 1)
            // filtrar destino
            ->whereHas('rooms', function ($query) {
                $query->where('state', 1);

            })
            ->with([
                'channel' => function ($query) {
                    $query->where("channel_id", 1);
                }
            ])
            ->with([
                'country' => function ($query) use ($language_id) {
                    $query->select('id', 'iso', 'local_tax', 'local_service', 'foreign_tax', 'foreign_service');

                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'country');
                            $query->where('language_id', $language_id);
                        },
                    ]);
                },
            ])
            ->with([
                'state' => function ($query) use ($language_id) {
                    $query->select('id', 'iso');

                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'state');
                            $query->where('language_id', $language_id);
                        },
                    ]);
                },
            ])
            ->with([
                'city' => function ($query) use ($language_id) {
                    $query->select('id');

                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'city');
                            $query->where('language_id', $language_id);
                        },
                    ]);
                },
            ])
            ->with([
                'district' => function ($query) use ($language_id) {
                    $query->select('id');

                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'district');
                            $query->where('language_id', $language_id);
                        },
                    ]);
                },
            ])
            ->with([
                'zone' => function ($query) use ($language_id) {
                    $query->select('id');
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'zone');
                            $query->where('language_id', $language_id);
                        },
                    ]);
                },
            ])
            ->with([
                'taxes' => function ($query) {
                    $query->where('status', '1');
                },
            ])
            ->with([
                'translations' => function ($query) use ($language_id) {
                    $query->select('object_id', 'value', 'slug');
                    $query->where('type', 'hotel');
                    $query->where('language_id', $language_id);
                },
            ])
            ->with([
                'galeries' => function ($query) {
                    $query->select('object_id', 'slug', 'url');
                    $query->where('type', 'hotel');
                    $query->where('state', 1);
                },
            ])
            ->with([
                'channels' => function ($query) {
                    $query->wherePivot('state', '=', 1);
                    $query->wherePivot('code', '!=', '');
                    $query->wherePivot('code', '!=', 'null');
                },
            ])
            ->with([
                'amenity' => function ($query) use ($language_id) {
                    $query->where('status', 1);

                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'amenity');
                            $query->where('language_id', $language_id);
                        },
                    ]);
                    $query->with([
                        'galeries' => function ($query) {
                            $query->select('object_id', 'url');
                            $query->where('type', 'amenity');
                        },
                    ]);
                },
            ])
            ->with([
                'hoteltype' => function ($query) use ($language_id) {
                    $query->select('id');
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'hoteltype');
                            $query->where('language_id', $language_id);
                        },
                    ]);
                },
            ])
            ->with('chain')
            // ->with([
            //     'typeclass' => function ($query) use ($language_id) {
            //         $query->with([
            //             'translations' => function ($query) use ($language_id) {
            //                 $query->select('object_id', 'value');
            //                 $query->where('type', 'typeclass');
            //                 $query->where('language_id', $language_id);
            //             },
            //         ]);
            //     },
            // ])
            ->with([
                'hoteltypeclass' => function ($query) use ($period, $language_id) {
                    $query->where('year', $period);
                    $query->with([
                        'typeclass.translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'typeclass');
                            $query->where('language_id', $language_id);
                        },
                    ]);
                },
            ])
            ->with([
                'hotelpreferentials' => function ($query) use ($period) {
                    $query->where('year', $period);
                },
            ])
            ->with([
                'markup' => function ($query) use ($period, $client_id) {
                    $query->where('client_id', $client_id);
                    $query->where('period', '>=', $period);
                },
            ])
            ->with([
                'rooms' => function ($query) use (
                    $language_id,
                    $period,
                    $client_id
                ) {
                    $query->select('id', 'hotel_id', 'room_type_id', 'max_capacity', 'min_adults', 'max_adults',
                        'max_child', 'max_infants');

                    $query->where('state', 1);

                    $query->with([
                        'galeries' => function ($query) {
                            $query->select('object_id', 'url');
                            $query->where('type', 'room');
                            $query->where('state', 1);
                        },
                    ]);

                    $query->with([
                        'channels' => function ($query) {
                            $query->wherePivot('state', '=', 1);
                            $query->wherePivot('code', '!=', '');
                            $query->wherePivot('code', '!=', 'null');
                        },
                    ]);

                    $query->with([
                        'room_type' => function ($query) use ($language_id) {
                            $query->select('id', 'occupation');
                            $query->with([
                                'translations' => function ($query) use ($language_id) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'roomtype');
                                    $query->where('language_id', $language_id);
                                },
                            ]);
                        },
                    ]);

                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value', 'slug');
                            $query->where('type', 'room');
                            $query->where('language_id', $language_id);
                        },
                    ]);
                },
            ]);


        $hotels_client = $hotels_client->get();


        return $hotels_client;
    }

    public function getHotelTypeClass($hoteltypeclass, $language_id = 1)
    {
        $hoteltypeclass = collect($hoteltypeclass)->map(function ($obj, $key) use ($language_id){
            $key_language = array_search($language_id, array_column($obj['typeclass']['translations'],'language_id'));
            return [
                'id' => $obj['typeclass']['id'],
                'name' => $obj['typeclass']['translations'][$key_language]['value'] ?? 'NoClassType',
                'color' => $obj['typeclass']['color'],
                'order' => $obj['typeclass']['order']
            ];
        })->sortByDesc('order')->first();

        if ($hoteltypeclass === null) {
            return [
                'id' => '',
                'name' => 'NoClassType',
                'color' => '#BD0D12',
                'order' => 1
            ];
        }

        return $hoteltypeclass;
    }

    /**
     * esta funcion retorna un arreglo de ids de hoteles dado un periodo y un client_id
     *
     * @param $client_id
     * @param $hotels_client_hotel_id_list
     * @param $date_from
     * @param $date_to
     * @param $from
     * @param $to
     * @param $reservation_days
     * @param $rate_plan_room_ids_include
     * @param $country_iso
     * @param null $state_iso
     * @param null $city_id
     * @param null $district_id
     * @param null $typeclass_id
     * @param array $hotels_id
     * @return mixed
     */
    public function getClientHotelsAvailReadonly(
        $client_id,
        $period,
        array $hotels_client_hotel_id_list,
        $date_from,
        $date_to,
        $from,
        $to,
        $reservation_days,
        $rate_plan_room_ids_include,
        $country_iso,
        $state_iso,
        $city_id,
        $district_id,
        $typeclass_id,
        $hotels_id,
        $language_id
    )
    {
        /** @var Collection $hotels_client */
        $hotels_client = Hotel::on('mysql_read')->select('id', 'name', 'country_id', 'state_id', 'city_id',
            'district_id', 'zone_id',
            'hotel_type_id', 'typeclass_id', 'chain_id', 'latitude', 'longitude', 'stars', 'check_in_time',
            'check_out_time', 'preferential', 'min_age_child', 'max_age_child', 'allows_child', 'allows_teenagers',
            'min_age_teenagers', 'max_age_teenagers', 'notes', 'flag_new', 'date_end_flag_new')
//            // Excluir los hoteles que no se quieren trabajar
            ->whereDoesntHave('hotelClients', function ($query) use ($period, $client_id) {
                $query->where('client_id', $this->client_id());
                $query->where('period', $client_id);
            })
//            ->whereDoesntHave('rates_plans.client', function ($query) use ($period, $client_id) {
//                $query->where('client_id', $this->client_id());
//                $query->where('rate_plans', $client_id);
//            })
            ->where('status', 1)
            // filtrar hoteles
            ->when(!empty(count($hotels_client_hotel_id_list) > 0),
                function ($query) use ($hotels_client_hotel_id_list) {
                    return $query->whereIn('id', $hotels_client_hotel_id_list);
                })
            // filtrar destino
            ->when(!empty($country_iso), function ($query) use ($country_iso) {
                return $query->whereHas('country', function ($query) use ($country_iso) {
                    $query->where('iso', $country_iso);
                });
            })
            ->when(!empty($state_iso), function ($query) use ($state_iso) {
                return $query->whereHas('state', function ($query) use ($state_iso) {
                    $query->where('iso', $state_iso);
                });
            })
            ->when(!empty($city_id), function ($query) use ($city_id) {
                return $query->where('city_id', $city_id);
            })
            ->when(!empty($district_id), function ($query) use ($district_id) {
                return $query->where('district_id', $district_id);
            })
            // ->when(!empty($typeclass_id), function ($query) use ($typeclass_id) {
            //     return $query->where('typeclass_id', $typeclass_id);
            // })
            ->whereHas('rooms', function ($query) use ($from, $to, $reservation_days, $rate_plan_room_ids_include) {
                $query->where('state', 1);
                $query->whereHas('rates_plan_room', function ($query) use (
                    $from,
                    $to,
                    $reservation_days,
                    $rate_plan_room_ids_include
                ) {
                    $query->whereIn('id', $rate_plan_room_ids_include);
                    $query->where('status', 1);//lista de ids a incluir
                });
            })
            ->when(!empty($typeclass_id), function ($query) use ($from, $typeclass_id) {
                return $query->whereHas('hoteltypeclass', function ($query) use ($from, $typeclass_id) {
                    $query->where('typeclass_id', $typeclass_id);
                    $query->where('year', Carbon::parse($from)->year);
                });
            })
            ->with([
                'country' => function ($query) {
                    $query->select('id', 'iso', 'local_tax', 'local_service', 'foreign_tax', 'foreign_service');

                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'country');
                            $query->where('language_id', 1);
                        },
                    ]);
                },
            ])
            ->with([
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
            ])
            ->with([
                'city' => function ($query) {
                    $query->select('id');

                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'city');
                            $query->where('language_id', 1);
                        },
                    ]);
                },
            ])
            ->with([
                'district' => function ($query) {
                    $query->select('id');

                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'district');
                            $query->where('language_id', 1);
                        },
                    ]);
                },
            ])
            ->with([
                'zone' => function ($query) use ($language_id) {
                    $query->select('id');
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'zone');
                            $query->where('language_id', $language_id);
                        },
                    ]);
                },
            ])
            ->with([
                'taxes' => function ($query) {
                    $query->where('status', '1');
                },
            ])
            ->with([
                'translations' => function ($query) use ($language_id) {
                    $query->select('object_id', 'value', 'slug');
                    $query->where('type', 'hotel');
                    $query->where('language_id', $language_id);
                },
            ])
            ->with([
                'galeries' => function ($query) {
                    $query->select('object_id', 'slug', 'url');
                    $query->where('type', 'hotel');
                    $query->where('state', 1);
                },
            ])
            ->with([
                'channels' => function ($query) {
                    $query->wherePivot('state', '=', 1);
                    $query->wherePivot('code', '!=', '');
                    $query->wherePivot('code', '!=', 'null');
                },
            ])
            ->with([
                'amenity' => function ($query) use ($language_id) {
                    $query->where('status', 1);

                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'amenity');
                            $query->where('language_id', $language_id);
                        },
                    ]);
                    $query->with([
                        'galeries' => function ($query) {
                            $query->select('object_id', 'url');
                            $query->where('type', 'amenity');
                        },
                    ]);
                },
            ])
            ->with([
                'hoteltype' => function ($query) use ($language_id) {
                    $query->select('id');
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'hoteltype');
                            $query->where('language_id', $language_id);
                        },
                    ]);
                },
            ])
            ->with('chain')
            // ->with([
            //     'typeclass' => function ($query) use ($language_id) {
            //         $query->with([
            //             'translations' => function ($query) use ($language_id) {
            //                 $query->select('object_id', 'value');
            //                 $query->where('type', 'typeclass');
            //                 $query->where('language_id', $language_id);
            //             },
            //         ]);
            //     },
            // ])
            ->with([
                'hoteltypeclass' => function ($query) use ($from, $typeclass_id, $language_id) {
                    $query->where('year', Carbon::parse($from)->year);
                    $query->when(!empty($typeclass_id), function ($query) use ($typeclass_id) {
                        return $query->where('typeclass_id', $typeclass_id);
                    });

                    $query->with([
                        'typeclass.translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'typeclass');
                            $query->where('language_id', $language_id);
                        },
                    ]);
                },
            ])
            ->with([
                'hotelpreferentials' => function ($query) use ($from) {
                    $query->where('year', Carbon::parse($from)->year);
                },
            ])
            ->with([
                'markup' => function ($query) use ($period, $client_id) {
                    $query->where('client_id', $client_id);
                    $query->where('period', '>=', $period);
                },
            ])
            ->whereHas('rooms', function ($query) use ($from, $reservation_days) {
                $query->where('state', 1);

                $query->whereHas('rates_plan_room', function ($query) use (
                    $from,
                    $reservation_days
                ) {
                    $query->whereHas('calendarys', function ($query) use (
                        $from,
                        $reservation_days
                    ) {
                        $query->where('date', '=', $from);

                        $query->whereHas('policies_rates', function ($query) use (
                            $from,
                            $reservation_days
                        ) {
                            $query->where('min_length_stay', '<=', $reservation_days);
                            $query->where('max_length_stay', '>=', $reservation_days);
                        });
                    });
                });
            })
            ->with([
                'rooms' => function ($query) use (
                    $from,
                    $to,
                    $reservation_days,
                    $rate_plan_room_ids_include,
                    $period,
                    $client_id,
                    $language_id
                ) {
                    $query->select('id', 'hotel_id', 'room_type_id', 'max_capacity', 'min_adults', 'inventory',
                        'max_adults', 'max_child', 'max_infants', 'bed_additional');
                    $query->where('state', 1);

                    $query->with([
                        'galeries' => function ($query) {
                            $query->select('object_id', 'url');
                            $query->where('type', 'room');
                            $query->where('state', 1);
                        },
                    ]);

                    $query->with([
                        'channels' => function ($query) {
                            $query->wherePivot('state', '=', 1);
                            $query->wherePivot('code', '!=', '');
                            $query->wherePivot('code', '!=', 'null');
                        },
                    ]);

                    $query->with([
                        'room_type' => function ($query) use ($language_id) {
                            $query->select('id', 'occupation');
                            $query->with([
                                'translations' => function ($query) use ($language_id) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'roomtype');
                                    $query->where('language_id', $language_id);
                                },
                            ]);
                        },
                    ]);

                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value', 'slug');
                            $query->where('type', 'room');
                            $query->where('language_id', $language_id);
                        },
                    ]);

                    $query->whereHas('rates_plan_room', function ($query) use (
                        $from,
                        $to,
                        $reservation_days,
                        $rate_plan_room_ids_include
                    ) {
                        $query->where('status', 1);
                        $query->whereIn('id', $rate_plan_room_ids_include);//lista de ids a incluir
                    });

                    $query->with([
                        'rates_plan_room' => function ($rates_plan_room) use (
                            $from,
                            $to,
                            $reservation_days,
                            $rate_plan_room_ids_include,
                            $period,
                            $client_id,
                            $language_id
                        ) {
                            $rates_plan_room->whereDoesntHave('rate_plan.client',
                                function ($query) use ($period, $client_id) {
                                    $query->where('client_id', $this->client_id());
                                    $query->where('period', $period);
                                    $query->whereNull('client_rate_plans.deleted_at');  // se agrego esta linea porque en al relacion se esta trayendo todos los registros elinados con soft delete
                                });
                            $rates_plan_room->select(
                                'id',
                                'rates_plans_id',
                                'room_id',
                                'status',
                                'bag',
                                'channel_id',
                                'channel_child_price',
                                'channel_infant_price'
                            );
                            $rates_plan_room->whereIn('id', $rate_plan_room_ids_include);//lista de ids a incluir
                            $rates_plan_room->with('channel');
                            $rates_plan_room->with([
                                'policies_cancelation' => function ($query) use (
                                    $from,
                                    $to,
                                    $period,
                                    $rates_plan_room,
                                    $language_id
                                ) {
                                    $query->where('type', 'cancellations');
                                    $query->with([
                                        'policy_cancellation_parameter' => function ($query) {
                                            $query->where('min_day', '>=', 0);
                                            $query->where('max_day', '<>', 0);
                                            $query->with('penalty');
                                        },
                                    ]);
                                },
                                'descriptions' => function ($query) use ($from, $to, $period, $rates_plan_room) {

                                },
                                'calendarys' => function ($query) use (
                                    $from,
                                    $to,
                                    $period,
                                    $rates_plan_room,
                                    $language_id
                                ) {
                                    $query->where('date', '>=', $from);
                                    $query->where('date', '<=', $to);
                                    $query->with([
                                        'policies_rates' => function ($query) use ($language_id) {
                                            $query->with([
                                                'policies_cancelation' => function ($query) {
//                                                    $query->with('policy_cancellation_parameter');
                                                    $query->where('type', 'cancellations');
                                                    $query->with([
                                                        'policy_cancellation_parameter' => function ($query) {
                                                            $query->where('min_day', '>=', 0);
                                                            $query->where('max_day', '<>', 0);
                                                            $query->with('penalty');
                                                        },
                                                    ]);
                                                },
                                            ]);
                                            $query->with([
                                                'translations' => function ($query) use ($language_id) {
                                                    $query->where('type', 'rate_policies');
                                                    $query->where('language_id', $language_id);
                                                },
                                            ]);
                                        },
                                        'policies_cancelation' => function ($query) {
                                            $query->where('type', 'cancellations');
                                            $query->with([
                                                'policy_cancellation_parameter' => function ($query) {
                                                    $query->where('min_day', '>=', 0);
                                                    $query->where('max_day', '<>', 0);
                                                    $query->with('penalty');
                                                },
                                            ]);
                                        },
                                    ]);
                                    $query->with('rate');
                                },
                            ]);
                            $rates_plan_room->with([
                                'rate_plan' => function ($query) use ($period, $language_id, $client_id) {
                                    $query->with([
                                        'translations' => function ($query) use ($language_id) {
                                            $query->where('language_id', $language_id);
                                        }
                                    ]);
                                    $query->with([
                                        'translations_no_show' => function ($query) use ($language_id) {
                                            $query->where('language_id', $language_id);
                                        }
                                    ]);
                                    $query->with([
                                        'translations_day_use' => function ($query) use ($language_id) {
                                            $query->where('language_id', $language_id);
                                        }
                                    ]);
                                    $query->with([
                                        'translations_notes' => function ($query) use ($language_id) {
                                            $query->where('language_id', $language_id);
                                        }
                                    ]);
                                    $query->with([
                                        'meal.translations',
                                        'markup' => function ($query) use ($period, $client_id) {
                                            $query->where('client_id', $client_id);
                                            $query->where('period', '>=', $period);
                                        },
                                    ]);
                                    $query->with('promotionsData');
                                    $query->where('status', 1);
                                },
                            ]);
                            $rates_plan_room->with([
                                'inventories' => function ($query) use ($from, $to) {
                                    $query->where('date', '>=', $from);
                                    $query->where('date', '<=', $to);
                                },
                            ]);

                            $rates_plan_room->with([
                                'bag_rate.bag_room.inventory_bags' => function ($query) use ($from, $to) {
                                    $query->where('date', '>=', $from);
                                    $query->where('date', '<=', $to);
                                },
                            ]);

                            $rates_plan_room->with([
                                'markup' => function ($query) use ($from, $to, $client_id) {
                                    $query->where('client_id', $client_id);
                                    $query->where('period', Carbon::parse($from)->year);
                                },
                            ]);
                            $rates_plan_room->where('status', 1);
                        },
                    ]);
                },
            ]);

//        $hotels_client = $hotels_client->where('id', 44)->get(); ////////////////////
//        throw new \Exception(json_encode($hotels_client));

        if (count($hotels_id) > 0) {
            $hotels_client = $hotels_client->whereIn('id', $hotels_id)->get();
        } else {
            $hotels_client = $hotels_client->get();
        }

//        dd($hotels_client->toArray());

        $client_markup = Markup::on('mysql_read')->select('hotel')->where([
            'client_id' => $client_id,
            'period' => $period
        ])->first();

        $global_policies_cancelation_channels = PoliciesCancelations::on('mysql_read')->where('code',
            'CANCELLATION_POLICY_CHANNELS')
            ->with([
                'policy_cancellation_parameter' => function ($query) {
                    $query->with('penalty');
                },
            ])
            ->first();

        // throw new \Exception(json_encode($hotels_client));

        $hotels_client->transform(function (Hotel $hotel) use (
            $client_id,
            $date_from,
            $date_to,
            $client_markup,
            $global_policies_cancelation_channels
        ) {
            $hotel->rooms->transform(function (Room $rooms) use ($global_policies_cancelation_channels, $date_from, $date_to) {
                $rooms->rates_plan_room->transform(function (RatesPlansRooms $rate_plan_room) use (
                    $global_policies_cancelation_channels, $date_from, $date_to
                ) {

                    if ($rate_plan_room['bag'] == '1') {
                        unset($rate_plan_room->inventories);
                        $rate_plan_room->inventories = [];
                    } else {
                        unset($rate_plan_room->bag_rate);
                        $rate_plan_room->bag_rate = [];
                    }


                    if ($rate_plan_room['channel']['code'] != 'AURORA') {
                        $price = RatePlanRoomDateRange::where('rate_plan_room_id', '=', $rate_plan_room->id)
                            ->where(function ($query) use ($date_from, $date_to) { // 01-03-2024 | 02-03-2024
                                $query->where('date_from', '<=', $date_from); // 01-01-2023 | 01-03-2024
                                $query->where('date_from', '<=', $date_to); // 01-01-2023 | 02-03-2024
                            })
                            ->where(function ($query) use ($date_from, $date_to) {
                                $query->where('date_to', '>=', $date_from); // 31-12-2023 | 01-03-2024
                                $query->where('date_to', '>=', $date_to); // 31-12-2023 | 02-03-2024
                            })
                            ->orderBy('group', 'ASC')->first();

                        // Aquí se debería considerar los precios de FITS por DEFECTO..
                        // Por analizar.. Hyperguest CHILD
                        $rate_plan_room->channel_child_price = 0;
                        $rate_plan_room->channel_infant_price = 0;

                        if ($price) {
                            $rate_plan_room->channel_child_price = $price->price_child;
                            $rate_plan_room->channel_infant_price = $price->price_infant;
                        }

                        if ($rate_plan_room->policies_cancelation->count() == 0) {
                            $rate_plan_room->calendarys->transform(function (RatesPlansCalendarys $calendary) use (
                                $global_policies_cancelation_channels
                            ) {
                                if (!$calendary['policies_cancelation_id'] or !$calendary['policies_cancelation']) {
                                    $calendary->policies_cancelation_id = $global_policies_cancelation_channels->id;
                                    $calendary->setRelation('policies_cancelation',
                                        $global_policies_cancelation_channels);
                                }

                                return $calendary;
                            });
                        }
                    }

                    return $rate_plan_room;
                });
                return $rooms;
            });

            return collect([
                "client_id" => $client_id,
                "hotel_id" => $hotel->id,
                "check_in" => $date_from,
                "check_out" => $date_to,
                "nights" => difDateDays(Carbon::parse($date_from), Carbon::parse($date_to)),
                "client_markup" => $client_markup,
                "hotel" => $hotel,
            ]);
        });


        $hotels_client = $this->transformRatesChannelsInRatesAurora($hotels_client->toArray());

        return $hotels_client;
    }

    public function getClientHotelsAvailBK(
        $client_id,
        $period,
        array $hotels_client_hotel_id_list,
        $date_from,
        $date_to,
        $from,
        $to,
        $reservation_days,
        $rate_plan_room_ids_include,
        $country_iso,
        $state_iso,
        $city_id,
        $district_id,
        $typeclass_id,
        $hotels_id,
        $language_id
    )
    {
        /** @var Collection $hotels_client */
        $hotels_client = Hotel::select('id', 'name', 'country_id', 'state_id', 'city_id', 'district_id', 'zone_id',
            'hotel_type_id', 'typeclass_id', 'chain_id', 'latitude', 'longitude', 'stars', 'check_in_time',
            'check_out_time', 'preferential', 'min_age_child', 'max_age_child', 'allows_child', 'allows_teenagers',
            'min_age_teenagers', 'max_age_teenagers', 'notes', 'flag_new', 'date_end_flag_new')
//            // Excluir los hoteles que no se quieren trabajar
            ->whereDoesntHave('hotelClients', function ($query) use ($period, $client_id) {
                $query->where('client_id', $this->client_id());
                $query->where('period', $client_id);
            })
//            ->whereDoesntHave('rates_plans.client', function ($query) use ($period, $client_id) {
//                $query->where('client_id', $this->client_id());
//                $query->where('rate_plans', $client_id);
//            })
            ->where('status', 1)
            // filtrar hoteles
            ->when(!empty(count($hotels_client_hotel_id_list) > 0),
                function ($query) use ($hotels_client_hotel_id_list) {
                    return $query->whereIn('id', $hotels_client_hotel_id_list);
                })
            // filtrar destino
            ->when(!empty($country_iso), function ($query) use ($country_iso) {
                return $query->whereHas('country', function ($query) use ($country_iso) {
                    $query->where('iso', $country_iso);
                });
            })
            ->when(!empty($state_iso), function ($query) use ($state_iso) {
                return $query->whereHas('state', function ($query) use ($state_iso) {
                    $query->where('iso', $state_iso);
                });
            })
            ->when(!empty($city_id), function ($query) use ($city_id) {
                return $query->where('city_id', $city_id);
            })
            ->when(!empty($district_id), function ($query) use ($district_id) {
                return $query->where('district_id', $district_id);
            })
            // ->when(!empty($typeclass_id), function ($query) use ($typeclass_id) {
            //     return $query->where('typeclass_id', $typeclass_id);
            // })
            ->whereHas('rooms', function ($query) use ($from, $to, $reservation_days, $rate_plan_room_ids_include) {
                $query->where('state', 1);
                $query->whereHas('rates_plan_room', function ($query) use (
                    $from,
                    $to,
                    $reservation_days,
                    $rate_plan_room_ids_include
                ) {
                    $query->whereIn('id', $rate_plan_room_ids_include);
                    $query->where('status', 1);//lista de ids a incluir
                });
            })
            ->when(!empty($typeclass_id), function ($query) use ($from, $typeclass_id) {
                return $query->whereHas('hoteltypeclass', function ($query) use ($from, $typeclass_id) {
                    $query->where('typeclass_id', $typeclass_id);
                    $query->where('year', Carbon::parse($from)->year);
                });
            })
            ->with([
                'country' => function ($query) {
                    $query->select('id', 'iso', 'local_tax', 'local_service', 'foreign_tax', 'foreign_service');

                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'country');
                            $query->where('language_id', 1);
                        },
                    ]);
                },
            ])
            ->with([
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
            ])
            ->with([
                'city' => function ($query) {
                    $query->select('id');

                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'city');
                            $query->where('language_id', 1);
                        },
                    ]);
                },
            ])
            ->with([
                'district' => function ($query) {
                    $query->select('id');

                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'district');
                            $query->where('language_id', 1);
                        },
                    ]);
                },
            ])
            ->with([
                'zone' => function ($query) use ($language_id) {
                    $query->select('id');
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'zone');
                            $query->where('language_id', $language_id);
                        },
                    ]);
                },
            ])
            ->with([
                'taxes' => function ($query) {
                    $query->where('status', '1');
                },
            ])
            ->with([
                'translations' => function ($query) use ($language_id) {
                    $query->select('object_id', 'value', 'slug');
                    $query->where('type', 'hotel');
                    $query->where('language_id', $language_id);
                },
            ])
            ->with([
                'galeries' => function ($query) {
                    $query->select('object_id', 'slug', 'url');
                    $query->where('type', 'hotel');
                    $query->where('state', 1);
                },
            ])
            ->with([
                'channels' => function ($query) {
                    $query->wherePivot('state', '=', 1);
                    $query->wherePivot('code', '!=', '');
                    $query->wherePivot('code', '!=', 'null');
                },
            ])
            ->with([
                'amenity' => function ($query) use ($language_id) {
                    $query->where('status', 1);

                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'amenity');
                            $query->where('language_id', $language_id);
                        },
                    ]);
                    $query->with([
                        'galeries' => function ($query) {
                            $query->select('object_id', 'url');
                            $query->where('type', 'amenity');
                        },
                    ]);
                },
            ])
            ->with([
                'hoteltype' => function ($query) use ($language_id) {
                    $query->select('id');
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'hoteltype');
                            $query->where('language_id', $language_id);
                        },
                    ]);
                },
            ])
            ->with('chain')
            ->with([
                'hoteltypeclass' => function ($query) use ($from, $typeclass_id, $language_id) {
                    $query->where('year', Carbon::parse($from)->year);
                    $query->when(!empty($typeclass_id), function ($query) use ($typeclass_id) {
                        return $query->where('typeclass_id', $typeclass_id);
                    });

                    $query->with([
                        'typeclass.translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'typeclass');
                            $query->where('language_id', $language_id);
                        },
                    ]);
                },
            ])
            ->with([
                'hotelpreferentials' => function ($query) use ($from) {
                    $query->where('year', Carbon::parse($from)->year);
                },
            ])
            // ->with([
            //     'typeclass' => function ($query) use ($language_id) {
            //         $query->with([
            //             'translations' => function ($query) use ($language_id) {
            //                 $query->select('object_id', 'value');
            //                 $query->where('type', 'typeclass');
            //                 $query->where('language_id', $language_id);
            //             },
            //         ]);
            //     },
            // ])
            ->with([

            ])->with([
                'alerts' => function ($query) use ($period, $language_id) {
                    $query->where('year', '=', $period);
                    $query->where('language_id', $language_id);
                },
            ])->with([
                'markup' => function ($query) use ($period, $client_id) {
                    $query->where('client_id', $client_id);
                    $query->where('period', '>=', $period);
                },
            ])
            ->whereHas('rooms', function ($query) use ($from, $reservation_days) {
                $query->where('state', 1);

                $query->whereHas('rates_plan_room', function ($query) use (
                    $from,
                    $reservation_days
                ) {
                    $query->whereHas('calendarys', function ($query) use (
                        $from,
                        $reservation_days
                    ) {
                        $query->where('date', '=', $from);

                        $query->whereHas('policies_rates', function ($query) use (
                            $from,
                            $reservation_days
                        ) {
                            $query->where('min_length_stay', '<=', $reservation_days);
                            $query->where('max_length_stay', '>=', $reservation_days);
                        });
                    });
                });
            })
            ->with([
                'rooms' => function ($query) use (
                    $from,
                    $to,
                    $reservation_days,
                    $rate_plan_room_ids_include,
                    $period,
                    $client_id,
                    $language_id
                ) {
                    $query->select('id', 'hotel_id', 'room_type_id', 'max_capacity', 'min_adults', 'inventory',
                        'max_adults', 'max_child', 'max_infants', 'bed_additional');
                    $query->where('state', 1);

                    $query->with([
                        'galeries' => function ($query) {
                            $query->select('object_id', 'url');
                            $query->where('type', 'room');
                            $query->where('state', 1);
                        },
                    ]);

                    $query->with([
                        'channels' => function ($query) {
                            $query->wherePivot('state', '=', 1);
                            $query->wherePivot('code', '!=', '');
                            $query->wherePivot('code', '!=', 'null');
                        },
                    ]);

                    $query->with([
                        'room_type' => function ($query) use ($language_id) {
                            $query->select('id', 'occupation');
                            $query->with([
                                'translations' => function ($query) use ($language_id) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'roomtype');
                                    $query->where('language_id', $language_id);
                                },
                            ]);
                        },
                    ]);

                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value', 'slug');
                            $query->where('type', 'room');
                            $query->where('language_id', $language_id);
                        },
                    ]);

                    $query->whereHas('rates_plan_room', function ($query) use (
                        $from,
                        $to,
                        $reservation_days,
                        $rate_plan_room_ids_include
                    ) {
                        $query->where('status', 1);
                        $query->whereIn('id', $rate_plan_room_ids_include);//lista de ids a incluir
                    });

                    $query->with([
                        'rates_plan_room' => function ($rates_plan_room) use (
                            $from,
                            $to,
                            $reservation_days,
                            $rate_plan_room_ids_include,
                            $period,
                            $client_id,
                            $language_id
                        ) {
                            $rates_plan_room->whereDoesntHave('rate_plan.client',
                                function ($query) use ($period, $client_id) {
                                    $query->where('client_id', $this->client_id());
                                    $query->where('period', $period);
                                    $query->whereNull('client_rate_plans.deleted_at');  // se agrego esta linea porque en al relacion se esta trayendo todos los registros elinados con soft delete
                                });
                            $rates_plan_room->select(
                                'id',
                                'rates_plans_id',
                                'room_id',
                                'status',
                                'bag',
                                'channel_id',
                                'channel_child_price',
                                'channel_infant_price'
                            );
                            $rates_plan_room->whereIn('id', $rate_plan_room_ids_include);//lista de ids a incluir
                            $rates_plan_room->with('channel');
                            $rates_plan_room->with([
                                'policies_cancelation' => function ($query) use (
                                    $from,
                                    $to,
                                    $period,
                                    $rates_plan_room,
                                    $language_id
                                ) {
                                    $query->where('type', 'cancellations');
                                    $query->with([
                                        'policy_cancellation_parameter' => function ($query) {
                                            $query->where('min_day', '>=', 0);
                                            $query->where('max_day', '<>', 0);
                                            $query->with('penalty');
                                        },
                                    ]);
                                },
                                'descriptions' => function ($query) use ($from, $to, $period, $rates_plan_room) {

                                },
                                'calendarys' => function ($query) use (
                                    $from,
                                    $to,
                                    $period,
                                    $rates_plan_room,
                                    $language_id
                                ) {
                                    $query->where('date', '>=', $from);
                                    $query->where('date', '<=', $to);
                                    $query->with([
                                        'policies_rates' => function ($query) use ($language_id) {
                                            $query->with([
                                                'policies_cancelation' => function ($query) {
//                                                    $query->with('policy_cancellation_parameter');
                                                    $query->where('type', 'cancellations');
                                                    $query->with([
                                                        'policy_cancellation_parameter' => function ($query) {
                                                            $query->where('min_day', '>=', 0);
                                                            $query->where('max_day', '<>', 0);
                                                            $query->with('penalty');
                                                        },
                                                    ]);
                                                },
                                            ]);
                                            $query->with([
                                                'translations' => function ($query) use ($language_id) {
                                                    $query->where('type', 'rate_policies');
                                                    $query->where('language_id', $language_id);
                                                },
                                            ]);
                                        },
                                        'policies_cancelation' => function ($query) {
                                            $query->where('type', 'cancellations');
                                            $query->with([
                                                'policy_cancellation_parameter' => function ($query) {
                                                    $query->where('min_day', '>=', 0);
                                                    $query->where('max_day', '<>', 0);
                                                    $query->with('penalty');
                                                },
                                            ]);
                                        },
                                    ]);
                                    $query->with('rate');
                                },
                            ]);
                            $rates_plan_room->with([
                                'rate_plan' => function ($query) use ($period, $language_id, $client_id) {
                                    $query->with([
                                        'translations' => function ($query) use ($language_id) {
                                            $query->where('language_id', $language_id);
                                        }
                                    ]);
                                    $query->with([
                                        'translations_no_show' => function ($query) use ($language_id) {
                                            $query->where('language_id', $language_id);
                                        }
                                    ]);
                                    $query->with([
                                        'translations_day_use' => function ($query) use ($language_id) {
                                            $query->where('language_id', $language_id);
                                        }
                                    ]);
                                    $query->with([
                                        'translations_notes' => function ($query) use ($language_id) {
                                            $query->where('language_id', $language_id);
                                        }
                                    ]);
                                    $query->with([
                                        'meal.translations',
                                        'markup' => function ($query) use ($period, $client_id) {
                                            $query->where('client_id', $client_id);
                                            $query->where('period', '>=', $period);
                                        },
                                    ]);
                                    $query->with('promotionsData');
                                    $query->where('status', 1);
                                },
                            ]);
                            $rates_plan_room->with([
                                'inventories' => function ($query) use ($from, $to) {
                                    $query->where('date', '>=', $from);
                                    $query->where('date', '<=', $to);
                                },
                            ]);

                            $rates_plan_room->with([
                                'bag_rate.bag_room.inventory_bags' => function ($query) use ($from, $to) {
                                    $query->where('date', '>=', $from);
                                    $query->where('date', '<=', $to);
                                },
                            ]);

                            $rates_plan_room->with([
                                'markup' => function ($query) use ($from, $to, $client_id) {
                                    $query->where('client_id', $client_id);
                                    $query->where('period', Carbon::parse($from)->year);
                                },
                            ]);
                            $rates_plan_room->where('status', 1);
                        },
                    ]);
                },
            ]);

//        $hotels_client = $hotels_client->where('id', 44)->get(); ////////////////////
//        throw new \Exception(json_encode($hotels_client));

        if (count($hotels_id) > 0) {
            $hotels_client = $hotels_client->whereIn('id', $hotels_id)->get();
        } else {
            $hotels_client = $hotels_client->get();
        }

//        dd($hotels_client->toArray());

        $client_markup = Markup::select('hotel')->where(['client_id' => $client_id, 'period' => $period])->first();

        $global_policies_cancelation_channels = PoliciesCancelations::where('code', 'CANCELLATION_POLICY_CHANNELS')
            ->with([
                'policy_cancellation_parameter' => function ($query) {
                    $query->with('penalty');
                },
            ])
            ->first();

        // throw new \Exception(json_encode($hotels_client));

        $hotels_client->transform(function (Hotel $hotel) use (
            $client_id,
            $date_from,
            $date_to,
            $client_markup,
            $global_policies_cancelation_channels
        ) {
            $hotel->rooms->transform(function (Room $rooms) use ($global_policies_cancelation_channels, $date_from, $date_to) {
                $rooms->rates_plan_room->transform(function (RatesPlansRooms $rate_plan_room) use (
                    $global_policies_cancelation_channels, $date_from, $date_to
                ) {

                    if ($rate_plan_room['bag'] == '1') {
                        unset($rate_plan_room->inventories);
                        $rate_plan_room->inventories = [];
                    } else {
                        unset($rate_plan_room->bag_rate);
                        $rate_plan_room->bag_rate = [];
                    }


                    if ($rate_plan_room['channel']['code'] != 'AURORA') {
                        $price = RatePlanRoomDateRange::where('rate_plan_room_id', '=', $rate_plan_room->id)
                            ->where(function ($query) use ($date_from, $date_to) { // 01-03-2024 | 02-03-2024
                                $query->where('date_from', '<=', $date_from); // 01-01-2023 | 01-03-2024
                                $query->where('date_from', '<=', $date_to); // 01-01-2023 | 02-03-2024
                            })
                            ->where(function ($query) use ($date_from, $date_to) {
                                $query->where('date_to', '>=', $date_from); // 31-12-2023 | 01-03-2024
                                $query->where('date_to', '>=', $date_to); // 31-12-2023 | 02-03-2024
                            })
                            ->orderBy('group', 'ASC')->first();

                        // Aquí se debería considerar los precios de FITS por DEFECTO..
                        // Por analizar.. Hyperguest CHILD
                        $rate_plan_room->channel_child_price = 0;
                        $rate_plan_room->channel_infant_price = 0;

                        if ($price) {
                            $rate_plan_room->channel_child_price = $price->price_child;
                            $rate_plan_room->channel_infant_price = $price->price_infant;
                        }

                        if ($rate_plan_room->policies_cancelation->count() == 0) {
                            $rate_plan_room->calendarys->transform(function (RatesPlansCalendarys $calendary) use (
                                $global_policies_cancelation_channels
                            ) {
                                if (!$calendary['policies_cancelation_id'] or !$calendary['policies_cancelation']) {
                                    $calendary->policies_cancelation_id = $global_policies_cancelation_channels->id;
                                    $calendary->setRelation('policies_cancelation',
                                        $global_policies_cancelation_channels);
                                }

                                return $calendary;
                            });
                        }
                    }

                    return $rate_plan_room;
                });
                return $rooms;
            });

            return collect([
                "client_id" => $client_id,
                "hotel_id" => $hotel->id,
                "check_in" => $date_from,
                "check_out" => $date_to,
                "nights" => difDateDays(Carbon::parse($date_from), Carbon::parse($date_to)),
                "client_markup" => $client_markup,
                "hotel" => $hotel,
            ]);
        });


        $hotels_client = $this->transformRatesChannelsInRatesAurora($hotels_client->toArray());

        return $hotels_client;
    }

    /**
     * obtener las tarifas on request
     *
     * @param $period
     * @param $from
     * @param $to
     * @param $reservation_days
     * @param array $hotels_id
     * @param $language_id
     * @param $rates_plan_rooms_on_request
     * @return mixed
     */
    public function getClientHotelsAvailOnRequestReadonly(
        $period,
        $from,
        $to,
        $reservation_days,
        array $hotels_id,
        $language_id,
        $rates_plan_rooms_on_request
    )
    {
        return RatesPlansRooms::on('mysql_read')->whereIn('id', $rates_plan_rooms_on_request)
            ->when(count($hotels_id) > 0, function ($query) use ($hotels_id) {
                return $query->whereHas('rate_plan', function ($query) use ($hotels_id) {
                    $query->whereIn('hotel_id', $hotels_id);
                });
            })
            ->with([
//                'policies_cancelation' => function ($query) use ($from, $to, $period, $rates_plan_room) {
                'policies_cancelation' => function ($query) {
                    $query->where('type', 'cancellations');
                    $query->with([
                        'policy_cancellation_parameter' => function ($query) {
                            $query->where('min_day', '>=', 0);
                            $query->where('max_day', '<>', 0);
                            $query->with('penalty');
                        },
                    ]);
                },
            ])
            ->with([
                'descriptions' => function ($query) {

                }
            ])
            ->with([
                'calendarys' => function ($query) use ($from, $to, $language_id) {
                    $query->where('date', '>=', $from);
                    $query->where('date', '<=', $to);
                    $query->orderBy('date');
                    $query->with([
                        'policies_rates' => function ($query) use ($language_id) {
                            $query->with([
                                'policies_cancelation' => function ($query) {
//                                        $query->with('policy_cancellation_parameter');
                                    $query->where('type', 'cancellations');
                                    $query->with([
                                        'policy_cancellation_parameter' => function ($query) {
                                            $query->where('min_day', '>=', 0);
                                            $query->where('max_day', '<>', 0);
                                            $query->with('penalty');
                                        },
                                    ]);
                                },
                            ]);
                            $query->with([
                                'translations' => function ($query) use ($language_id) {
                                    $query->where('type', 'rate_policies');
                                    $query->where('language_id', $language_id);
                                },
                            ]);
                        },
                    ]);
                    $query->with('rate');
                },
            ])->with('channel')
            ->with('rate_plan.meal.translations')
            ->with([
                'rate_plan' => function ($query) use ($period, $language_id) {
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->where('language_id', $language_id);
                        }
                    ]);
                    $query->with([
                        'translations_no_show' => function ($query) use ($language_id) {
                            $query->where('slug', 'no_show');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                    $query->with([
                        'translations_day_use' => function ($query) use ($language_id) {
                            $query->where('slug', 'day_use');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                    $query->with([
                        'translations_notes' => function ($query) use ($language_id) {
                            $query->where('slug', 'notes');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                    $query->with([
                        'meal.translations',
                        'markup' => function ($query) use ($period) {
                            $query->where('period', '>=', $period);
                        },
                    ]);
                },
            ])
//                ->whereHas('inventories', function ($query) use ($from, $to) {
//                    $query->where('date', '>=', $from);
//                    $query->where('date', '<=', $to);
//                    $query->where('locked', '<>', 1);
//                })
            ->with([
                'inventories' => function ($query) use ($from, $to) {
                    $query->where('date', '>=', $from);
                    $query->where('date', '<=', $to);
                    $query->where('locked', '<>', 1);
                },
            ])
            ->with([
                'room' => function ($query) {
                    $query->with([
                        'galeries' => function ($query) {
                            $query->select('object_id', 'url');
                            $query->where('type', 'room');
                            $query->where('state', 1);
                        },
                    ]);

                    $query->with([
                        'channels' => function ($query) {
                            $query->wherePivot('state', '=', 1);
                            $query->wherePivot('code', '!=', '');
                            $query->wherePivot('code', '!=', 'null');
                        },
                    ]);

                    $query->with([
                        'room_type' => function ($query) {
                            $query->select('id', 'occupation');
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'roomtype');
                                    $query->where('language_id', 1);
                                },
                            ]);
                        },
                    ]);

                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value', 'slug');
                            $query->where('type', 'room');
                            $query->where('language_id', 1);
                        },
                    ]);
                }
            ])->where('channel_id', 1)
            ->whereHas('calendarys', function ($query) use (
                $from,
                $reservation_days
            ) {
                $query->where('date', '=', $from);

                $query->whereHas('policies_rates', function ($query) use (
                    $from,
                    $reservation_days
                ) {
                    $query->where('min_length_stay', '<=', $reservation_days);
                    $query->where('max_length_stay', '>=', $reservation_days);
                });
            })
            ->get()->toArray();
    }

    public function getClientHotelsAvailOnRequestBK(
        $period,
        $from,
        $to,
        $reservation_days,
        array $hotels_id,
        $language_id,
        $rates_plan_rooms_on_request
    )
    {
        return RatesPlansRooms::whereIn('id', $rates_plan_rooms_on_request)
            ->when(count($hotels_id) > 0, function ($query) use ($hotels_id) {
                return $query->whereHas('rate_plan', function ($query) use ($hotels_id) {
                    $query->whereIn('hotel_id', $hotels_id);
                });
            })
            ->with([
                'policies_cancelation' => function ($query) {
                    $query->where('type', 'cancellations');
                    $query->with([
                        'policy_cancellation_parameter' => function ($query) {
                            $query->where('min_day', '>=', 0);
                            $query->where('max_day', '<>', 0);
                            $query->with('penalty');
                        },
                    ]);
                },
            ])
            ->with('descriptions')
            ->with([
                'calendarys' => function ($query) use ($from, $to, $language_id) {
                    $query->where('date', '>=', $from);
                    $query->where('date', '<=', $to);
                    $query->orderBy('date');
                    $query->with([
                        'policies_rates' => function ($query) use ($language_id) {
                            $query->with([
                                'policies_cancelation' => function ($query) {
//                                        $query->with('policy_cancellation_parameter');
                                    $query->where('type', 'cancellations');
                                    $query->with([
                                        'policy_cancellation_parameter' => function ($query) {
                                            $query->where('min_day', '>=', 0);
                                            $query->where('max_day', '<>', 0);
                                            $query->with('penalty');
                                        },
                                    ]);
                                },
                            ]);
                            $query->with([
                                'translations' => function ($query) use ($language_id) {
                                    $query->where('type', 'rate_policies');
                                    $query->where('language_id', $language_id);
                                },
                            ]);
                        },
                    ]);
                    $query->with('rate');
                },
            ])->with('channel')
            ->with('rate_plan.meal.translations')
            ->with([
                'rate_plan' => function ($query) use ($period, $language_id) {
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->where('language_id', $language_id);
                            $query->where('type', 'rates_plan');
                            $query->where('slug', 'commercial_name');
                        }
                    ]);
                    $query->with([
                        'translations_no_show' => function ($query) use ($language_id) {
                            $query->where('slug', 'no_show');
                            $query->where('type', 'rates_plan');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                    $query->with([
                        'translations_day_use' => function ($query) use ($language_id) {
                            $query->where('slug', 'day_use');
                            $query->where('type', 'rates_plan');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                    $query->with([
                        'translations_notes' => function ($query) use ($language_id) {
                            $query->where('slug', 'notes');
                            $query->where('type', 'rates_plan');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                    $query->with([
                        'meal.translations',
                        'markup' => function ($query) use ($period) {
                            $query->where('period', '>=', $period);
                        },
                    ]);
                },
            ])
            ->with([
                'inventories' => function ($query) use ($from, $to) {
                    $query->where('date', '>=', $from);
                    $query->where('date', '<=', $to);
                    $query->where('locked', '<>', 1);
                },
            ])
            ->with([
                'room' => function ($query) {
                    $query->with([
                        'galeries' => function ($query) {
                            $query->select('object_id', 'url');
                            $query->where('type', 'room');
                            $query->where('state', 1);
                        },
                    ]);

                    $query->with([
                        'channels' => function ($query) {
                            $query->wherePivot('state', '=', 1);
                            $query->wherePivot('code', '!=', '');
                            $query->wherePivot('code', '!=', 'null');
                        },
                    ]);

                    $query->with([
                        'room_type' => function ($query) {
                            $query->select('id', 'occupation');
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'roomtype');
                                    $query->where('language_id', 1);
                                },
                            ]);
                        },
                    ]);

                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value', 'slug');
                            $query->where('type', 'room');
                            $query->where('language_id', 1);
                        },
                    ]);
                }
            ])->where('channel_id', 1)
            ->whereHas('calendarys', function ($query) use (
                $from,
                $reservation_days
            ) {
                $query->where('date', '=', $from);

                $query->whereHas('policies_rates', function ($query) use (
                    $from,
                    $reservation_days
                ) {
                    $query->where('min_length_stay', '<=', $reservation_days);
                    $query->where('max_length_stay', '>=', $reservation_days);
                });
            })
            ->get()->toArray();
    }

    /**
     * esta funcion retorna un arreglo de hoteles con una estructura solo de hoteles
     *
     * @param array $hotels_id
     * @param $period
     * @param $client_id
     * @param $date_from
     * @param $date_to
     * @return mixed
     */
    public function getDataHotelNoReturnReadonly($hotels_id, $period, $client_id, $date_from, $date_to, $language_id)
    {
        /** @var Collection $hotels_client */
        $hotels_client = Hotel::on('mysql_read')->select('id', 'name', 'country_id', 'state_id', 'city_id',
            'district_id', 'zone_id',
            'hotel_type_id', 'typeclass_id', 'chain_id', 'latitude', 'longitude', 'stars', 'check_in_time',
            'check_out_time', 'preferential', 'min_age_child', 'max_age_child', 'allows_child', 'allows_teenagers',
            'min_age_teenagers', 'max_age_teenagers', 'notes', 'flag_new', 'date_end_flag_new')
//            // Excluir los hoteles que no se quieren trabajar
            ->whereDoesntHave('hotelClients', function ($query) use ($period, $client_id) {
                $query->where('client_id', $this->client_id());
                $query->where('period', $client_id);
            })
            ->where('status', 1)
            ->with([
                'country' => function ($query) {
                    $query->select('id', 'iso', 'local_tax', 'local_service', 'foreign_tax', 'foreign_service');

                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'country');
                            $query->where('language_id', 1);
                        },
                    ]);
                },
            ])
            ->with([
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
            ])
            ->with([
                'city' => function ($query) {
                    $query->select('id');

                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'city');
                            $query->where('language_id', 1);
                        },
                    ]);
                },
            ])
            ->with([
                'district' => function ($query) {
                    $query->select('id');

                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'district');
                            $query->where('language_id', 1);
                        },
                    ]);
                },
            ])
            ->with([
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
            ])
            ->with([
                'taxes' => function ($query) {
                    $query->where('status', '1');
                },
            ])
            ->with([
                'translations' => function ($query) use ($language_id) {
                    $query->select('object_id', 'value', 'slug');
                    $query->where('type', 'hotel');
                    $query->where('language_id', $language_id);
                },
            ])
            ->with([
                'galeries' => function ($query) {
                    $query->select('object_id', 'slug', 'url');
                    $query->where('type', 'hotel');
                    $query->where('state', 1);
                },
            ])
            ->with([
                'channels' => function ($query) {
                    $query->wherePivot('state', '=', 1);
                    $query->wherePivot('code', '!=', '');
                    $query->wherePivot('code', '!=', 'null');
                },
            ])
            ->with([
                'amenity' => function ($query) use ($language_id) {
                    $query->where('status', 1);

                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'amenity');
                            $query->where('language_id', $language_id);
                        },
                    ]);
                    $query->with([
                        'galeries' => function ($query) {
                            $query->select('object_id', 'url');
                            $query->where('type', 'amenity');
                        },
                    ]);
                },
            ])
            ->with([
                'hoteltype' => function ($query) use ($language_id) {
                    $query->select('id');
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'hoteltype');
                            $query->where('language_id', $language_id);
                        },
                    ]);
                },
            ])
            ->with('chain')
            // ->with([
            //     'typeclass' => function ($query) use ($language_id) {
            //         $query->with([
            //             'translations' => function ($query) use ($language_id) {
            //                 $query->select('object_id', 'value');
            //                 $query->where('type', 'typeclass');
            //                 $query->where('language_id', $language_id);
            //             },
            //         ]);
            //     },
            // ])
            ->with([
                'hoteltypeclass' => function ($query) use ($period, $language_id) {
                    $query->where('year', $period);
                    $query->with([
                        'typeclass.translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'typeclass');
                            $query->where('language_id', $language_id);
                        },
                    ]);
                },
            ])
            ->with([
                'hotelpreferentials' => function ($query) use ($period) {
                    $query->where('year', $period);
                },
            ])
            ->with([
                'markup' => function ($query) use ($period, $client_id) {
                    $query->where('client_id', $client_id);
                    $query->where('period', '>=', $period);
                },
            ]);

        $hotels_client = $hotels_client->whereIn('id', $hotels_id)->get();

        $client_markup = Markup::on('mysql_read')->select('hotel')->where([
            'client_id' => $client_id,
            'period' => $period
        ])->first();

        $hotels_client->transform(function (Hotel $hotel) use (
            $client_id,
            $date_from,
            $date_to,
            $client_markup
        ) {

            $hotel['rooms'] = [];

            return collect([
                "client_id" => $client_id,
                "hotel_id" => $hotel->id,
                "check_in" => $date_from,
                "check_out" => $date_to,
                "nights" => difDateDays(Carbon::parse($date_from), Carbon::parse($date_to)),
                "client_markup" => $client_markup,
                "hotel" => $hotel,
                "best_options" => [],

            ]);
        });


        return $hotels_client->toArray();
    }

    public function getDataHotelNoReturn($hotels_id, $period, $client_id, $date_from, $date_to, $language_id, $from, $typeclass_id, $country_iso)
    {
        /** @var Collection $hotels_client */
        $hotels_client = Hotel::select('id', 'name', 'country_id', 'state_id', 'city_id', 'district_id', 'zone_id',
            'hotel_type_id', 'typeclass_id', 'chain_id', 'latitude', 'longitude', 'stars', 'check_in_time',
            'check_out_time', 'preferential', 'min_age_child', 'max_age_child', 'allows_child', 'allows_teenagers',
            'min_age_teenagers', 'max_age_teenagers', 'notes', 'flag_new', 'date_end_flag_new')
//            // Excluir los hoteles que no se quieren trabajar
            ->whereDoesntHave('hotelClients', function ($query) use ($period, $client_id) {
                $query->where('client_id', $this->client_id());
                $query->where('period', $client_id);
            })
            ->when(!empty($typeclass_id), function ($query) use ($from, $typeclass_id) {
                return $query->whereHas('hoteltypeclass', function ($query) use ($from, $typeclass_id) {
                    $query->where('typeclass_id', $typeclass_id);
                    $query->where('year', Carbon::parse($from)->year);
                });
            })
            ->where('status', 1)
            ->with([
                'country' => function ($query) {
                    $query->select('id', 'iso', 'local_tax', 'local_service', 'foreign_tax', 'foreign_service');

                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'country');
                            $query->where('language_id', 1);
                        },
                    ]);
                },
            ])
            ->with([
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
            ])
            ->with([
                'city' => function ($query) {
                    $query->select('id');

                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'city');
                            $query->where('language_id', 1);
                        },
                    ]);
                },
            ])
            ->with([
                'district' => function ($query) {
                    $query->select('id');

                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'district');
                            $query->where('language_id', 1);
                        },
                    ]);
                },
            ])
            ->with([
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
            ])
            ->with([
                'taxes' => function ($query) {
                    $query->where('status', '1');
                },
            ])
            ->with([
                'translations' => function ($query) use ($language_id) {
                    $query->select('object_id', 'value', 'slug');
                    $query->where('type', 'hotel');
                    $query->where('language_id', $language_id);
                },
            ])
            ->with([
                'galeries' => function ($query) {
                    $query->select('object_id', 'slug', 'url');
                    $query->where('type', 'hotel');
                    $query->where('state', 1);
                },
            ])
            ->with([
                'channels' => function ($query) {
                    $query->wherePivot('state', '=', 1);
                    $query->wherePivot('code', '!=', '');
                    $query->wherePivot('code', '!=', 'null');
                },
            ])
            ->with([
                'amenity' => function ($query) use ($language_id) {
                    $query->where('status', 1);

                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'amenity');
                            $query->where('language_id', $language_id);
                        },
                    ]);
                    $query->with([
                        'galeries' => function ($query) {
                            $query->select('object_id', 'url');
                            $query->where('type', 'amenity');
                        },
                    ]);
                },
            ])
            ->with([
                'hoteltype' => function ($query) use ($language_id) {
                    $query->select('id');
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'hoteltype');
                            $query->where('language_id', $language_id);
                        },
                    ]);
                },
            ])
            ->with('chain')
            // ->with([
            //     'typeclass' => function ($query) use ($language_id) {
            //         $query->with([
            //             'translations' => function ($query) use ($language_id) {
            //                 $query->select('object_id', 'value');
            //                 $query->where('type', 'typeclass');
            //                 $query->where('language_id', $language_id);
            //             },
            //         ]);
            //     },
            // ])
            ->with([
                'hoteltypeclass' => function ($query) use ($period, $language_id, $typeclass_id) {
                    $query->where('year', $period);
                    $query->when(!empty($typeclass_id), function ($query) use ($typeclass_id) {
                        return $query->where('typeclass_id', $typeclass_id);
                    });

                    $query->with([
                        'typeclass.translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'typeclass');
                            $query->where('language_id', $language_id);
                        },
                    ]);
                },
            ])
            ->with([
                'hotelpreferentials' => function ($query) use ($period) {
                    $query->where('year', $period);
                },
            ])
            ->with([
                'markup' => function ($query) use ($period, $client_id) {
                    $query->where('client_id', $client_id);
                    $query->where('period', '>=', $period);
                },
            ])->with([
                'alerts' => function ($q) use ($period, $language_id) {
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
                },
            ]);

        $hotels_client = $hotels_client->whereIn('id', $hotels_id)->get();

        // $client_markup = Markup::select('hotel')->where(['client_id' => $client_id, 'period' => $period])->first();

        $client_markup = Markup::whereHas('businessRegion.countries', function($query) use ($country_iso) {
            $query->where('iso', $country_iso);
        })->where(['client_id' => $client_id, 'period' => $period])->first();


        $hotels_client->transform(function (Hotel $hotel) use (
            $client_id,
            $date_from,
            $date_to,
            $client_markup
        ) {

            $hotel['rooms'] = [];

            return [
                "client_id" => $client_id,
                "hotel_id" => $hotel->id,
                "check_in" => $date_from,
                "check_out" => $date_to,
                "nights" => difDateDays(Carbon::parse($date_from), Carbon::parse($date_to)),
                "client_markup" => $client_markup,
                "hotel" => $hotel,
                "best_options" => [],

            ];
        });


        return $hotels_client->toArray();
    }

    public function getMarkupFromsearch($clientMarkup, $hotelMarkup, $rates_plan_room, $set_markup = 0)
    {
        if ($set_markup > 0) { // Todo Verifico primero si tiene un markup asignado de forma obligatoria (Ejm: Cotizador)
            $markup = $set_markup;
        } elseif (!empty($rates_plan_room['rate_plan']['markup'])) {
            $markup = $rates_plan_room['rate_plan']['markup']['markup'];
        } elseif (!empty($hotelMarkup['markup'])) {
            $markup = $hotelMarkup['markup'];
        } else {
            $markup = $clientMarkup['hotel'];
        }
        $rates_plan_room['markup']['markup'] = (double)$markup;
        return $rates_plan_room;
    }

    public function checkHotelFavorite($hotel_id)
    {
        $hotel_favorite_exists = HotelFavoriteUser::where('user_id', Auth::user()->id)->where('hotel_id',
            $hotel_id)->get();

        if ($hotel_favorite_exists->count() > 0) {
            return $hotel_favorite_exists[0]["favorite"];
        } else {
            return 0;
        }
    }

    /**
     * Funcion retorna un arreglo de ids de servicios dado un periodo y un client_id
     *
     * @param $client_id
     * @param null $period
     * @param false $pluck
     * @return mixed
     */
    public function getClientServices($client_id, $period, $pluck = false, $service_id = null, $regions = [])
    {
        $service_client = Service::select(['id'])
            // Excluir los servicios que no se quieren trabajar
            ->whereDoesntHave('service_clients', function ($query) use ($client_id, $period, $regions) {
                $query->where('client_id', $client_id);
                $query->where('period', $period);
                $query->whereIn('business_region_id',$regions);
            })
            ->when(!empty($service_id), function ($query) use ($service_id) {
                if (is_array($service_id)) {
                    return $query->whereIn('id', $service_id);
                } else {
                    return $query->where('id', '=', $service_id);
                }
            })->where('status', 1)->get();
        $service_client->transform(function ($query) {
            return collect([
                "service_id" => $query->id,
            ]);
        });
        if ($pluck) {
            $service_client = $service_client->pluck('service_id');
        }

        return $service_client;
    }


    public function getDestinationsServices($service_clients, $model)
    {
        $service_origins = $model::select('country_id', 'state_id', 'city_id', 'zone_id')
            ->whereIn('service_id', $service_clients)
            ->with([
                'country' => function ($query) use ($service_clients) {
                    $query->select('id', 'iso');
                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'country');
                            $query->where('language_id', 1);
                        },
                    ]);
                },
            ])->with([
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
            ])->with([
                'city' => function ($query) {
                    $query->select('id');
                    $query->with([
                        'translations' => function ($query) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'city');
                            $query->where('language_id', 1);
                        },
                    ]);
                },
            ])->with([
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
            ])->distinct()->get();

        return $service_origins;
    }

    /**
     * esta funcion retorna un arreglo de los servicios dado un periodo y un client_id
     *
     * @param data $date_to
     * @param $origin_country_iso
     * @param $destiny_country_iso
     * @param null $origin_state_iso
     * @param null $origin_city_id
     * @param null $origin_zone_id
     * @param null $destiny_state_iso
     * @param null $destiny_city_id
     * @param null $destiny_zone_id
     * @param null $filter
     * @param null $sub_category
     * @param null $classification
     * @param null $experiences
     * @param null $type
     * @param null $period
     * @param string $lang_iso
     * @param integer $totalPax
     * @param int $child_min_age_search
     * @return collection
     */
    public function getServicesClient(
        $services_id,
        $current_date,
        $date_to,
        $origin_country_iso,
        $destiny_country_iso,
        $origin_state_iso = null,
        $origin_city_id = null,
        $origin_zone_id = null,
        $destiny_state_iso = null,
        $destiny_city_id = null,
        $destiny_zone_id = null,
        $filter = null,
        $sub_category = null,
        $classification = null,
        $experiences = null,
        $type = null,
        $period = null,
        $lang_iso = 'es',
        $language_id = 1,
        $totalPax = 0,
        int $child_min_age_search = null,
        Carbon $reservation_time,
        $recommendations = null,
        $dayOfWeekName,
        $compensation = false,
        $filtro_miselaneo = false,
        $countries = []
    )
    {

        $user_type = Auth::user()->user_type_id;
        $query_search = Service::select([
            'id',
            'aurora_code',
            'name',
            'currency_id',
            'latitude',
            'longitude',
            'qty_reserve',
            'equivalence_aurora',
            'affected_igv',
            'affected_markup',
            'affected_schedule',
            'allow_guide',
            'allow_child',
            'allow_infant',
            'limit_confirm_hours',
            'unit_duration_limit_confirmation',
            'infant_min_age',
            'infant_max_age',
            'include_accommodation',
            'unit_id',
            'unit_duration_id',
            'unit_duration_reserve',
            'service_type_id',
            'classification_id',
            'service_sub_category_id',
            'user_id',
            'duration',
            'pax_min',
            'pax_max',
            'min_age',
            'require_itinerary',
            'require_image_itinerary',
            'status',
            'physical_intensity_id',
            'notes',
            'compensation',
            'tag_service_id',
            'service_mask'
        ])
            ->where('status', 1)
            ->where('type', 'service')
            ->where('pax_min', '<=', $totalPax)
            ->where('pax_max', '>=', $totalPax);

        // $regionId = optional($query_search->serviceOrigin->country->businessRegions->first())->id;

        if (isset($services_id[0])) {
            $query_search = $query_search->whereIn('id', $services_id);
        } elseif ((is_string($services_id) or is_integer($services_id)) and $services_id !== null) {
            $query_search = $query_search->where('id', $services_id);
        }


        // Todo Si el cliente tiene activo el campo ecommerce entonces  mostramos los servicios creados por el cliente
        if ($this->_client->ecommerce == 1) {
            $query_search = $query_search->whereDoesntHave('client_services', function ($query) {
                $query->where('client_id', '!=', $this->client_id());
            });

        }


        $query_search = $query_search->with([
            'tax' => function ($query) {
                $query->select('amount', 'service_id');
                $query->where('status', 1);
            }
        ])
            ->with([
                'markup_service' => function ($query) use ($period, $services_id) {
                    $query->where('client_id', $this->client_id());
                    $query->where('period', $period);
                }
            ])
            ->with([
                'languages_guide' => function ($query) {
                    $query->select('id', 'language_id', 'service_id');
                    $query->with([
                        'language' => function ($query) {
                            $query->select('id', 'name', 'iso');
                        }
                    ]);
                }
            ])
            ->with([
                'tag_service' => function ($query) use ($language_id) {
                    $query->select('id');
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'slug', 'value');
                            $query->where('type', 'tagservices');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])
            ->with([
                'serviceDestination' => function ($query) {
                    $query->select(['id', 'service_id', 'country_id', 'state_id', 'city_id', 'zone_id']);
                    $query->with([
                        'country' => function ($query) {
                            $query->select(['id', 'iso']);
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'country');
                                    $query->where('language_id', 1);
                                }
                            ]);
                        }
                    ]);
                    $query->with([
                        'state' => function ($query) {
                            $query->select(['id', 'iso']);
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'state');
                                    $query->where('language_id', 1);
                                }
                            ]);
                        }
                    ]);
                    $query->with([
                        'city' => function ($query) {
                            $query->select('id');
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'city');
                                    $query->where('language_id', 1);
                                }
                            ]);
                        }
                    ]);
                    $query->with([
                        'zone' => function ($query) {
                            $query->select('id');
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'zone');
                                    $query->where('language_id', 1);
                                }
                            ]);
                        }
                    ]);

                }
            ])
            ->with([
                'serviceOrigin' => function ($query) {
                    $query->select(['id', 'service_id', 'country_id', 'state_id', 'city_id', 'zone_id']);
                    $query->with([
                        'country' => function ($query) {
                            $query->select(['id', 'iso']);
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'country');
                                    $query->where('language_id', 1);
                                }
                            ]);
                        }
                    ]);
                    $query->with([
                        'state' => function ($query) {
                            $query->select(['id', 'iso']);
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'state');
                                    $query->where('language_id', 1);
                                }
                            ]);
                        }
                    ]);
                    $query->with([
                        'city' => function ($query) {
                            $query->select('id');
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'city');
                                    $query->where('language_id', 1);
                                }
                            ]);
                        }
                    ]);
                    $query->with([
                        'zone' => function ($query) {
                            $query->select('id');
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'zone');
                                    $query->where('language_id', 1);
                                }
                            ]);
                        }
                    ]);

                }
            ])
            ->with([
                'currency' => function ($query) use ($language_id) {
                    $query->select(['id', 'symbol', 'iso']);
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'currency');
                            $query->where('language_id', $language_id);
                        }
                    ]);

                }
            ])
            ->with([
                'serviceSubCategory' => function ($query) use ($language_id) {
                    $query->select(['id', 'service_category_id', 'order']);
                    $query->with([
                        'serviceCategories' => function ($query) use ($language_id) {
                            $query->select(['id', 'order']);
                            $query->with([
                                'translations' => function ($query) use ($language_id) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'servicecategory');
                                    $query->where('language_id', $language_id);
                                }
                            ]);

                        }
                    ]);
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'servicesubcategory');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])
            ->with([
                'classification' => function ($query) use ($language_id) {
                    $query->select('id');
                    $query->with([
                        'galeries' => function ($query) {
                            $query->where('type', 'classification');
                        }
                    ]);
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'classification');
                            $query->where('language_id', $language_id);
                        }
                    ]);

                }
            ])->with([
                'unitDurations' => function ($query) use ($language_id) {
                    $query->select('id');
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'unitduration');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])->with([
                'serviceType' => function ($query) use ($language_id) {
                    $query->select(['id', 'code', 'abbreviation']);
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'servicetype');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])->with([
                'experience' => function ($query) use ($language_id) {
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('type', 'experience');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])->with([
                'restriction' => function ($query) use ($language_id) {
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select('object_id', 'value');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])->with([
                'highlights.featured' => function ($query) use ($language_id) {
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select(['value', 'object_id']);
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])->with([
                'instructions' => function ($query) use ($language_id) {
                    $query->select(['id']);
                    $query->with([
                        'instructions' => function ($query) use ($language_id) {
                            $query->with([
                                'translations' => function ($query) use ($language_id) {
                                    $query->select(['value', 'object_id']);
                                    $query->where('type', 'instruction');
                                    $query->where('language_id', $language_id);
                                }
                            ]);
                        }
                    ]);
                }
            ])->with([
                'physical_intensity' => function ($query) use ($language_id) {
                    $query->select(['id', 'color']);
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select(['value', 'object_id']);
                            $query->where('type', 'physicalintensity');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])
            ->with([
                'service_translations' => function ($query) use ($language_id, $filter) {
                    $query->select('id', 'language_id', 'name', 'name_commercial',
                        'description', 'itinerary', 'summary', 'description_commercial',
                        'itinerary_commercial', 'summary_commercial', 'service_id');
                    $query->where('language_id', $language_id);
                }
            ])
            ->with([
                'service_translations_gtm' => function ($query) use ($language_id, $filter) {
                    $query->select('id', 'language_id', 'name', 'service_id');
                    $query->where('language_id', 2);
                }
            ])
            ->with([
                'galleries' => function ($query) {
                    $query->select('object_id', 'slug', 'url');
                    $query->where('type', 'service');
                },
            ])
            ->with([
                'inclusions' => function ($query) use ($language_id, $user_type) {
                    if ($user_type != 3) {
                        $query->where('see_client', 1);
                    }
                    $query
                        ->orderBy('day', 'asc')
                        ->orderBy('order', 'asc');
                    $query->with([
                        'inclusions.translations' => function ($query) use ($language_id) {
                            $query->where('type', 'inclusion');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])
            ->with([
                'schedules' => function ($query) use ($reservation_time) {
                    $query->with('servicesScheduleDetail');
                }
            ])
            ->with([
                'children_ages' => function ($query) use ($reservation_time) {
                    $query->select(['service_id', 'min_age', 'max_age'])->where('status', 1);
                }
            ])
            ->with([
                'rated' => function ($query) use ($period) {
                    $query->select('id', 'rated', 'service_id');
                    $query->where('client_id', $this->client_id());
                    $query->where('period', $period);
                    $query->orderBy('rated', 'desc');
                }
            ])
            ->with([
                'client_service_setting' => function ($query) {
                    $query->select('client_id', 'service_id', 'reservation_from', 'unit_duration_reserve');
                    $query->where('client_id', $this->client_id());
                }
            ])
            ->with([
                'operability' => function ($query) use ($language_id) {
                    $query->with([
                        'services_operation_activities.service_type_activities.translations' => function ($query) use (
                            $language_id
                        ) {
                            $query->where('type', 'servicetypeactivity');
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])
            ->with([
                'supplements' => function ($query) use ($date_to, $totalPax, $period, $language_id) {
                    $query->select('id', 'service_id', 'object_id', 'type', 'days_to_charge', 'charge_all_pax');
                }
            ])
            ->with([
                'client_services' => function ($query) use ($date_to, $totalPax, $period, $language_id) {
                    $query->select('id', 'service_id', 'client_id');
                }
            ])


            //TODO verifica Que tenga tarifa y en plan de tarifa y que la tarifa tenga inventario

            ->when(($filtro_miselaneo == false), function ($query) use ($date_to, $totalPax, $period, $lang_iso, $dayOfWeekName, $user_type) {
                return $query->whereHas('service_rate', function ($query) use ($date_to, $totalPax, $period, $lang_iso, $dayOfWeekName, $user_type) {
                    $query->where('status', 1);
                    if ($user_type == 4) {
                        $query->where('price_dynamic', 0);
                    }
                    $query->whereDoesntHave('clients_rate_plan', function ($query) use ($period) {
                        $query->where('client_id', $this->client_id());
                        $query->where('period', $period);
                    });
                    $query->whereHas('service_rate_plans', function ($query) use ($date_to, $totalPax, $dayOfWeekName) {
                        $query->where('date_from', '<=', $date_to)
                            ->where('date_to', '>=', $date_to)
                            ->where('pax_from', '<=', $totalPax)
                            ->where('pax_to', '>=', $totalPax)
                            ->where('status', 1);
                    });
                });
            })
            ->with([
                'service_rate' => function ($query) use ($date_to, $totalPax, $period, $language_id, $user_type) {
                    $query->select('id', 'name', 'service_id', 'status');
                    $query->where('status', 1);
                    if ($user_type == 4) {
                        $query->where('price_dynamic', 0);
                    }
                    $query->whereDoesntHave('clients_rate_plan', function ($query) use ($period) {
                        $query->where('client_id', $this->client_id());
                        $query->where('period', $period);
                    });
                    $query->with([
                        'offers' => function ($query) use ($date_to) {
                            $query->select('id', 'client_id', 'service_rate_id', 'date_from', 'date_to', 'value',
                                'is_offer');
                            $query->where('date_from', '<=', $date_to);
                            $query->where('date_to', '>=', $date_to);
                            $query->where('client_id', $this->client_id());
                            $query->where('status', 1);
                        }
                    ]);
                    $query->with([
                        'service_rate_plans' => function ($query) use ($date_to, $totalPax) {
                            $query->where('date_from', '<=', $date_to)
                                ->where('date_to', '>=', $date_to);
                            $query->where('pax_from', '<=', $totalPax)
                                ->where('pax_to', '>=', $totalPax)
                                ->where('status', 1);
                            $query->with([
                                'policy' => function ($query) {
                                    $query->where('status', 1);
                                    $query->with([
                                        'parameters' => function ($query) {
                                            $query->with('penalty');
                                        }
                                    ]);
                                }
                            ]);
                        }
                    ])->with([
                        'inventory' => function ($query) use ($date_to, $totalPax) {
                            $query->select([
                                'id',
                                'service_rate_id',
                                'day',
                                'date',
                                'inventory_num',
                                'total_booking',
                                'total_canceled',
                                'locked',
                            ]);
                            $query->where('date', '>=', $date_to);
                            $query->where('date', '<=', $date_to);
                        }
                    ])->with([
                        'markup_rate_plan' => function ($query) use ($period) {
                            $query->select('markup', 'period', 'service_rate_id');
                            $query->where('client_id', $this->client_id());
                            $query->where('period', $period);
                        }
                    ])->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->where('language_id', $language_id);
                        }
                    ]);
                }
            ])
            ->when(($filter !== null),
                function ($query) use ($filter, $language_id, $origin_country_iso, $destiny_country_iso) {
                    $query->where(function ($query) use (
                        $filter,
                        $language_id,
                        $origin_country_iso,
                        $destiny_country_iso
                    ) {
                        //TODO filtro por nombre o codigo de aurora
                        // if (!empty($origin_country_iso) or !empty($destiny_country_iso)) {
                        //     $query->orWhere('aurora_code', 'like', $filter);
                        // } else {
                        //     $query->orWhere('aurora_code', 'like', '%' . $filter . '%');
                        // }

                        $query->orWhere('aurora_code', 'like', '%' . $filter . '%');

                        //TODO filtro por idioma del nombre comercial / descripcion / itinerario
                        $query->orWhereHas('service_translations', function ($query) use ($filter, $language_id) {
                            $query->where(function ($query) use ($filter) {
                                $query->where('name_commercial', 'like', '%' . $filter . '%');
                                $query->orWhere('name', 'like', '%' . $filter . '%');
                            });
                            // $query->where('name_commercial', 'like', '%' . $filter . '%');
                            // $query->orWhere('description', 'like', '%' . $filter . '%');
                            // $query->orWhere('itinerary', 'like', '%' . $filter . '%');
                            $query->where('language_id', $language_id);
                        });
                        return $query;
                    });
                })

            //TODO Filtrar por experiencia
            ->when(($experiences != null and $experiences != 'all'), function ($query) use ($experiences) {
                return $query->whereHas('experience', function ($query) use ($experiences) {
                    $query->whereIn('experience_id', $experiences);
                });
            })
            //TODO Filtrar por tipo de servicio
            ->when(($type != null and $type != 'all'), function ($query) use ($type) {
                return $query->whereIn('service_type_id', $type);
            })
            //TODO Filtrar por clasificacion
            ->when(($classification != null and $classification != 'all'), function ($query) use ($classification) {
                return $query->whereIn('classification_id', $classification);
            })
            //TODO Filtrar por categoria
            ->when(($sub_category != null and $sub_category != 'all'), function ($query) use ($sub_category) {
                return $query->whereIn('service_sub_category_id', $sub_category);
            })
            //TODO si se buscan niños se excluyen los servicios que no admintan niños y que permitan la edad minima
            ->when($child_min_age_search != null, function ($query) {
//                return $query->where('allow_child', 1)->where('allow_infant', 1);
            })
            //TODO filtrar Origen -> pais
            ->when(!empty($origin_country_iso), function ($query) use ($origin_country_iso) {
                return $query->whereHas('serviceOrigin', function ($query) use ($origin_country_iso) {
                    $query->where('country_id', $origin_country_iso);
                });
            })
            ->when(empty($origin_country_iso), function ($query) use ($countries) {
                if (count($countries) > 0) {
                    return $query->whereHas('serviceOrigin', function ($query) use ($countries) {
                        $query->whereIn('country_id', $countries);
                    });
                }
            })
            //TODO filtrar Origen -> estado
            ->when(!empty($origin_state_iso), function ($query) use ($origin_state_iso) {
                return $query->whereHas('serviceOrigin', function ($query) use ($origin_state_iso) {
                    $query->where('state_id', $origin_state_iso);
                });
            })
            //TODO filtrar Origen -> ciudad
            ->when(!empty($origin_city_id), function ($query) use ($origin_city_id) {
                return $query->whereHas('serviceOrigin', function ($query) use ($origin_city_id) {
                    $query->where('city_id', $origin_city_id);
                });
            })
            //TODO filtrar Origen -> zona
            ->when(!empty($origin_zone_id), function ($query) use ($origin_zone_id) {
                return $query->whereHas('serviceOrigin', function ($query) use ($origin_zone_id) {
                    $query->where('zone_id', $origin_zone_id);
                });
            })
            //TODO filtrar Destino -> pais
            ->when(!empty($destiny_country_iso), function ($query) use ($destiny_country_iso) {
                return $query->whereHas('serviceDestination', function ($query) use ($destiny_country_iso) {
                    $query->where('country_id', $destiny_country_iso);
                });
            })
            //TODO filtrar Destino -> estado
            ->when(!empty($destiny_state_iso), function ($query) use ($destiny_state_iso) {
                return $query->whereHas('serviceDestination', function ($query) use ($destiny_state_iso) {
                    $query->where('state_id', $destiny_state_iso);
                });
            })
            //TODO filtrar Destino -> ciudad
            ->when(!empty($destiny_city_id), function ($query) use ($destiny_city_id) {
                return $query->whereHas('serviceDestination', function ($query) use ($destiny_city_id) {
                    $query->where('city_id', $destiny_city_id);
                });
            })
            //TODO filtrar Destino -> zona
            ->when(!empty($destiny_zone_id), function ($query) use ($destiny_zone_id) {
                return $query->whereHas('serviceDestination', function ($query) use ($destiny_zone_id) {
                    $query->where('zone_id', $destiny_zone_id);
                });
            })


            //TODO filtrar por la valoracion
            ->when($recommendations == true, function ($query) {
                return $query->whereHas('rated', function ($query) {
                    $query->where('rated', '>', 0);
                    $query->where('client_id', $this->client_id());
                    $query->orderBy('rated', 'desc');
                });
            })

            //TODO filtrar por los servicios que sean de compensacion
            ->when($compensation == true, function ($query) use ($compensation) {
                return $query->where('compensation', $compensation);
            })

            //TODO verifica Que tenga tarifa y en plan de tarifa y que la tarifa tenga inventario
            ->when(($filtro_miselaneo == false), function ($query) use ($date_to, $totalPax, $period, $lang_iso) {

                return $query->whereHas('service_rate', function ($query) use ($date_to, $totalPax, $period, $lang_iso) {
                    $query->where('status', 1);
                    $query->whereDoesntHave('clients_rate_plan', function ($query) use ($period) {
                        $query->where('client_id', $this->client_id());
                        $query->where('period', $period);
                    });
                    $query->whereHas('service_rate_plans', function ($query) use ($date_to, $totalPax) {
                        $query->where('date_from', '<=', $date_to)
                            ->where('date_to', '>=', $date_to)
                            ->where('pax_from', '<=', $totalPax)
                            ->where('pax_to', '>=', $totalPax)
                            ->where('status', 1);
                    });
                });
            })

            // Excluir los servicios que no se quieren trabajar
            ->whereDoesntHave('service_clients', function ($query) use ($period) {
                $query->where('client_id', $this->client_id());
                $query->where('period', $period);
            })


            //Todo Si el cliente no tiene activo el campo ecommerce entonces no mostramos los servicios creados por clientes
            ->when(($this->_client->ecommerce == 0), function ($query) {
                $query->whereDoesntHave('client_services');
            });
        return $query_search;
    }

    public function getDestinationServicesCountByState($service_clients)
    {
        $service_destinations = DB::table('service_destinations as d')
            ->join('translations as t', 't.object_id', '=', 'd.state_id')
            ->where('t.language_id', 1)
            ->where('t.type', 'state')
            ->where('t.slug', 'state_name')
            ->whereIn('d.service_id', $service_clients)
            ->whereNull('d.deleted_at')
            ->select('d.state_id as state_id', 'd.country_id as country_id', 't.value as state',
                DB::raw('count(d.state_id) as service_total'))
            ->groupBy('d.state_id')
            ->get();
        $ids = $service_destinations->pluck('state_id');
        $galeries = Galery::whereIn('object_id', $ids)
            ->where('slug', 'state_gallery')
            ->where('type', 'state')
            ->where('position', 1)
            ->where('state', 1)
            ->whereNull('deleted_at')
            ->get(['object_id', 'url']);
        $service_destinations = $service_destinations->transform(function ($item) use ($galeries) {
            $img = $galeries->first(function ($value) use ($item) {
                return $value->object_id == $item->state_id;
            });
            if ($img) {
                $states = [
                    'country_id' => $item->country_id,
                    'state_id' => $item->state_id,
                    'state' => $item->state,
                    'service_total' => $item->service_total,
                    'image' => $img->url,
                ];
            } else {
                $states = [
                    'country_id' => $item->country_id,
                    'state_id' => $item->state_id,
                    'state' => $item->state,
                    'service_total' => $item->service_total,
                    'image' => '',
                ];
            }
            return $states;
        });

        return $service_destinations;
    }

    public function getDestinationPackageCountByState($package_clients)
    {

        $package_destinations = DB::table('package_destinations as d')
            ->join('translations as t', 't.object_id', '=', 'd.state_id')
            ->join('states as s', 's.id', '=', 'd.state_id')
            ->join('countries as c', 'c.id', '=', 's.country_id')
            ->where('t.language_id', 1)
            ->where('t.type', 'state')
            ->where('t.slug', 'state_name')
            ->whereIn('d.package_id', $package_clients)
            ->select('c.id as country_id', 'd.state_id as state_id', 't.value as state',
                DB::raw('count(d.state_id) as package_total'))
            ->groupBy('d.state_id')
            ->get();
        $ids = $package_destinations->pluck('state_id');
        $galeries = Galery::whereIn('object_id', $ids)
            ->where('slug', 'state_gallery')
            ->where('type', 'state')
            ->where('position', 1)
            ->where('state', 1)
            ->whereNull('deleted_at')
            ->get(['object_id', 'url']);

        $package_destinations = $package_destinations->transform(function ($item) use ($galeries) {
            $img = $galeries->first(function ($value) use ($item) {
                return $value->object_id == $item->state_id;
            });
            if ($img) {
                $states = [
                    'country_id' => $item->country_id,
                    'state_id' => $item->state_id,
                    'state' => $item->state,
                    'package_total' => $item->package_total,
                    'image' => $img->url,
                ];
            } else {
                $states = [
                    'country_id' => $item->country_id,
                    'state_id' => $item->state_id,
                    'state' => $item->state,
                    'package_total' => $item->package_total,
                    'image' => '',
                ];
            }
            return $states;
        });


        return $package_destinations;
    }

    public function getFieldPriceByTotalPax($paxTotal, $type_service)
    {
        $field = '';
        $paxRange = $paxTotal;
        $cant = 1;
        if ($type_service == 1) { // Compartido
            if ($paxTotal >= 1 and $paxTotal <= 3) { //pax en base simple,doble,triple
                $field = $this->type_pax[$paxTotal];
            } else { // si es mayor a 3 en base doble
                $paxRange = 2;
                $field = $this->type_pax[2];
                $cant = $paxTotal;
            }
        } elseif ($type_service == 2) { //Privado
            if ($paxTotal >= 1 and $paxTotal <= 3) {  //pax en base simple,doble,triple
                $field = $this->type_pax[$paxTotal];
            } elseif ($paxTotal > 3) {// pax en base simple
                $paxRange = 2;
                $field = $this->type_pax[2];
                $cant = $paxTotal;
            }
        }

        $response = [
            'field' => $field,
            'range_pax' => $paxRange,
            'cant' => $cant
        ];

        return $response;
    }

    public function getPackagesClient(
        $client,
        $destiny,
        $type_package,
        $language,
        $type_service,
        $date_from,
        $type_class,
        $adult,
        $child,
        $child_with_bed,
        $tags,
        $filter = '',
        $days = 0,
        $recommendations = false,
        $only_recommended = false,
        $package_id = null,
        $limit = 0,
        $groups = [],
        $gtm = false
    ) {
        // 1. Cachear el año para evitar múltiples llamadas a Carbon
        $year = Carbon::parse($date_from)->year;
        $dateFormatted = Carbon::parse($date_from)->format("Y-m-d");
        $currentDate = Carbon::now()->format('Y-m-d');

        // 2. Consulta base optimizada
        $packages = Package::query()
            ->select([
                'id', 'country_id', 'code', 'extension', 'nights',
                'portada_link', 'map_link', 'map_itinerary_link',
                'image_link', 'status', 'reference', 'rate_type',
                'rate_dynamic', 'allow_guide', 'allow_child', 'allow_infant',
                'limit_confirmation_hours', 'infant_min_age', 'infant_max_age',
                'infant_discount_rate', 'physical_intensity_id', 'tag_id',
                'allow_modify', 'free_sale', 'recommended', 'destinations'
            ])
            ->where('status', 1)
            ->whereIn('extension', $type_package);

        $this->applyBaseFilters($packages, [
            'child' => $child,
            'only_recommended' => $only_recommended,
            'days' => $days,
            'destiny' => $destiny,
            'tags' => $tags,
            'groups' => $groups,
            'recommendations' => $recommendations,
            'package_id' => $package_id,
            'limit' => $limit,
            'client' => $client,
        ]);

        // Filtro crítico de plan_rates
        $packages->whereHas('plan_rates', function ($query) use (
            $client, $type_service, $type_class, $dateFormatted
        ) {
            $this->applyPlanRatesFilter($query, $client, $type_service, $type_class, $dateFormatted);
        });

        // Eager loading COMPLETO
        $packages->with($this->getFullEagerLoading(
            $language, $dateFormatted, $year, $currentDate,
            $client, $type_service, $type_class
        ));

        return $packages->get();
    }


    public function getDestinationPackageState($package_clients)
    {

        $package_destinations = DB::table('package_destinations as d')
            ->join('states as s', 'd.state_id', '=', 's.id')
            ->join('countries as c', 's.country_id', '=', 'c.id')
            ->join('translations as tc', 'tc.object_id', '=', 's.country_id')
            ->join('translations as t', 't.object_id', '=', 'd.state_id')
            ->where('t.language_id', 1)
            ->where('t.type', 'state')
            ->where('t.slug', 'state_name')
            ->whereIn('d.package_id', $package_clients)
            ->select('d.state_id as state_id', 't.value as state', 's.country_id as country_id', 'tc.value as country',
                DB::raw('count(d.state_id) as package_total'))
            ->groupBy('d.state_id')
            ->get();
        $package_destinations = $package_destinations->transform(function ($item) {
            return [
                'code' => $item->country_id . ',' . $item->state_id,
                'label' => $item->state,
            ];
        });


        return $package_destinations;
    }

    public function getDestinationPackageClientState($package_clients, $lang_id)
    {

        $package_destinations = PackageDestination::whereIn('package_id', $package_clients)
            ->with([
                'state' => function ($query) use ($lang_id) {
                    $query->select(['id', 'country_id', 'iso']);
                    $query->with(['country.translations' => function ($query) use ($lang_id) {
                        $query->select(['id', 'value', 'object_id']);
                            $query->where('language_id', $lang_id);
                            $query->where('type', 'country');
                            $query->where('slug', 'country_name');
                    }]);
                    $query->with([
                        'translations' => function ($query) use ($lang_id) {
                            $query->select(['id', 'value', 'object_id']);
                            $query->where('language_id', $lang_id);
                            $query->where('type', 'state');
                            $query->where('slug', 'state_name');
                        }
                    ]);
                }
            ])
            ->get(['id', 'package_id', 'state_id']);
        $package_destinations = $package_destinations->transform(function ($item) {
            return [
                'code' => optional($item->state)->id,
                'country' => (isset($item->state->country->translations)) ? $item->state->country->translations[0]['value'] : 'NULL',
                'label' => (isset($item->state->translations)) ? $item->state->translations[0]['value'] : 'NULL',
            ];
        });
        $package_destinations = $package_destinations->sortBy('label');
        $package_destinations = $package_destinations->unique('code')->values();
        return $package_destinations;
    }

    public function getDaysPackage($package_clients)
    {
        $package_days = Package::whereIn('extension', [0, 2])->where('status', 1)->whereIn('id',
            $package_clients)->orderBy('nights')->get([
            'nights'
        ]);
        $package_days = $package_days->unique('nights');
        $package_days = $package_days->transform(function ($item) {
            return [
                'day' => ($item->nights > 1) ? $item->nights + 1 : $item->nights,
            ];
        });

        return $package_days->values();
    }

    public function getInterestsPackage($package_clients, $language_id = 1)
    {
        $package_interests = Package::whereIn('extension', [0, 1, 2])->where('status', 1)
            ->whereIn('id', $package_clients)
            ->with([
                'tag' => function ($query) use ($language_id) {
                    $query->select(['id', 'tag_group_id', 'color']);
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select(['id', 'value', 'object_id']);
                            $query->where('language_id', $language_id);
                            $query->where('type', 'tag');
                            $query->where('slug', 'tag_name');
                        }
                    ]);
                },
                'tag.tag_group' => function ($query) use ($language_id) {
                    $query->with([
                        'translations' => function ($query) use ($language_id) {
                            $query->select(['id', 'value', 'object_id']);
                            $query->where('language_id', $language_id);
                            $query->where('type', 'taggroup');
                            $query->where('slug', 'group_name');
                        }
                    ]);
                }])
            ->orderBy('tag_id')
            ->get(['id', 'tag_id']);

        $package_interests = $package_interests->transform(function ($item) {
            return [
                'code' => $item->tag_id,
                'label' => ucwords(mb_strtolower($item->tag->translations[0]['value'])),
                'group_code' => $item->tag->tag_group->id,
                'group_label' => ucwords(mb_strtolower($item->tag->tag_group->translations[0]['value'])),
            ];
        });
        $package_interests = $package_interests->unique('code')->values();
        return $package_interests;

    }

    public function getDistinctServicesDestinations()
    {
        $services_ids = Service::whereDoesntHave('service_clients', function ($query) {
            $query->where('client_id', $this->client_id());
            $query->where('period', date('Y'));
        })->get(['id'])->pluck('id');
        $services = ServiceDestination::select(['country_id', 'state_id', 'city_id', 'zone_id']);
        $services = $services->whereIn('service_id', $services_ids)->distinct()->get();

        return $services;
    }

    /**
     * @param $client_id
     * @param null $executive_id
     * @return mixed
     */
    public function getClientExecutives($client_id, $executive_id = null)
    {
        return ClientExecutive::where('client_id', $client_id)
            ->where('status', 1)
            ->where('use_email_reserve', 1)
            ->with([
                'user' => function ($query) use ($executive_id) {
                    $query->select(['id']);
                    if (!empty($executive_id)) {
                        $query->where('id', $executive_id);
                    }
                    $query->where('user_type_id', 3);
                }
            ])
            ->whereHas('user', function ($query) use ($executive_id) {
                if (!empty($executive_id)) {
                    $query->where('id', $executive_id);
                }
                $query->where('user_type_id', 3);
            })
            ->first();
    }

    public function getCountryRegionClient($client_id){
        $client = Client::with('businessRegions.countries')->find($client_id);

        $formattedData = $client->businessRegions->flatMap(function ($region) {
            return $region->countries->map(function ($country) use ($region) {
                return [
                    'country_id' => $country->id,
                    'iso' => $country->iso,
                    'business_region_id' => $region->id
                ];
            });
        })->all();

        return $formattedData;
    }

    public function getClientPackageDefaultId(): ?int {
        return Client::where('code','CLDEPA')->first()->id ?? null;
    }

    public function getPackagesLight(
        $client,
        $destiny,
        $type_package,
        $language,
        $type_service,
        $date_from,
        $type_class,
        $adult,
        $child,
        $child_with_bed,
        $tags,
        $filter = '',
        $days = 0,
        $recommendations = false,
        $only_recommended = false,
        $package_id = null,
        $limit = 0,
        $groups = [],
        $gtm = false
    ) {
        // 1. Cachear el año para evitar múltiples llamadas a Carbon
        $year = Carbon::parse($date_from)->year;
        $dateFormatted = Carbon::parse($date_from)->format("Y-m-d");
        $currentDate = Carbon::now()->format('Y-m-d');

        $packages = Package::query()
            ->select([
                'id', 'country_id', 'code', 'nights',
                'portada_link', 'tag_id',
                'destinations', 'physical_intensity_id'
            ])
            ->where('status', 1)
            ->whereIn('extension', $type_package);

        // Aplicar filtros base
        $this->applyBaseFilters($packages, [
            'only_recommended' => $only_recommended,
            'days' => $days,
            'destiny' => $destiny,
            'tags' => $tags,
            'groups' => $groups,
            'package_id' => $package_id,
            'limit' => $limit,
        ]);

        // Eager loading LIGERO
        $packages->with($this->getLightEagerLoading(
            $language, $dateFormatted, $year, $currentDate,
            $client, $type_service, $type_class
        ));

        return $packages->get();
    }

    /**
     * Método auxiliar: Aplicar filtros base comunes
     */
    private function applyBaseFilters($query, $options = [])
    {
        // Child filter
        if (isset($options['child']) && $options['child'] > 0) {
            $query->where('allow_child', 1);
        }

        // Recommended
        if (!empty($options['only_recommended'])) {
            $query->where('recommended', 1);
        }

        // Days/Nights
        if (!empty($options['days']) && $options['days'] > 0) {
            $nights = ($options['days'] === 1) ? 1 : ($options['days'] - 1);
            $query->where('nights', $nights);
        }

        // Destinations
        if (!empty($options['destiny']) && count($options['destiny']) > 0) {
            $query->whereHas('package_destinations', function ($q) use ($options) {
                $q->whereIn('state_id', $options['destiny']);
            });
        }

        // Tags
        if (!empty($options['tags']) && count($options['tags']) > 0) {
            $query->whereIn('tag_id', $options['tags']);
        }

        // Tag Groups
        if (!empty($options['groups']) && count($options['groups']) > 0) {
            $query->whereHas('tag', function ($q) use ($options) {
                $q->whereIn('tag_group_id', $options['groups']);
            });
        }

        // Recommendations
        if (!empty($options['recommendations']) && isset($options['client'])) {
            $query->whereHas('rated', function ($q) use ($options) {
                $q->where('rated', '>', 0)
                    ->where('client_id', $options['client']->id);
            });
        }

        // Specific Package IDs
        if (!empty($options['package_id']) && count($options['package_id']) > 0) {
            $ids_ordered = implode(',', $options['package_id']);
            $query->whereIn('id', $options['package_id'])
                ->orderByRaw("FIELD(id, $ids_ordered)");
        } elseif (!empty($options['only_recommended'])) {
            $query->inRandomOrder();
        }

        // Limit
        if (!empty($options['limit']) && $options['limit'] > 0) {
            $query->limit($options['limit']);
        }
    }

    /**
     * Método auxiliar: Filtro de plan_rates
     */
    private function applyPlanRatesFilter($query, $client, $type_service, $type_class, $dateFormatted)
    {
        $query->where('status', 1)
            ->where('date_from', '<=', $dateFormatted)
            ->where('date_to', '>=', $dateFormatted);

        // Markup filter
        $query->where(function ($q) use ($client) {
            $q->whereHas('package_rate_sale_markup', function ($markup) use ($client) {
                $markup->where('status', 1)
                    ->where(function ($m) use ($client) {
                        $m->where(function ($client_q) use ($client) {
                            $client_q->where('seller_type', 'App\Client')
                                ->where('seller_id', $client->id);
                        })->orWhere(function ($market_q) use ($client) {
                            $market_q->where('seller_type', 'App\Market')
                                ->where('seller_id', $client->market_id);
                        });
                    });
            });
        });

        // Service type filter
        if ($type_service !== 'all') {
            $query->where('service_type_id', $type_service);
        } else {
            $query->whereIn('service_type_id', [1, 2]);
        }

        // Type class filter
        if ($type_class !== 'all') {
            $query->whereHas('plan_rate_categories', function ($q) use ($type_class) {
                $q->where('type_class_id', $type_class);
            });
        }
    }

    /**
     * Método auxiliar: Eager loading completo
     */
    private function getFullEagerLoading($language, $dateFormatted, $year, $currentDate, $client, $type_service, $type_class)
    {
        return [
            'package_destinations' => function ($query) use ($language) {
                $query->select(['package_id', 'state_id'])
                    ->with([
                        'state' => function ($q) use ($language) {
                            $q->select(['id', 'iso', 'country_id'])
                                ->with(['translations' => $this->translationClosure('state', $language)]);
                        }
                    ]);
            },

            'schedules' => function ($query) use ($dateFormatted) {
                $query->select([
                    'id', 'package_id', 'date_from', 'date_to',
                    'monday', 'tuesday', 'wednesday', 'thursday',
                    'friday', 'saturday', 'sunday', 'room', 'state'
                ])
                    ->where('date_from', '<=', $dateFormatted)
                    ->where('date_to', '>=', $dateFormatted);
            },

            'tag' => function ($query) use ($language) {
                $query->select(['id', 'color'])
                    ->with(['translations' => $this->translationClosure('tag', $language)]);
            },

            'translations' => function ($query) use ($language) {
                $query->select([
                    'package_id', 'name', 'tradename', 'label',
                    'description', 'description_commercial',
                    'itinerary_link', 'itinerary_link_commercial',
                    'itinerary_description', 'itinerary_commercial',
                    'inclusion', 'restriction', 'restriction_commercial',
                    'policies', 'policies_commercial'
                ])->where('language_id', $language->id);
            },

            'translations_gtm' => function ($query) {
                $query->select(['package_id', 'name', 'tradename'])
                    ->where('language_id', 2);
            },

            'itineraries' => function ($query) use ($language, $year) {
                $query->select([
                    'id', 'year', 'itinerary_link', 'itinerary_link_commercial',
                    'link_itinerary_priceless', 'package_id', 'language_id'
                ])
                    ->where('year', $year)
                    ->where('language_id', $language->id);
            },

            'itineraries_all' => function ($query) use ($language) {
                $query->select([
                    'id', 'year', 'itinerary_link', 'itinerary_link_commercial',
                    'link_itinerary_priceless', 'package_id', 'language_id'
                ])
                    ->where('year', '>=', date('Y'))
                    ->where('language_id', $language->id);
            },

            'extension_recommended:id,package_id,extension_id',

            'fixed_outputs' => function ($query) use ($currentDate) {
                $query->select('id', 'package_id', 'date', 'room')
                    ->where('date', '>=', $currentDate)
                    ->where('state', 1)
                    ->orderBy('date');
            },

            'galleries' => function ($query) {
                $query->select('object_id', 'slug', 'url', 'position', 'deleted_at')
                    ->where('type', 'package')
                    ->whereNull('deleted_at')
                    ->orderBy('position', 'ASC');
            },

            'rated' => function ($query) use ($client) {
                $query->select('id', 'rated', 'package_id')
                    ->where('client_id', $client->id);
            },

            'client_package_setting' => function ($query) use ($client) {
                $query->select('id', 'client_id', 'package_id', 'reservation_from', 'unit_duration_reserve')
                    ->where('client_id', $client->id);
            },

            'children' => function ($query) {
                $query->select(['id', 'package_id', 'min_age', 'max_age', 'has_bed'])
                    ->where('status', 1);
            },

            'highlights' => function ($query) use ($language) {
                $query->select(['id', 'package_id', 'image_highlight_id'])
                    ->with([
                        'highlights' => function ($q) use ($language) {
                            $q->select(['id', 'url'])
                                ->where('status', 1)
                                ->with([
                                    'translations' => $this->translationClosure('image_highlights', $language),
                                    'translations_content' => $this->translationClosure('image_highlights', $language)
                                ]);
                        }
                    ])
                    ->orderBy('order');
            },

            'plan_rates' => function ($query) use (
                $dateFormatted, $type_class, $type_service, $language, $client
            ) {
                $query->select([
                    'id', 'package_id', 'name', 'date_from', 'date_to',
                    'service_type_id', 'status', 'enable_fixed_prices'
                ])
                    ->where('status', 1)
                    ->where('date_from', '<=', $dateFormatted)
                    ->where('date_to', '>=', $dateFormatted);

                if ($type_service !== 'all') {
                    $query->where('service_type_id', $type_service);
                } else {
                    $query->whereIn('service_type_id', [1, 2]);
                }

                if ($type_class !== 'all') {
                    $query->whereHas('plan_rate_categories', function ($q) use ($type_class) {
                        $q->where('type_class_id', $type_class);
                    });
                }

                $query->with([
                    'package_rate_sale_markup' => function ($q) use ($client) {
                        $q->select(['id', 'seller_type', 'markup', 'package_plan_rate_id'])
                            ->where('seller_type', 'App\Client')
                            ->where('status', 1)
                            ->where('seller_id', $client->id);
                    },

                    'package_rate_sale_markup_market' => function ($q) use ($client) {
                        $q->select(['id', 'seller_type', 'markup', 'package_plan_rate_id'])
                            ->where('seller_type', 'App\Market')
                            ->where('status', 1)
                            ->where('seller_id', $client->market_id);
                    },

                    'service_type' => function ($q) use ($language) {
                        $q->select(['id', 'code'])
                            ->with(['translations' => function ($trans) use ($language) {
                                $trans->select(['object_id', 'value'])
                                    ->where('type', 'servicetype')
                                    ->where('slug', 'servicetype_name')
                                    ->where('language_id', $language->id);
                            }]);
                    },

                    'plan_rate_categories' => function ($q) use ($type_class, $language) {
                        $q->select(['id', 'package_plan_rate_id', 'type_class_id']);

                        if ($type_class !== 'all') {
                            $q->where('type_class_id', $type_class);
                        }

                        $q->with([
                            'category' => function ($cat) use ($language) {
                                $cat->with(['translations' => function ($trans) use ($language) {
                                    $trans->where('type', 'typeclass')
                                        ->where('language_id', $language->id);
                                }]);
                            },
                            'sale_rates_fixed:id,package_plan_rate_category_id,simple,double,triple,child_with_bed,child_without_bed'
                        ]);
                    },

                    'plan_rate_categories_all' => function ($q) use ($language) {
                        $q->select(['id', 'package_plan_rate_id', 'type_class_id'])
                            ->with([
                                'category' => function ($cat) use ($language) {
                                    $cat->with(['translations' => function ($trans) use ($language) {
                                        $trans->where('type', 'typeclass')
                                            ->where('language_id', $language->id);
                                    }]);
                                }
                            ]);
                    },

                    'offers' => function ($q) use ($dateFormatted, $client) {
                        $q->select([
                            'id', 'client_id', 'package_plan_rate_id',
                            'date_from', 'date_to', 'value', 'is_offer'
                        ])
                            ->where('date_from', '<=', $dateFormatted)
                            ->where('date_to', '>=', $dateFormatted)
                            ->where('client_id', $client->id)
                            ->where('status', 1);
                    }
                ]);
            }
        ];
    }

    private function getLightEagerLoading($language, $dateFormatted, $year, $currentDate, $client, $type_service, $type_class)
    {
        return [
            'package_destinations' => function ($query) use ($language) {
                $query->select(['package_id', 'state_id'])
                    ->with([
                        'state' => function ($q) use ($language) {
                            $q->select(['id', 'iso', 'country_id'])
                                ->with(['translations' => $this->translationClosure('state', $language)]);
                        }
                    ]);
            },

            'tag' => function ($query) use ($language) {
                $query->select(['id', 'color'])
                    ->with(['translations' => $this->translationClosure('tag', $language)]);
            },

            'translations' => function ($query) use ($language) {
                $query->select([
                    'package_id', 'name', 'tradename', 'label',
                    'description', 'description_commercial',
                    'itinerary_link', 'itinerary_link_commercial',
                    'itinerary_description', 'itinerary_commercial',
                    'inclusion', 'restriction', 'restriction_commercial',
                    'policies', 'policies_commercial'
                ])->where('language_id', $language->id);
            },

            'itineraries' => function ($query) use ($language, $year) {
                $query->select([
                    'id', 'year', 'itinerary_link', 'itinerary_link_commercial',
                    'link_itinerary_priceless', 'package_id', 'language_id'
                ])
                    ->where('year', $year)
                    ->where('language_id', $language->id);
            },

            'itineraries_all' => function ($query) use ($language) {
                $query->select([
                    'id', 'year', 'itinerary_link', 'itinerary_link_commercial',
                    'link_itinerary_priceless', 'package_id', 'language_id'
                ])
                    ->where('year', '>=', date('Y'))
                    ->where('language_id', $language->id);
            },


            'extension_recommended:id,package_id,extension_id',

            'fixed_outputs' => function ($query) use ($currentDate) {
                $query->select('id', 'package_id', 'date', 'room')
                    ->where('date', '>=', $currentDate)
                    ->where('state', 1)
                    ->orderBy('date');
            },

            'galleries' => function ($query) {
                $query->select('object_id', 'slug', 'url', 'position', 'deleted_at')
                    ->where('type', 'package')
                    ->whereNull('deleted_at')
                    ->orderBy('position', 'ASC');
            },

            'rated' => function ($query) use ($client) {
                $query->select('id', 'rated', 'package_id')
                    ->where('client_id', $client->id);
            },

            'client_package_setting' => function ($query) use ($client) {
                $query->select('id', 'client_id', 'package_id', 'reservation_from', 'unit_duration_reserve')
                    ->where('client_id', $client->id);
            },

            'children' => function ($query) {
                $query->select(['id', 'package_id', 'min_age', 'max_age', 'has_bed'])
                    ->where('status', 1);
            },

            'highlights' => function ($query) use ($language) {
                $query->select(['id', 'package_id', 'image_highlight_id'])
                    ->with([
                        'highlights' => function ($q) use ($language) {
                            $q->select(['id', 'url'])
                                ->where('status', 1)
                                ->with([
                                    'translations' => $this->translationClosure('image_highlights', $language),
                                    'translations_content' => $this->translationClosure('image_highlights', $language)
                                ]);
                        }
                    ])
                    ->orderBy('order');
            },

            'plan_rates' => function ($query) use (
                $dateFormatted, $type_class, $type_service, $language, $client
            ) {
                $query->select([
                    'id', 'package_id', 'name', 'date_from', 'date_to',
                    'service_type_id', 'status', 'enable_fixed_prices'
                ])
                    ->where('status', 1)
                    ->where('date_from', '<=', $dateFormatted)
                    ->where('date_to', '>=', $dateFormatted);

                if ($type_service !== 'all') {
                    $query->where('service_type_id', $type_service);
                } else {
                    $query->whereIn('service_type_id', [1, 2]);
                }

                if ($type_class !== 'all') {
                    $query->whereHas('plan_rate_categories', function ($q) use ($type_class) {
                        $q->where('type_class_id', $type_class);
                    });
                }

                $query->with([
                    'package_rate_sale_markup' => function ($q) use ($client) {
                        $q->select(['id', 'seller_type', 'markup', 'package_plan_rate_id'])
                            ->where('seller_type', 'App\Client')
                            ->where('status', 1)
                            ->where('seller_id', $client->id);
                    },

                    'package_rate_sale_markup_market' => function ($q) use ($client) {
                        $q->select(['id', 'seller_type', 'markup', 'package_plan_rate_id'])
                            ->where('seller_type', 'App\Market')
                            ->where('status', 1)
                            ->where('seller_id', $client->market_id);
                    },

                    'service_type' => function ($q) use ($language) {
                        $q->select(['id', 'code'])
                            ->with(['translations' => function ($trans) use ($language) {
                                $trans->select(['object_id', 'value'])
                                    ->where('type', 'servicetype')
                                    ->where('slug', 'servicetype_name')
                                    ->where('language_id', $language->id);
                            }]);
                    },

                    'plan_rate_categories' => function ($q) use ($type_class, $language) {
                        $q->select(['id', 'package_plan_rate_id', 'type_class_id']);

                        if ($type_class !== 'all') {
                            $q->where('type_class_id', $type_class);
                        }

                        $q->with([
                            'category' => function ($cat) use ($language) {
                                $cat->with(['translations' => function ($trans) use ($language) {
                                    $trans->where('type', 'typeclass')
                                        ->where('language_id', $language->id);
                                }]);
                            },
                            'sale_rates_fixed:id,package_plan_rate_category_id,simple,double,triple,child_with_bed,child_without_bed'
                        ]);
                    },

                    'plan_rate_categories_all' => function ($q) use ($language) {
                        $q->select(['id', 'package_plan_rate_id', 'type_class_id'])
                            ->with([
                                'category' => function ($cat) use ($language) {
                                    $cat->with(['translations' => function ($trans) use ($language) {
                                        $trans->where('type', 'typeclass')
                                            ->where('language_id', $language->id);
                                    }]);
                                }
                            ]);
                    },

                    'offers' => function ($q) use ($dateFormatted, $client) {
                        $q->select([
                            'id', 'client_id', 'package_plan_rate_id',
                            'date_from', 'date_to', 'value', 'is_offer'
                        ])
                            ->where('date_from', '<=', $dateFormatted)
                            ->where('date_to', '>=', $dateFormatted)
                            ->where('client_id', $client->id)
                            ->where('status', 1);
                    }
                ]);
            }
        ];
    }

    /**
     * Helper para closures de traducción (DRY)
     */
    private function translationClosure($type, $language)
    {
        return function ($query) use ($type, $language) {
            $query->select(['object_id', 'value'])
                ->where('type', $type)
                ->where('language_id', $language->id);
        };
    }
}

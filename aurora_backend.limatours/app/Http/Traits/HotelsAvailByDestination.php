<?php

namespace App\Http\Traits;

use App\HotelClient;
use Carbon\Carbon;

trait HotelsAvailByDestination
{
    /**
     * @param int $client_id
     * @param array $hotel_id
     * @param Carbon $check_in
     * @param Carbon $check_out
     * @param string $country_iso
     * @param string $state_iso
     * @param int|null $city_id
     * @param int|null $district_id
     * @param int|null $typeclass_id
     * @param array $rate_plan_room_ids_include
     * @return array
     */
    public function hotelsAvailByDestination(int $client_id, array $hotel_id, Carbon $check_in, Carbon $check_out, string $country_iso, string $state_iso, int $city_id = null, int $district_id = null, int $typeclass_id = null, array $rate_plan_room_ids_include = [])
    {
        $period = Carbon::parse($check_in)->year;
        $reservation_days = $check_in->diffInDays($check_out);

        $check_in = $check_in->format('Y-m-d');
        // Eliminamos un dia de la fecha de salida ya que este no se toma en cuenta por ser el dia que sale (no estara en el hotel)
        $pre_check_out = subDateDays($check_out, 1)->format('Y-m-d');
        $check_out = $check_out->format('Y-m-d');

        $hotels_client = HotelClient::select('client_id', 'hotel_id')
            ->where('client_id', $client_id)
            ->where('period', $period)
            ->whereIn('hotel_id', $hotel_id)
            ->whereHas('hotel', function ($query)
            use (
                $check_in,
                $pre_check_out,
                $reservation_days,
                $rate_plan_room_ids_include,
                $country_iso,
                $state_iso,
                $city_id,
                $district_id,
                $typeclass_id
            ) {
                $query->where('status', 1);

                if ($country_iso != "") {
                    $query->whereHas('country', function ($query) use ($country_iso) {
                        $query->where('iso', $country_iso);
                    });
                }

                if ($state_iso != "") {
                    $query->whereHas('state', function ($query) use ($state_iso) {
                        $query->where('iso', $state_iso);
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

                $query->whereHas('rooms', function ($query) use (
                    $check_in,
                    $pre_check_out,
                    $reservation_days,
                    $rate_plan_room_ids_include
                ) {
                    $query->where('state', 1);
                    $query->whereHas('rates_plan_room', function ($query) use (
                        $check_in,
                        $pre_check_out,
                        $reservation_days,
                        $rate_plan_room_ids_include
                    ) {
                        $query->whereIn('id', $rate_plan_room_ids_include);//lista de ids a incluir
                    });
                });
            })->with([
                'hotel' => function ($query)
                use ($check_in, $period, $pre_check_out, $reservation_days, $rate_plan_room_ids_include) {
                    $query->select('id', 'name', 'country_id', 'state_id', 'city_id', 'district_id', 'zone_id', 'hotel_type_id', 'typeclass_id', 'chain_id', 'latitude', 'longitude', 'stars', 'check_in_time', 'check_out_time', 'preferential');
                    $query->with([
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
                        'translations' => function ($query) {
                            $query->select('object_id', 'value', 'slug');
                            $query->where('type', 'hotel');
                            $query->where('language_id', 1);
                        },
                        'galeries' => function ($query) {
                            $query->select('object_id', 'slug', 'url');
                            $query->where('type', 'hotel');
                        },
                        'amenity' => function ($query) {
                            $query->where('status', 1);

                            $query->with([
                                'translations' => function ($query) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'amenity');
                                    $query->where('language_id', 1);
                                },
                            ]);
                            $query->with([
                                'galeries' => function ($query) {
                                    $query->select('object_id', 'url');
                                    $query->where('type', 'amenity');
                                },
                            ]);
                        },
                        'hoteltype' => function ($query) {
                            $query->select('id');
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select('object_id', 'value');
                                    $query->where('type', 'hoteltype');
                                    $query->where('language_id', 1);
                                },
                            ]);
                        },
                        'chain',
                        'channels' => function ($query) {
                            $query->wherePivot('state', '=', 1);
                            $query->wherePivot('code', '!=', '');
                            $query->wherePivot('code', '!=', 'null');
                        },
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
                    $query->with([
                        'rooms' => function ($query) use (
                            $check_in,
                            $period,
                            $pre_check_out,
                            $reservation_days,
                            $rate_plan_room_ids_include
                        ) {
                            $query->select('id', 'hotel_id', 'room_type_id', 'max_capacity', 'min_adults', 'max_adults', 'max_child', 'max_infants');

                            $query->where('state', 1);

                            $query->whereHas('rates_plan_room', function ($query) use (
                                $check_in,
                                $pre_check_out,
                                $reservation_days,
                                $rate_plan_room_ids_include
                            ) {
                                $query->whereIn('id', $rate_plan_room_ids_include);//lista de ids a incluir
                                $query->where('status', 1);
                            });

                            $query->with([
                                'galeries' => function ($query) {
                                    $query->select('object_id', 'url');
                                    $query->where('type', 'room');
                                },
                                'room_type' => function ($query) {
                                    $query->select('id');
                                    $query->with([
                                        'translations' => function ($query) {
                                            $query->select('object_id', 'value');
                                            $query->where('type', 'roomtype');
                                            $query->where('language_id', 1);
                                        },
                                    ]);
                                },
                                'translations' => function ($query) {
                                    $query->select('object_id', 'value', 'slug');
                                    $query->where('type', 'room');
                                    $query->where('language_id', 1);
                                },
                                'channels' => function ($query) {
                                    $query->wherePivot('state', '=', 1);
                                    $query->wherePivot('code', '!=', '');
                                    $query->wherePivot('code', '!=', 'null');
                                },
                                'rates_plan_room' => function ($query) use (
                                    $check_in,
                                    $period,
                                    $pre_check_out,
                                    $reservation_days,
                                    $rate_plan_room_ids_include
                                ) {
                                    $query->select('id', 'rates_plans_id', 'room_id', 'status', 'bag', 'channel_id');
                                    $query->whereIn('id', $rate_plan_room_ids_include);//lista de ids a incluir
                                    $query->with([
                                        'channel',
                                        'calendarys' => function ($query) use ($check_in, $pre_check_out) {
                                            $query->where('date', '>=', $check_in);
                                            $query->where('date', '<=', $pre_check_out);
                                            $query->with([
                                                'policies_rates' => function ($query) {
                                                    $query->with([
                                                        'policies_cancelation' => function ($query) {
                                                            $query->with(
                                                                [
                                                                    'policy_cancellation_parameter' => function ($query) {
                                                                        $query->with('penalty');
                                                                    },
                                                                ]
                                                            );
                                                        },
                                                    ]);
                                                },
                                                'rate'
                                            ]);
                                        },
                                        'rate_plan.meal.translations',
                                        'inventories' => function ($query) use ($check_in, $pre_check_out) {
                                            $query->where('date', '>=', $check_in);
                                            $query->where('date', '<=', $pre_check_out);
                                        },
                                        'markup' => function ($query) use ($period) {
                                            $query->where('period', $period);
                                        },
                                    ]);
                                },
                            ]);
                        },
                    ]);
                },
            ])->distinct()->get();

        return $hotels_client->toArray();
    }
}

<?php

namespace App\Http\Aurora\Hotels\Traits;

use App\Hotel;
use App\Markup;
use Carbon\Carbon;
use App\MarkupHotel;
use App\MarkupRatePlan;
use Illuminate\Support\Facades\Log;
use App\Http\Traits\ValidateHotelSearch;
use App\Http\Services\Traits\ClientHotelUtilTrait;
use App\Http\Multichannel\Hyperguest\Common\Constants\ChannelHyperguestConfig;

trait AvailabilityAuroraHotelTrait
{
    use ClientHotelUtilTrait;
    use ValidateHotelSearch;
    use AvailabilityHotelMapper;

    private $typeChannel = ChannelHyperguestConfig::TYPE_CHANNEL; // 1 = PUSH , 2 = PULL

    public function getClientHotelsAvailable(array $params = [])
    {
        [
            'client_id' => $client_id,
            'period' => $period,
            'date_from' => $date_from,
            'date_to' => $date_to, // Fecha de salida para HYPERGUEST
            'from' => $from, // Fecha de entrada
            'to' => $to, // Fecha de salida
            'reservation_days' => $reservation_days,
            'country_iso' => $country_iso,
            'state_iso' => $state_iso,
            'city_id' => $city_id,
            'district_id' => $district_id,
            'typeclass_id' => $typeclass_id,
            'hotels_id' => $hotels_id,
            'language_id' => $language_id,
            'hotels_search_code' => $hotels_search_code,
        ] = $params;

        $paramsQuery = [
            'client_id' => $client_id,
            'period' => $period,
            'date_from' => $date_from,
            'date_to' => $date_to, // Fecha de salida para HYPERGUEST
            'from' => $from, // Fecha de entrada
            'to' => $to, // Fecha de salida
            'reservation_days' => $reservation_days,
            'country_iso' => $country_iso,
            'state_iso' => $state_iso,
            'city_id' => $city_id,
            'district_id' => $district_id,
            'typeclass_id' => $typeclass_id,
            'hotels_id' => $hotels_id,
            'language_id' => $language_id,
            'hotels_search_code' => $hotels_search_code,
        ];
        $data_client_hotels = $this->queryClientHotels($paramsQuery);

        $paramsMarkUp = [
            'client_id' => $client_id,
            'period' => $period,
            'country_iso' => $country_iso,
        ];
        $client_markup = $this->loadClientMarkup($paramsMarkUp);

        $paramsHotelRooms = [
            'hotels_client' => $data_client_hotels,
            'client_id' => $client_id,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'client_markup' => $client_markup,
        ];
        $data_client_hotels = $this->transformHotelRooms($paramsHotelRooms);

        $client_hotels = $this->deepToArray($data_client_hotels->toArray());

        return $client_hotels;
    }

    private function queryClientHotels(array $params = [])
    {
        [
            'client_id' => $client_id,
            'period' => $period,
            'date_to' => $date_to, // Fecha de salida para HYPERGUEST
            'from' => $from, // Fecha de entrada
            'to' => $to, // Fecha de salida
            'reservation_days' => $reservation_days,
            'country_iso' => $country_iso,
            'state_iso' => $state_iso,
            'typeclass_id' => $typeclass_id,
            'hotels_id' => $hotels_id,
            'language_id' => $language_id,
            'hotels_search_code' => $hotels_search_code,
        ] = $params;

        $paramsQuery = [
            'client_id' => $client_id,
            'period' => $period,
            'hotels_search_code' => $hotels_search_code,
        ];
        $client_hotels_query = $this->buildHotelBaseQuery($paramsQuery);

        $paramsHotelFilters = [
            'query' => $client_hotels_query,
            'country_iso' => $country_iso,
            'state_iso' => $state_iso,
            'typeclass_id' => $typeclass_id,
            'from' => $from,
            'hotel_ids' => $hotels_id,
            'hotels_search_code' => $hotels_search_code,
        ];
        $client_hotels_query = $this->applyHotelFilters($paramsHotelFilters);


        $paramsHotelRelations = [
            'query' => $client_hotels_query,
            'from' => $from,
            'typeclass_id' => $typeclass_id,
            'language_id' => $language_id,
            'period' => $period,
            'client_id' => $client_id
        ];
        $client_hotels_query = $this->loadHotelRelations($paramsHotelRelations);

        $paramsRoomRelations = [
            'query' => $client_hotels_query,
            'from' => $from,
            'to' => $to,
            'date_to' => $date_to,
            'reservation_days' => $reservation_days,
            'period' => $period,
            'client_id' => $client_id,
            'language_id' => $language_id
        ];
        $client_hotels_query = $this->loadRoomRelations($paramsRoomRelations);

        // Log::info("Query de hoteles: ".$client_hotels_query->toSql());

        $data_client_hotels = $client_hotels_query->get();

        return $data_client_hotels;
    }

    private function buildHotelBaseQuery(array $params = [])
    {
        [
            'client_id' => $client_id,
            'period' => $period,
        ] = $params;

        return Hotel::select(
            'id',
            'name',
            'country_id',
            'state_id',
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
            'flag_new',
            'date_end_flag_new'
        )
            ->whereDoesntHave('hotelClients', function ($query) use ($client_id, $period) {
                $query->where('client_id', $client_id)
                    ->where('period', $period);
            })
            ->where('status', 1);
    }

    private function applyHotelFilters(array $params = [])
    {
        [
            'query' => $query,
            'hotel_ids' => $hotel_ids,
            'typeclass_id' => $typeclass_id,
            'from' => $from,
            'country_iso' => $country_iso,
            'state_iso' => $state_iso,
            'hotels_search_code' => $hotels_search_code,
        ] = $params;

        if (!empty($typeclass_id) && $typeclass_id != "all") {
            $query->whereHas('hoteltypeclass', function ($q) use ($typeclass_id, $from) {
                $q->where('typeclass_id', $typeclass_id)
                    ->where('year', Carbon::parse($from)->year);
            });
        }

        if (!empty($country_iso)) {
            $query->whereHas('country', function ($q) use ($country_iso) {
                $q->where('iso', $country_iso);
            });
        }

        if (!empty($state_iso)) {
            $query->where('state_id', $state_iso);
        }

        $query->whereHas('channels', function ($q) {
            $q->where('channel_hotel.type', 2);
        });

        $query->where(function ($q) {
            $q->whereHas('rooms', function ($r) {
                $r->where('state', 1);
            })->whereHas('rates_plans', function ($rp) {
                $rp->where('status', 1);
            });
        });

        if (!empty($hotels_search_code)) {
            $codes = explode(',', $hotels_search_code);

            $query->whereHas('channels', function ($query) use ($codes) {
                $query->whereIn('channels.id', [1, 6]);
                $query->whereIn('channel_hotel.code', $codes);
            });
            // $query->where(function ($query) use ($hotels_search_code) {
            //     $query->orWhere('name', 'like', '%' . $hotels_search_code . '%');

            //     $query->orWhereHas('channels', function ($query) use ($hotels_search_code) {
            //         $query->orWhereIn('channels.id', [1, 6]);
            //         $query->where('channel_hotel.code', '=', $hotels_search_code);
            //     });
            // });
        }

        return $query;
    }

    private function loadHotelRelations(array $params = [])
    {
        [
            'query' => $query,
            'from' => $from,
            'typeclass_id' => $typeclass_id,
            'language_id' => $language_id,
            'period' => $period,
            'client_id' => $client_id
        ] = $params;
        $year = Carbon::parse($from)->year;

        return $query
            ->with(['country.translations' => function ($q) use ($language_id) {
                $q->where('type', 'country')->where('language_id', $language_id);
            }])
            ->with(['state.translations' => function ($q) use ($language_id) {
                $q->where('type', 'state')->where('language_id', $language_id);
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
                $q->wherePivot('state', 1)->wherePivot('code', '!=', '')->wherePivot('code', '!=', 'null')->wherePivot('type', 2);
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
                $q->where('hotel_alerts.year', $period)
                    ->where(function ($q2) use ($language_id) {
                        $q2->where('hotel_alerts.language_id', 1)
                            ->orWhere('hotel_alerts.language_id', $language_id);
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

    private function loadRoomRelations(array $params = [])
    {
        [
            'query' => $query,
            'language_id' => $language_id
        ] = $params;

        return $query->with(['rooms' => function ($query) use ($language_id) {
            $query->select('id', 'hotel_id', 'room_type_id', 'max_capacity', 'min_adults', 'max_adults', 'max_child', 'max_infants');
            $query->where('state', 1);
            $query->with(['galeries' => function ($q) {
                $q->select('object_id', 'slug', 'url')->where('type', 'room')->where('state', 1);
            }]);
            $query->with(['channels' => function ($q) {
                $q->wherePivot('state', 1)->wherePivot('code', '!=', '')->wherePivot('code', '!=', 'null')->wherePivot('type', $this->typeChannel);
            }]);
            $query->with(['room_type.translations' => function ($q) use ($language_id) {
                $q->select('object_id', 'value')->where('type', 'roomtype')->where('language_id', $language_id);
            }]);
            $query->with(['translations' => function ($q) use ($language_id) {
                $q->select('object_id', 'value', 'slug')->where('type', 'room')->where('language_id', $language_id);
            }]);
        }]);
    }

    private function loadClientMarkup(array $params = []): ?float
    {
        [
            'client_id' => $client_id,
            'period' => $period,
            'country_iso' => $countryISO,
        ] = $params;

        $markupForRatePlan = MarkupRatePlan::where('client_id', $client_id)->where('period', $period)->value('markup');
        if (!empty($markupForRatePlan)) {
            return (float)$markupForRatePlan;
        }

        $markupHotel = MarkupHotel::where('client_id', $client_id)->where('period', $period)->value('markup');
        if (!empty($markupHotel)) {
            return (float)$markupHotel;
        }

        $markupBusinessRegion = Markup::select('hotel')->whereHas('businessRegion.countries', function ($query) use ($countryISO) {
            $query->where('iso', $countryISO);
        })->where(['client_id' => $client_id, 'period' => $period])->value('hotel');

        if (!empty($markupBusinessRegion)) {
            return (float)$markupBusinessRegion;
        }

        return null;
    }

    private function transformHotelRooms(array $params = [])
    {
        [
            'hotels_client' => $hotels_client,
            'client_id' => $client_id,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'client_markup' => $client_markup,
        ] = $params;

        $data = $hotels_client->transform(function (Hotel $hotel) use (
            $client_id,
            $date_from,
            $date_to,
            $client_markup
        ) {
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

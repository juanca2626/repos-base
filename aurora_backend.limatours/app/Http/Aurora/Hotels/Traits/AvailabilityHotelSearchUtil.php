<?php

namespace App\Http\Aurora\Hotels\Traits;

use App\City;
use App\State;
use App\Markup;
use App\Country;
use App\District;
use App\Language;
use App\MarkupHotel;
use RuntimeException;
use App\MarkupRatePlan;
use Illuminate\Http\Request;
use App\Http\Multichannel\Hyperguest\Common\Constants\ChannelHyperguestConfig;

trait AvailabilityHotelSearchUtil
{
    public function getParamsFromRequest(Request $request): array
    {
        $priceRange = $request->get('price_range');
        $hotels_id = (array) $request->get('hotels_id', []);
        $rate_plan_room_search = $request->get('rate_plan_room_search', []);
        $allow_children = $request->get('allow_children', false);
        $hotels_search_code = $request->get('hotels_search_code');

        if (!$request->has('destiny')) {
            $request->merge([
                'destiny' => ['code' => '', 'label' => '']
            ]);
        }

        $destiny = $request->get('destiny');
        $date_from = $request->get('date_from');
        $date_to = $request->get('date_to');
        $typeclass_id = $request->get('typeclass_id');
        $setMarkup = $request->get('set_markup', 0);
        $zeroRates = $request->get('zero_rates', false);
        $preferential = $request->get('preferential', false);
        $quantity_persons_rooms = $request->get('quantity_persons_rooms', []);
        $promotional_rate = (int) @$request->get('promotional_rate');
        $lang_iso = $request->get('lang', 'en');

        $language = Language::where('iso', $lang_iso)->where('state', 1)->first();
        if (!$language) {
            $lang_iso = 'en';
            $language = Language::where('iso', 'en')->where('state', 1)->first();
        }
        $language_id = $language->id;

        return compact(
            'priceRange',
            'hotels_id',
            'rate_plan_room_search',
            'allow_children',
            'hotels_search_code',
            'destiny',
            'date_from',
            'date_to',
            'typeclass_id',
            'setMarkup',
            'zeroRates',
            'preferential',
            'quantity_persons_rooms',
            'promotional_rate',
            'language',
            'language_id',
            'lang_iso'
        );
    }

    public function getDestinationLocationCodes(string $destiny_code, string $label_code): array
    {
        $destiny_codes = explode(",", $destiny_code);
        $destiny_labels = explode(",", $label_code);

        $state = ($destiny_codes[1] ?? "") === "" ? ($destiny_labels[1] ?? "") : $destiny_codes[1];
        $city = ($destiny_codes[2] ?? "") === "" ? ($destiny_labels[2] ?? "") : $destiny_codes[2];
        $district = ($destiny_codes[3] ?? "") === "" ? ($destiny_labels[3] ?? "") : $destiny_codes[3];

        $country_id = $destiny_codes[0] ?? "";
        $id_country = $this->getCountry($country_id);
        $state_id = $this->getState($state, $id_country); // validar por pais
        $city_id = $this->getCity($city, $state_id);
        $district_id = $this->getDistrict($district, $city_id);

        return [$country_id, $state_id, $city_id, $district_id];
    }

    public function applyMarkup(float $value, array $params = [])
    {
        $markup = $this->getMarkup($params);
        if ($markup === null) {
            throw new RuntimeException('El Markup del cliente no está definido.');
        }

        $rate = 1 + ($markup / 100);
        $valueWithMarkup = $value * $rate;

        // Redondeo consistente a 2 decimales (half-even)
        // $rounded = round($valueWithMarkup, 2, PHP_ROUND_HALF_EVEN);
        $rounded = roundLito($valueWithMarkup, 'hotel');
        return $rounded;
    }

    public function getMarkup(array $params = []): ?float
    {
        [
            'client_id' => $client_id,
            'period' => $period,
            'hotel' => $hotel,
            'rate_plan_id' => $rate_plan_id,
            'countryISO' => $countryISO,
            'set_markup' => $set_markup,
        ] = $params;

        if ($set_markup > 0) { // Todo Verifico primero si tiene un markup asignado de forma obligatoria (Ejm: Cotizador)
            $markup = $set_markup;
            return (float) $markup;
        } else {
            $markupForRatePlan = MarkupRatePlan::where('client_id', $client_id)->where('rate_plan_id', $rate_plan_id)->where('period', $period)->value('markup');
            if (!empty($markupForRatePlan)) {
                return (float) $markupForRatePlan;
            }

            $markupHotel = MarkupHotel::where('client_id', $client_id)->where('hotel_id', $hotel['id'])->where('period', $period)->value('markup');
            if (!empty($markupHotel)) {
                return (float) $markupHotel;
            }

            $markupBusinessRegion = Markup::select('hotel')->whereHas('businessRegion.countries', function ($query) use ($countryISO) {
                $query->where('iso', $countryISO);
            })->where(['client_id' => $client_id, 'period' => $period])->value('hotel');

            if (!empty($markupBusinessRegion)) {
                return (float) $markupBusinessRegion;
            }

            return null;
        }
    }

    public function getRoomOccupancy(array $quantity_persons_rooms): array
    {
        $occupancies = [];

        foreach ($quantity_persons_rooms as $room) {
            $adults = max(1, (int) ($room['adults'] ?? 0));

            // Solo hacer caso a niños si child es mayor que 0
            $flagChild = ((int) ($room['child'] ?? 0)) > 0;
            $ages = [];

            if ($flagChild) {
                // Si hay edades, contamos esos niños; si no, asumimos 1 niño
                $ages = $room['ages_child'] ?? [];
                $children = is_array($ages) ? count($ages) : 1;
                $children = max(1, (int) $children);
            } else {
                $children = 0;
            }

            $occupancies[] = [
                'adults' => $adults,
                'children' => $children,
                'ages_child' => ($children > 0) ? $ages : []
            ];
        }

        return [
            'adults' => array_sum(array_column($occupancies, 'adults')),
            'children' => array_sum(array_column($occupancies, 'children')),
            'ages_child' => $occupancies[0]['ages_child'],
            'rooms' => count($occupancies),
            'occupancies' => $occupancies,
        ];
    }

    public function getCountry(string $label): ?string
    {
        if ($label == "") {
            return "";
        }

        $country = Country::where('iso', $label)->first();

        return $country ? $country->id : "";
    }

    public function getState(string $label, int $country_id): ?string
    {
        if ($label == "") {
            return "";
        }

        $state = State::where('iso', $label)->where('country_id', $country_id)->first();

        if (!$state) {
            $state = State::with('translations')
                ->where('country_id', $country_id)
                ->whereHas('translations', function ($query) use ($label) {
                    $query->where('value', 'like', '%' . $label . '%');
                })
                ->first();
        }

        return $state ? $state->id : "";
    }

    public function getCity(string $label, ?string $stateId): ?string
    {
        if ($label == "") {
            return "";
        }

        $city = City::where('iso', $label)
            ->where('state_id', $stateId)
            ->first();

        if (!$city) {
            $city = City::with('translations')
                ->where('state_id', $stateId)
                ->whereHas('translations', function ($query) use ($label) {
                    $query->where('value', 'like', '%' . $label . '%');
                })
                ->first();
        }

        return $city ? $city->id : "";
    }

    public function getDistrict(string $label, ?string $cityId): ?string
    {
        if ($label == "") {
            return "";
        }

        $district = District::where('iso', $label)
            ->where('city_id', $cityId)
            ->first();

        if (!$district) {
            $district = District::with('translations')
                ->where('city_id', $cityId)
                ->whereHas('translations', function ($query) use ($label) {
                    $query->where('value', 'like', '%' . $label . '%');
                })
                ->first();
        }

        return $district ? $district->id : "";
    }

    public function mapRoomGallery(array $room): array
    {
        // Galerías originales (puede no existir la clave)
        $galeries = $room['galeries'] ?? [];

        // Mapeo de URLs
        $galleryUrls = collect($galeries)
            ->map(function ($galerie) {
                return $galerie['url'] ?? null;
            })
            ->filter() // elimina nulls
            ->values()
            ->all();

        // Asignación final
        $room['gallery'] = $galleryUrls;

        return $room['gallery'];
    }

    public function findChannelRoom(array $channelRooms, array $room): ?array
    {
         // Si no existen channels definidos en el room
         $channels = $room['channels'] ?? [];

         if (empty($channels)) {
             return [];
         }

         // Buscar el channel con channel_id = 6 y type = 2
         $selectedChannel = collect($channels)->first(function ($channel) {
             return $channel['pivot']['channel_id'] == ChannelHyperguestConfig::CHANNEL_ID && $channel['pivot']['type'] == ChannelHyperguestConfig::TYPE_CHANNEL && !empty($channel['pivot']['code']);
         });

         // Si no existe un channel que cumpla la condición, no hay coincidencia
         if (!$selectedChannel) {
             return [];
         }

         $code = $selectedChannel['pivot']['code'];

         $channelRoom = collect($channelRooms)
            ->first(function ($channelRoom) use ($code) {
                $externalChannelRoomId = $channelRoom['roomCode'] ?? null;
                $exists = $externalChannelRoomId == $code;
                return $exists;
            });

        return $channelRoom ?: null;
    }

    public function generateRatePlanRoomId(): string
    {
        $time = microtime(true);
        $hash = abs(crc32($time));
        $numero = str_pad($hash % 100000, 5, "0", STR_PAD_LEFT);
        return $numero;
    }

    public function hasChannelRoom(array $room, array $channelRooms): bool
    {
        // Si no existen channels definidos en el room
        $channels = $room['channels'] ?? [];

        if (empty($channels)) {
            return false;
        }

        // Buscar el channel con channel_id = 6 y type = 2
        $selectedChannel = collect($channels)->first(function ($channel) {
            return $channel['pivot']['channel_id'] == ChannelHyperguestConfig::CHANNEL_ID && $channel['pivot']['type'] == ChannelHyperguestConfig::TYPE_CHANNEL && !empty($channel['pivot']['code']);
        });

        // Si no existe un channel que cumpla la condición, no hay coincidencia
        if (!$selectedChannel) {
            return false;
        }

        $code = $selectedChannel['pivot']['code'];

        return collect($channelRooms)->contains(function ($channelRoom) use ($code) {
            return isset($channelRoom['roomCode']) && $channelRoom['roomCode'] == $code;
        });
    }

    public function getBoardTypeDescription($code, $lang = 'en'): string
    {
        $boardTypes = [
            'RO' => [
                'en' => 'Room Only',
                'es' => 'Solo habitación',
                'pt' => 'Somente quarto',
            ],
            'BB' => [
                'en' => 'Bed & Breakfast',
                'es' => 'Alojamiento y desayuno',
                'pt' => 'Alojamento e café da manhã',
            ],
            'HB' => [
                'en' => 'Half Board',
                'es' => 'Media pensión',
                'pt' => 'Meia pensão',
            ],
            'FB' => [
                'en' => 'Full Board',
                'es' => 'Pensión completa',
                'pt' => 'Pensão completa',
            ],
            'AI' => [
                'en' => 'All Inclusive',
                'es' => 'Todo incluido',
                'pt' => 'Tudo incluído',
            ],
        ];

        $lang = strtolower($lang);
        if (!isset($boardTypes[$code])) {
            return "Unknown code: $code";
        }

        if (!isset($boardTypes[$code][$lang])) {
            $lang = 'en'; // fallback
        }

        return $boardTypes[$code][$lang];
    }

    function getDisplayTaxes(array $taxes): array
    {
        return array_values(array_filter($taxes, function ($tax) {
            // Admite tanto objeto como array asociativo
            $relation = is_object($tax) ? ($tax->relation ?? null) : ($tax['relation'] ?? null);
            return $relation === 'display';
        }))[0] ?? [];
    }

}

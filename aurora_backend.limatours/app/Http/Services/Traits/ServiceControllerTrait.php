<?php

namespace App\Http\Services\Traits;

use App\Room;
use DateTime;
use App\Hotel;
use App\Rates;
use Carbon\Carbon;
use App\BusinessRegion;
use App\BusinessRegionsCountry;
use App\HotelTypeClass;
use App\RatesPlansRooms;
use Faker\Factory as Faker;
use App\RatesPlansCalendarys;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

trait ServiceControllerTrait
{
    // Mostrar solo tarifas de HYPERGUEST y tarifas de Aurora de manera controlada para busqueda de hoteles
    public function onlyRatesHyperguestAndAuroraHotels($rates): array
    {
        $ratesHyperguest = collect($rates)
            ->filter(function ($rate) {
                return $rate['rateProvider'] === 'HYPERGUEST';
            })
            ->values()
            ->toArray();

        $ratesAurora = collect($rates)
            ->filter(function ($rate) {
                return $rate['rateProvider'] === 'Aurora';
            })
            ->values()
            ->toArray();

        if (count($ratesHyperguest) == 0) {
            return $ratesAurora;
        }

        // Extraemos meal_id y rates_plans_type_id de $ratesHyperguest
        $hyperguestIdentifiers = collect($ratesHyperguest)->map(function ($rate) {
            return [
                'meal_id' => $rate['meal_id'],
                'rates_plans_type_id' => $rate['rates_plans_type_id'],
            ];
        })->toArray();

        // Filtramos $ratesAurora para obtener las entradas distintas
        $rateAuroraDistinctHyperguest = collect($ratesAurora)
            ->filter(function ($rate) use ($hyperguestIdentifiers) {
                return collect($hyperguestIdentifiers)->contains(
                    function ($identifier) use ($rate) {
                        return $identifier['meal_id'] === $rate['meal_id'] && $identifier['rates_plans_type_id'] !== $rate['rates_plans_type_id'];
                    }
                );
            })
            ->values()
            ->toArray();

        // Si no hay entradas distintas, devolvemos $ratesHyperguest completo
        return count($rateAuroraDistinctHyperguest) > 0
            ? array_merge($ratesHyperguest, $rateAuroraDistinctHyperguest)
            : $ratesHyperguest;
    }
    // Esta validacion, afecta a las tarifas de aurora que asociado al paquete, porque esta funcion cuando hay hyperguest solo te muestra esto y cuando hacen mach no lo encuentra y genera error
    // Mostrar solo tarifas de HYPERGUEST y tarifas de Aurora de manera controlada para busqueda de cotizaciones
    public function onlyRatesHyperguestAndAuroraQuotes($rooms): array
    {
        return collect($rooms)->map(function ($room) {
            // Filtrar las tarifas de "HYPERGUEST" con `onRequest = 1`
            $ratesHyperguest = collect($room['rates'])->filter(function ($rate) {
                return $rate['rateProvider'] === 'HYPERGUEST' && $rate['onRequest'] == 1;
            })->values()
            ->toArray();

            $ratesAurora = collect($room['rates'])->filter(function ($rate) {
                return $rate['rateProvider'] === 'Aurora';
            })
            ->values()
            ->toArray();

            if (count($ratesHyperguest) == 0) {
                 // Retornar la habitación con las tarifas combinadas
            $room['rates'] = $ratesAurora;
                return $room;
                // return $ratesAurora;
            }

            // Identificar combinaciones únicas de meal_id y rates_plans_type_id de "HYPERGUEST"
            $hyperguestIdentifiers = collect($ratesHyperguest)->map(function ($rate) {
                return [
                    'meal_id' => $rate['meal_id'],
                    'rates_plans_type_id' => $rate['rates_plans_type_id'],
                ];
            })->toArray();

            // Filtrar las tarifas de "Aurora" que no coinciden con los identificadores de "HYPERGUEST"
            // $rateAuroraDistinctHyperguest = collect($room['rates'])->filter(function ($rate) use ($hyperguestIdentifiers) {
            //     return $rate['rateProvider'] === 'Aurora' &&
            //         !collect($hyperguestIdentifiers)->contains(function ($identifier) use ($rate) {
            //             return $identifier['meal_id'] === $rate['meal_id'] &&
            //                 $identifier['rates_plans_type_id'] === $rate['rates_plans_type_id'];
            //         });
            // })->values()->toArray();
            $rateAuroraDistinctHyperguest = collect($ratesAurora)
            ->filter(function ($rate) use ($hyperguestIdentifiers) {
                return collect($hyperguestIdentifiers)->contains(
                    function ($identifier) use ($rate) {
                        return $identifier['meal_id'] === $rate['meal_id'] && $identifier['rates_plans_type_id'] !== $rate['rates_plans_type_id'];
                    }
                );
            })
            ->values()
            ->toArray();

            // Combinar las tarifas de HYPERGUEST y Aurora
            $combinedRates = array_merge($ratesHyperguest, $rateAuroraDistinctHyperguest);

            // Retornar la habitación con las tarifas combinadas
            $room['rates'] = $combinedRates;

            return $room;
        })->toArray();
    }

    /**
     * Generate a unique token for hotel search
     *
     * @return string
     */
    public function generateHotelSearchToken(): string
    {
        $faker = Faker::create();
        return $faker->unique()->uuid;
    }

    /**
     * Extract tokens from response data
     *
     * @param array $response
     * @return array
     */
    public function extractTokensFromResponse(array $response): array
    {
        $tokens = [];

        if (isset($response['data'][0]['city']['token_search'])) {
            $tokens['token_search'] = $response['data'][0]['city']['token_search'];
        }

        if (isset($response['data'][0]['city']['token_search_frontend'])) {
            $tokens['token_search_frontend'] = $response['data'][0]['city']['token_search_frontend'];
        }

        return $tokens;
    }

    /**
     * Merge data from both responses
     *
     * @param array $response1
     * @param array $response2
     * @return array
     */
    public function mergeResponseData(array $response1, array $response2): array
    {
        $mergedData = [];

        // Merge de los datos de hoteles
        if (isset($response1['data']) && isset($response2['data'])) {
            $mergedData = $response1['data'];

            // Si response2 tiene estructura similar, merge los hoteles
            if (isset($response2['data'][0]['city']['hotels'])) {
                $hotelsFromResponse1 = $mergedData[0]['city']['hotels'] ?? [];
                $hotelsFromResponse2 = $response2['data'][0]['city']['hotels'];

                // Combinar ambos arrays de hoteles
                $allHotels = array_merge($hotelsFromResponse1, $hotelsFromResponse2);

                // Agrupar hoteles por ID y consolidar sus rooms
                $grouped = $this->groupHotelsAndRooms($allHotels, 'rooms');

                // Consolidar hoteles: mantener el primer registro y agregar todas las rooms agrupadas y ordenadas
                $consolidatedHotels = [];
                foreach ($grouped['firstOccurrence'] as $hotelId => $firstHotel) {
                    $rooms = $grouped['grouped'][$hotelId] ?? [];
                    // Fusionar rooms con el mismo room_id antes de ordenar
                    $rooms = $this->mergeRoomsByRoomId($rooms);
                    $sortedRooms = $this->sortRoomsByBestPrice($rooms);
                    $firstHotel['rooms'] = $sortedRooms;
                    // Obtener el precio más bajo y el flag_migrate del hotel original
                    $priceInfo = $this->getLowestPriceAndFlagMigrate($allHotels, $sortedRooms, 'rooms');
                    $firstHotel['price'] = $priceInfo['price'];
                    // Actualizar flag_migrate si se encontró
                    if ($priceInfo['flag_migrate'] !== null) {
                        $firstHotel['flag_migrate'] = $priceInfo['flag_migrate'];
                    }
                    $consolidatedHotels[] = $firstHotel;
                }

                // Agregar hoteles sin ID al final
                $mergedData[0]['city']['hotels'] = array_merge($consolidatedHotels, $grouped['withoutId']);
            }

            // Merge de clases de hoteles si existen
            if (isset($response2['data'][0]['city']['class'])) {
                $classesFromResponse2 = $response2['data'][0]['city']['class'];
                $existingClasses = $mergedData[0]['city']['class'] ?? [];

                foreach ($classesFromResponse2 as $class) {
                    $exists = false;
                    foreach ($existingClasses as $existingClass) {
                        if ($existingClass['class_name'] === $class['class_name']) {
                            $exists = true;
                            break;
                        }
                    }
                    if (!$exists) {
                        $mergedData[0]['city']['class'][] = $class;
                    }
                }
            }

            // Merge de zonas si existen
            if (isset($response2['data'][0]['city']['zones'])) {
                $zonesFromResponse2 = $response2['data'][0]['city']['zones'];
                $existingZones = $mergedData[0]['city']['zones'] ?? [];

                foreach ($zonesFromResponse2 as $zone) {
                    $exists = false;
                    foreach ($existingZones as $existingZone) {
                        if ($existingZone['zone_name'] === $zone['zone_name']) {
                            $exists = true;
                            break;
                        }
                    }
                    if (!$exists) {
                        $mergedData[0]['city']['zones'][] = $zone;
                    }
                }
            }
        }

        return $mergedData;
    }

    /**
     * Get the lowest best_price from an array of rooms
     *
     * @param array $rooms
     * @return float
     */
    private function getLowestBestPrice(array $rooms): float
    {
        if (empty($rooms)) {
            return 0.00;
        }

        $prices = [];
        foreach ($rooms as $room) {
            $bestPrice = (float)($room['best_price'] ?? $room['bestPrice'] ?? 0.00);
            if ($bestPrice > 0.00) {
                $prices[] = $bestPrice;
            }
        }

        return !empty($prices) ? min($prices) : 0.00;
    }

    /**
     * Find the hotel with the lowest best_price and get its flag_migrate
     *
     * @param array $allHotels Array of all hotels from both responses
     * @param array $sortedRooms Sorted rooms array
     * @param string $roomsKey Key to access rooms ('rooms' or 'hotel.rooms')
     * @return array ['price' => float, 'flag_migrate' => mixed|null]
     */
    private function getLowestPriceAndFlagMigrate(array $allHotels, array $sortedRooms, string $roomsKey = 'rooms'): array
    {
        if (empty($sortedRooms)) {
            return ['price' => 0.00, 'flag_migrate' => null];
        }

        // Encontrar el precio más bajo
        $lowestPrice = $this->getLowestBestPrice($sortedRooms);

        if ($lowestPrice == 0.00) {
            return ['price' => 0.00, 'flag_migrate' => null];
        }

        // Buscar la room con el precio más bajo
        $roomWithLowestPrice = null;
        foreach ($sortedRooms as $room) {
            $bestPrice = (float)($room['best_price'] ?? $room['bestPrice'] ?? 0.00);
            if ($bestPrice == $lowestPrice && $bestPrice > 0.00) {
                $roomWithLowestPrice = $room;
                break;
            }
        }

        if (!$roomWithLowestPrice) {
            return ['price' => $lowestPrice, 'flag_migrate' => null];
        }

        // Buscar el hotel original que contiene esta room
        $flagMigrate = null;
        foreach ($allHotels as $hotel) {
            $hotelRooms = null;
            if ($roomsKey === 'hotel.rooms') {
                $hotelRooms = $hotel['hotel']['rooms'] ?? null;
            } else {
                if (isset($hotel['rooms']) && $hotel['rooms'] instanceof \Illuminate\Support\Collection) {
                    $hotelRooms = $hotel['rooms']->toArray();
                } else {
                    $hotelRooms = $hotel['rooms'] ?? null;
                }
            }

            if ($hotelRooms && is_array($hotelRooms)) {
                // Verificar si este hotel contiene la room con el precio más bajo
                foreach ($hotelRooms as $hotelRoom) {
                    $roomPrice = (float)($hotelRoom['best_price'] ?? $hotelRoom['bestPrice'] ?? 0.00);
                    if ($roomPrice == $lowestPrice && $roomPrice > 0.00) {
                        // Encontrar flag_migrate en el hotel
                        if ($roomsKey === 'hotel.rooms') {
                            $flagMigrate = $hotel['hotel']['flag_migrate'] ?? null;
                        } else {
                            $flagMigrate = $hotel['flag_migrate'] ?? null;
                        }
                        break 2; // Salir de ambos loops
                    }
                }
            }
        }

        return ['price' => $lowestPrice, 'flag_migrate' => $flagMigrate];
    }

    /**
     * Ordenar las habitaciones por mejor_precio (ascendente); las habitaciones con mejor_precio = 0,00 van al final.
     *
     * @param array $rooms
     * @return array
     */
    private function sortRoomsByBestPrice(array $rooms): array
    {
        if (empty($rooms)) {
            return $rooms;
        }

        // Separar rooms con best_price = 0.00 y las demás
        $roomsWithPrice = [];
        $roomsWithZeroPrice = [];

        foreach ($rooms as $room) {
            $bestPrice = (float)($room['best_price'] ?? $room['bestPrice'] ?? 0.00);

            if ($bestPrice == 0.00) {
                $roomsWithZeroPrice[] = $room;
            } else {
                $roomsWithPrice[] = $room;
            }
        }

        // Ordenar rooms con precio de menor a mayor
        usort($roomsWithPrice, function ($a, $b) {
            $priceA = (float)($a['best_price'] ?? $a['bestPrice'] ?? 0.00);
            $priceB = (float)($b['best_price'] ?? $b['bestPrice'] ?? 0.00);
            return $priceA <=> $priceB;
        });

        // Combinar: primero las ordenadas por precio, luego las de precio 0.00
        return array_merge($roomsWithPrice, $roomsWithZeroPrice);
    }

    /**
     * Fusionar rooms que comparten el mismo room_id combinando ciertas claves.
     *
     * Claves a fusionar cuando hay duplicados:
     *  - rates
     *  - rates_plan_room
     *  - tarifas_seleccionadas
     *  - token_serach_channel
     *  - channels
     *
     * @param array $rooms
     * @return array
     */
    private function mergeRoomsByRoomId(array $rooms): array
    {
        if (empty($rooms)) {
            return $rooms;
        }

        $roomsWithId = [];
        $roomsWithoutId = [];

        foreach ($rooms as $room) {
            $roomId = $room['room_id'] ?? $room['id'] ?? null;

            if ($roomId === null) {
                $roomsWithoutId[] = $room;
                continue;
            }

            $roomsWithId[$roomId][] = $room;
        }

        $merged = [];

        foreach ($roomsWithId as $roomId => $group) {
            // Si solo hay una room con este ID, no hay nada que fusionar
            if (count($group) === 1) {
                $merged[] = $group[0];
                continue;
            }

            $base = array_shift($group);

            foreach ($group as $other) {
                // Fusionar arrays específicos
                foreach (['rates','rates_plan_room', 'tarifas_seleccionadas', 'channels'] as $key) {
                    $baseValue = $base[$key] ?? [];
                    $otherValue = $other[$key] ?? [];

                    if (!is_array($baseValue)) {
                        $baseValue = $baseValue ? [$baseValue] : [];
                    }
                    if (!is_array($otherValue)) {
                        $otherValue = $otherValue ? [$otherValue] : [];
                    }

                    if (!empty($baseValue) || !empty($otherValue)) {
                        $mergedArray = array_merge($baseValue, $otherValue);
                        // Eliminar duplicados preservando estructura
                        $mergedArray = array_values(array_reduce($mergedArray, function ($carry, $item) {
                            $key = is_array($item) || is_object($item)
                                ? md5(json_encode($item))
                                : md5((string) $item);
                            $carry[$key] = $item;
                            return $carry;
                        }, []));

                        $base[$key] = $mergedArray;
                    }
                }

                // token_serach_channel: priorizar el primero no vacío
                $baseToken = $base['token_search_channel'] ?? null;
                $otherToken = $other['token_search_channel'] ?? null;

                if (empty($baseToken) && !empty($otherToken)) {
                    $base['token_search_channel'] = $otherToken;
                }
            }

            $merged[] = $base;
        }

        // Añadir rooms sin ID al resultado final sin modificar
        return array_merge($merged, $roomsWithoutId);
    }

    private function groupHotelsAndRooms(array $allHotels, string $roomsKey = 'rooms'): array
    {
        $hotelsGrouped = [];
        $firstOccurrence = [];
        $hotelsWithoutId = [];

        foreach ($allHotels as $hotel) {
            // Identificar el hotel por 'id' o 'hotel_id'
            // Si roomsKey es 'hotel.rooms', buscar el ID en hotel['hotel']['id']
            if ($roomsKey === 'hotel.rooms') {
                $hotelId = $hotel['hotel']['id'] ?? $hotel['hotel_id'] ?? null;
            } else {
                $hotelId = $hotel['id'] ?? $hotel['hotel_id'] ?? null;
            }

            if ($hotelId === null) {
                $hotelsWithoutId[] = $hotel;
                continue;
            }

            // Si es la primera vez que vemos este hotel, guardarlo como referencia
            if (!isset($firstOccurrence[$hotelId])) {
                $firstOccurrence[$hotelId] = $hotel;
                $hotelsGrouped[$hotelId] = [];
            }

            // Obtener las rooms según la clave especificada
            $rooms = null;
            if ($roomsKey === 'hotel.rooms') {
                $rooms = $hotel['hotel']['rooms'] ?? null;
            } else {
                // Convertir Collection a array si es necesario
                if (isset($hotel['rooms']) && $hotel['rooms'] instanceof \Illuminate\Support\Collection) {
                    $hotel['rooms'] = $hotel['rooms']->toArray();
                }
                $rooms = $hotel['rooms'] ?? null;
            }

            // Agrupar las rooms de este hotel
            if ($rooms && is_array($rooms)) {
                $hotelsGrouped[$hotelId] = array_merge($hotelsGrouped[$hotelId], $rooms);
            }
        }

        return [
            'grouped' => $hotelsGrouped,
            'firstOccurrence' => $firstOccurrence,
            'withoutId' => $hotelsWithoutId
        ];
    }

    /**
     * Obtener datos almacenados en caché utilizando tokens de la respuesta
     *
     * @param array $tokens
     * @return array
     */
    public function getCachedDataFromTokens(array $tokens): array
    {
        $backendData = [];
        $frontendData = [];

        if (isset($tokens['token_search']) && !empty($tokens['token_search'])) {
            $backendData = Cache::get($tokens['token_search']);
        }

        if (isset($tokens['token_search_frontend']) && !empty($tokens['token_search_frontend'])) {
            $frontendData = Cache::get($tokens['token_search_frontend']);
        }

        return [
            'backend' => $backendData,
            'frontend' => $frontendData
        ];
    }

    /**
     * Combinar los datos almacenados en caché de ambas respuestas
     *
     * @param array $cachedData1
     * @param array $cachedData2
     * @return array
     */
    public function mergeCachedData(array $cachedData1, array $cachedData2, string $type): array
    {
        $mergedData = [];

        if (!empty($cachedData1) && !empty($cachedData2)) {
            if($type == 'backend') {
                // Combinar ambos arrays
                $allHotels = array_merge($cachedData1, $cachedData2);

                // Agrupar hoteles por ID y consolidar sus rooms
                $grouped = $this->groupHotelsAndRooms($allHotels, 'hotel.rooms');

                // Consolidar hoteles: mantener el primer registro y agregar todas las rooms agrupadas y ordenadas
                foreach ($grouped['firstOccurrence'] as $hotelId => $firstHotel) {
                    $rooms = $grouped['grouped'][$hotelId] ?? [];
                    // Fusionar rooms con el mismo room_id antes de ordenar
                    $rooms = $this->mergeRoomsByRoomId($rooms);
                    $sortedRooms = $this->sortRoomsByBestPrice($rooms);
                    $firstHotel['hotel']['rooms'] = $sortedRooms;
                    // Obtener el precio más bajo y el flag_migrate del hotel original
                    $priceInfo = $this->getLowestPriceAndFlagMigrate($allHotels, $sortedRooms, 'hotel.rooms');
                    $firstHotel['hotel']['price'] = $priceInfo['price'];
                    // Actualizar flag_migrate si se encontró
                    if ($priceInfo['flag_migrate'] !== null) {
                        $firstHotel['hotel']['flag_migrate'] = $priceInfo['flag_migrate'];
                    }
                    $mergedData[] = $firstHotel;
                }

                // Agregar hoteles sin ID
                $mergedData = array_merge($mergedData, $grouped['withoutId']);
            } else {
                $allHotels = array_merge($cachedData1[0]['city']['hotels'], $cachedData2[0]['city']['hotels']);

                // Agrupar hoteles por ID y consolidar sus rooms
                $grouped = $this->groupHotelsAndRooms($allHotels, 'rooms');

                // Consolidar hoteles: mantener el primer registro y agregar todas las rooms agrupadas y ordenadas
                foreach ($grouped['firstOccurrence'] as $hotelId => $firstHotel) {
                    $rooms = $grouped['grouped'][$hotelId] ?? [];
                    // Fusionar rooms con el mismo room_id antes de ordenar
                    $rooms = $this->mergeRoomsByRoomId($rooms);
                    $sortedRooms = $this->sortRoomsByBestPrice($rooms);
                    $firstHotel['rooms'] = $sortedRooms;
                    // Obtener el precio más bajo y el flag_migrate del hotel original
                    $priceInfo = $this->getLowestPriceAndFlagMigrate($allHotels, $sortedRooms, 'rooms');
                    $firstHotel['price'] = $priceInfo['price'];
                    // Actualizar flag_migrate si se encontró
                    if ($priceInfo['flag_migrate'] !== null) {
                        $firstHotel['flag_migrate'] = $priceInfo['flag_migrate'];
                    }
                    $mergedData[] = $firstHotel;
                }

                // Agregar hoteles sin ID
                $mergedData = array_merge($mergedData, $grouped['withoutId']);
            }

        } elseif (!empty($cachedData1)) {
            $mergedData = $cachedData1;
        } elseif (!empty($cachedData2)) {
            $mergedData = $cachedData2;
        }

        return $mergedData;
    }

    /**
     * Combinar las respuestas de los hoteles procedentes de diferentes servicios
     *
     * @param array $response1
     * @param array $response2
     * @param int $expiration_search_hotels
     * @return array
     */
    public function mergeHotelResponses(array $response1, array $response2, int $expiration_search_hotels = 180): array
    {
        if ($response1['success'] && $response2['success']) {

            $tokensFromResponse1 = $this->extractTokensFromResponse($response1);
            $tokensFromResponse2 = $this->extractTokensFromResponse($response2);

            $cachedDataFromResponse1 = $this->getCachedDataFromTokens($tokensFromResponse1);
            $cachedDataFromResponse2 = $this->getCachedDataFromTokens($tokensFromResponse2);

            $cachedDataFromResponseBackend1 = $cachedDataFromResponse1['backend'];
            $cachedDataFromResponseBackend2 = $cachedDataFromResponse2['backend'];

            $cachedDataFromResponseFrontend1 = $cachedDataFromResponse1['frontend'];
            $cachedDataFromResponseFrontend2 = $cachedDataFromResponse2['frontend'];

            $mergedCachedDataBackend = $this->mergeCachedData($cachedDataFromResponseBackend1, $cachedDataFromResponseBackend2, 'backend');
            $mergedCachedDataFrontend = $this->mergeCachedData($cachedDataFromResponseFrontend1, $cachedDataFromResponseFrontend2, 'frontend');

            $mergedData = $this->mergeResponseData($response1, $response2);

            $unifiedToken = $this->generateHotelSearchToken();
            $unifiedTokenFrontend = $this->generateHotelSearchToken();

            $mergedData[0]['city']['token_search'] = $unifiedToken;
            $mergedData[0]['city']['token_search_frontend'] = $unifiedTokenFrontend;

            $this->storeTokenSearchHotelsTrait($unifiedToken, $mergedCachedDataBackend, $expiration_search_hotels);
            $this->storeTokenSearchHotelsTrait($unifiedTokenFrontend, $mergedCachedDataFrontend, $expiration_search_hotels);

            return [
                'success' => true,
                'data' => $mergedData,
                'expiration_token' => $expiration_search_hotels,
            ];
        }

        if ($response1['success']) {
            return $response1;
        }

        if ($response2['success']) {
            return $response2;
        }

        return $response1;
    }

    public function mergeAuroraAndHyperguestHotels(array $response1, array $response2): array {
        // Marcar las rooms del primer response con hyperguest_pull = false
        $response1Marked = array_map(function ($hotel) {
            if (isset($hotel['rooms']) && is_array($hotel['rooms'])) {
                $hotel['rooms'] = array_map(function ($room) {
                    $room['hyperguest_pull'] = false;
                    return $room;
                }, $hotel['rooms']);
            }
            return $hotel;
        }, $response1);

        // Marcar las rooms del segundo response con hyperguest_pull = true
        $response2Marked = array_map(function ($hotel) {
            if (isset($hotel['rooms']) && is_array($hotel['rooms'])) {
                $hotel['rooms'] = array_map(function ($room) {
                    $room['hyperguest_pull'] = true;
                    return $room;
                }, $hotel['rooms']);
            }
            return $hotel;
        }, $response2);

        // Combinar ambos arrays de hoteles
        $allHotels = array_merge($response1Marked, $response2Marked);

        // Agrupar hoteles por ID y consolidar sus rooms
        $grouped = $this->groupHotelsAndRooms($allHotels, 'rooms');

        // Consolidar hoteles: mantener el primer registro y agregar todas las rooms agrupadas y ordenadas
        $consolidatedHotels = [];
        foreach ($grouped['firstOccurrence'] as $hotelId => $firstHotel) {
            $rooms = $grouped['grouped'][$hotelId] ?? [];
            // Fusionar rooms con el mismo room_id antes de ordenar
            $rooms = $this->mergeRoomsByRoomId($rooms);
            $sortedRooms = $this->sortRoomsByBestPrice($rooms);
            $firstHotel['rooms'] = $sortedRooms;
            // Obtener el precio más bajo y el flag_migrate del hotel original
            $priceInfo = $this->getLowestPriceAndFlagMigrate($allHotels, $sortedRooms, 'rooms');
            $firstHotel['price'] = $priceInfo['price'];
            // Actualizar flag_migrate si se encontró
            if ($priceInfo['flag_migrate'] !== null) {
                $firstHotel['flag_migrate'] = $priceInfo['flag_migrate'];
            }
            $consolidatedHotels[] = $firstHotel;
        }

        // Agregar hoteles sin ID al final
        return array_merge($consolidatedHotels, $grouped['withoutId']);
    }

    public function storeTokenSearchHotelsTrait($token_search, $hotels, $minutes)
    {
        Cache::put($token_search, $hotels, now()->addMinutes($minutes));
    }

    /**
     * Extraer códigos de ubicación del parámetro de destino
     *
     * @param array $destiny
     * @return array
     */
    public function extractLocationCodes(?array $destiny): array
    {
        $destiny_code = $destiny['code'] ?? '';
        $destiny_codes = explode(",", $destiny_code);

        $country_id = "";
        $state_id = "";
        $city_id = "";
        $district_id = "";

        // Separar códigos de destino
        for ($i = 0; $i < count($destiny_codes); $i++) {
            if ($i == 0) {
                $country_id = $destiny_codes[$i];
            }
            if ($i == 1) {
                $state_id = $destiny_codes[$i];
            }
            if ($i == 2) {
                $city_id = $destiny_codes[$i];
            }
            if ($i == 3) {
                $district_id = $destiny_codes[$i];
            }
        }

        return [
            'country_id' => $country_id,
            'state_id' => $state_id,
            'city_id' => $city_id,
            'district_id' => $district_id
        ];
    }

    public function getClientBusinessRegion($client_id, $country_id)
    {
        return BusinessRegion::query()
            ->whereHas('clients', function ($q) use ($client_id) {
                $q->where('clients.id', $client_id);
            })
            ->whereHas('countries', function ($q) use ($country_id) {
                $q->where('countries.iso', $country_id);
            })
            ->first();
    }

    public function getBusinessRegion($country_id)
    {
        return BusinessRegionsCountry::with('business_region')
            ->whereHas('country', function ($q) use ($country_id) {
                $q->where('countries.iso', $country_id);
            })
            ->first();
    }

    public function processHyperguestHotelData(array $response, string $date_from, string $date_to, array $type_classes = []): array
    {
        if ($response['success']) {
            $data_hotels = $response['data'][0]['city']['hotels'] ?? [];
            $hotels = [];

            foreach ($data_hotels as $hotel) {
                // VALIDAMOS QUE EL HOTEL PERTENEZCA AL TIPO QUE SE ESTA BUSCANDO
                if (count($hotel['rooms']) > 0) {
                    $hotel = $this->updateHotelRatesDates($hotel, $date_from, $date_to);
                    $hotel['typeclass_id'] = $type_classes[0] ?? '';
                    $hotels[] = $hotel;
                }
            }

            $response['data'][0]['city']['hotels'] = $hotels;
        }

        return $response;
    }

    private function convertDateRange($startDate, $endDate) {
        $today = new DateTime();
        $today->setTime(0, 0, 0); // Normalizar hora a 00:00:00

        $originalStart = new DateTime($startDate);
        $originalStart->setTime(0, 0, 0);

        $originalEnd = new DateTime($endDate);
        $originalEnd->setTime(0, 0, 0);

        // Si la fecha de inicio es futura, mantener las fechas originales
        if ($originalStart > $today) {
            return [
                'new_start_date' => $originalStart->format('Y-m-d'),
                'new_end_date' => $originalEnd->format('Y-m-d'),
            ];
        }

        // Calcular duración
        $durationDays = $originalStart->diff($originalEnd)->days;

        // Generar nuevas fechas a partir de hoy
        $newStart = clone $today;
        $newStart->modify("+1 day");

        $newEnd = clone $newStart;
        $newEnd->modify("+$durationDays days");

        return [
            'new_start_date' => $newStart->format('Y-m-d'),
            'new_end_date' => $newEnd->format('Y-m-d'),
        ];
    }

    private function updateHotelRatesDates(&$hotelData, $newDateFrom, $newDateTo)
    {
        $startDate = new DateTime($newDateFrom);

        $currentDate = clone $startDate;

        if (!isset($hotelData['rooms'])) {
            return $hotelData;
        }

        foreach ($hotelData['rooms'] as &$room) {
            $tempDate = clone $currentDate;

            if (isset($room['rates']) && is_array($room['rates'])) {
                foreach ($room['rates'] as &$rate) {
                    for ($i = 0; $i < count($rate['amount_days'] ?? []); $i++) {
                        if (isset($rate['amount_days'][$i]['date'])) {
                            $rate['amount_days'][$i]['date'] = $tempDate->format('Y-m-d');
                            $rate['calendarys'][$i]['date'] = $tempDate->format('Y-m-d');
                        }
                        $tempDate->modify('+1 day');
                    }
                    $tempDate = clone $currentDate;
                }
            }

            if (isset($room['rates_plan_room']) && is_array($room['rates_plan_room'])) {
                foreach ($room['rates_plan_room'] as &$rate_plan_room) {
                    for ($i = 0; $i < count($rate_plan_room['amount_days'] ?? []); $i++) {
                        if (isset($rate_plan_room['amount_days'][$i]['date'])) {
                            $rate_plan_room['amount_days'][$i]['date'] = $tempDate->format('Y-m-d');
                            $rate_plan_room['calendarys'][$i]['date'] = $tempDate->format('Y-m-d');
                        }
                        $tempDate->modify('+1 day');
                    }
                    $tempDate = clone $currentDate;
                }
            }
        }

        return $hotelData;
    }

    private function buildHotelSearchQuery(?string $query_search = '', ?string $allWords = null, ?string $country_id, ?string $state_id, ?string $city_id, ?string $district_id, array $type_classes, string $from, int $hotel_id = null)
    {
        $hotels = Hotel::where('status', 1);

        if ($hotel_id) {
            $hotels->where('id', $hotel_id);
        }

        if ($query_search) {
            $filters = explode(',', $query_search);

            for ($i = 0; $i < count($filters); $i++) {
                $_filter = trim($filters[$i]);
                if ($_filter != '') {
                    if ($allWords == 1 || $i == 0) {
                        // AND
                        $hotels->where(function ($query) use ($_filter) {
                            $query->where('name', 'like', '%' . $_filter . '%');
                            $query->orWhereHas('channel', function ($q) use ($_filter) {
                                $q->where('code', 'like', '%' . $_filter . '%');
                            });
                        });
                    } else {
                        // OR
                        $hotels->orWhere(function ($query) use ($_filter) {
                            $query->where('name', 'like', '%' . $_filter . '%');
                            $query->orWhereHas('channel', function ($q) use ($_filter) {
                                $q->where('code', 'like', '%' . $_filter . '%');
                            });
                        });
                    }
                }
            }
        } else {
            $hotels = $hotels
                // filtrar destino
                ->when(!empty($country_id), function ($query) use ($country_id) {
                    return $query->whereHas('country', function ($query) use ($country_id) {
                        $query->where('iso', $country_id);
                    });
                })
                ->when(!empty($state_id), function ($query) use ($state_id) {
                    return $query->whereHas('state', function ($query) use ($state_id) {
                        $query->where('iso', $state_id);
                    });
                })
                ->when(!empty($city_id), function ($query) use ($city_id) {
                    return $query->where('city_id', $city_id);
                })
                ->when(!empty($district_id), function ($query) use ($district_id) {
                    return $query->where('district_id', $district_id);
                })
                ->when(!empty($type_classes) && is_array($type_classes) && $type_classes[0] != "all" && $type_classes[0] != null, function ($query) use ($from, $type_classes) {
                    return $query->whereHas('hoteltypeclass', function ($query) use ($from, $type_classes) {
                        $query->whereIn('typeclass_id', $type_classes);
                        $query->where('year', Carbon::parse($from)->year);
                    });
                });
                // Validacion para que no se traigan hoteles de hyperguest
                // ->when(true, function ($query) {
                //     return $query->whereHas('channel', function ($query) {
                //         $query->where('channel_id', 6)
                //             ->where(function ($subQuery) {
                //                 $subQuery->where('type', 1);  // type = 1
                //             })
                //             ->orWhere(function ($subQuery) {
                //                 $subQuery->where('channel_id', 6)
                //                         ->whereNull('type');  // type IS NULL
                //             });
                //     });
                // })
                // ;
        }

        return $hotels;
    }

    private function loadRelationHotel($hotels, $from, $to, $client_id, $period)
    {
        return $hotels->with([
            'channel',
        ])->with([
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
        ])->with([
            'state' => function ($query) {
                $query->select('id');
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
        ])->with([
            'translations' => function ($query) {
                $query->select('object_id', 'value', 'slug');
                $query->where('type', 'hotel');
                $query->where('language_id', 1);
            },
        ])->with([
            'galeries' => function ($query) {
                $query->select('object_id', 'slug', 'url');
                $query->where('type', 'hotel');
                $query->where('state', 1);
            },
        ])->with([
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
        ])->with([
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
        ])->with([
            'taxes' => function ($query) {
                $query->where('status', '1');
            },
        ])
        // Traer markup si hay un cliente
        ->when($client_id, function ($query) use ($period) {
            return $query->with([
                'markup' => function ($query) use ($period) {
                    $query->where('period', '>=', $period);
                },
            ]);
        })
        ->with([
            'rooms' => function ($query) use ($from, $to, $client_id, $period) {
                $query->select(
                    'id',
                    'hotel_id',
                    'room_type_id',
                    'max_capacity',
                    'min_adults',
                    'max_adults',
                    'max_child',
                    'max_infants'
                );

                $query->where('state', 1);

                $query->with([
                    'galeries' => function ($query) {
                        $query->select('object_id', 'url');
                        $query->where('type', 'room');
                        $query->where('state', 1);
                    },
                ]);

                $query->with([
                    'room_type' => function ($query) {
                        $query->select('id','occupation');
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
                $query->with([
                    'rates_plan_room' => function ($rates_plan_room) use ($from, $to, $client_id, $period) {
                        $rates_plan_room->select('id', 'rates_plans_id', 'room_id', 'status', 'bag', 'channel_id');
                        $rates_plan_room->whereHas('rate_plan');
                        $rates_plan_room->whereHas('rate_plan', function ($q) {
                            $q->where('status', 1);
                        });
                        $rates_plan_room->where('status', 1);
                        $rates_plan_room->with('channel');

                        $rates_plan_room->with([
                            'calendarys' => function ($query) use ($from, $to) {
                                $query->where('date', '>=', $from);
                                $query->where('date', '<=', $to);
                                $query->with([
                                    'policies_rates' => function ($query) {
                                        $query->with([
                                            'policies_cancelation' => function ($query) {
                                                $query->with('policy_cancellation_parameter');
                                            },
                                        ]);
                                    },
                                ]);
                                $query->with('rate');
                            },
                        ]);

                        $rates_plan_room->with([
                            'rate_plan' => function ($rate_plan) use ($client_id, $period) {
                                $rate_plan->where('status', 1);
                                $rate_plan->with(['meal.translations']);
                                $rate_plan->when($client_id, function ($query) use ($period) {
                                    return $query->with([
                                        'markup' => function ($query) use ($period) {
                                            $query->where('period', '>=', $period);
                                        },
                                    ]);
                                });
                            },
                        ]);
                        $rates_plan_room->with([
                            'inventories' => function ($query) use ($from, $to) {
                                $query->where('date', '>=', $from);
                                $query->where('date', '<=', $to);
                            },
                        ]);

                        $rates_plan_room->with([
                            'markup' => function ($query) use ($from, $to) {
                                $query->where('period', Carbon::parse($from)->year);
                            },
                        ]);
                    },
                ]);
            },
        ]);
    }

    private function calculateApplicableFees($hotel, $client_procedence_type)
    {
        $apply_fees = [];
        foreach ($hotel['taxes'] as $tax) {
            if ($tax['type'] == 't' and $hotel['country'][$client_procedence_type . '_tax'] == '1') {
                $apply_fees['t'][] = $tax;
            }

            if ($tax['type'] == 's' and $hotel['country'][$client_procedence_type . '_service'] == '1') {
                $apply_fees['s'][] = $tax;
            }
        }

        return $apply_fees;
    }


    private function applyMarkupAndFeesToRate($rate, $markup, $apply_fees, $rate_plan_room)
    {
        $rate->price_extra = $rate->price_extra ? $rate->price_extra : 0;
        $rate->price_adult = roundLito((float)number_format(
            $rate->price_adult + $rate->price_extra,
            2,
            '.',
            ''
        ));

        foreach (['price_adult', 'price_child', 'price_extra', 'price_total'] as $price_type) {
            if (!$rate->$price_type) {
                continue;
            }

            // Add markup
            $rate->$price_type = addMarkup($rate->$price_type, $markup);

            // Add extra fees
            $extra_fees = 0;
            if (isset($apply_fees['t']) and !$rate_plan_room->rate_plan['taxes']) {
                foreach ($apply_fees['t'] as $tax) {
                    $extra_fees += pricePercent($rate->$price_type, $tax['pivot']['amount']);
                }
            }
            if (isset($apply_fees['s']) and !$rate_plan_room->rate_plan['services']) {
                foreach ($apply_fees['s'] as $tax) {
                    $extra_fees += pricePercent($rate->$price_type, $tax['pivot']['amount']);
                }
            }

            $rate->$price_type += $extra_fees;
        }

        return $rate;
    }

    private function applyClientMarkupToHotels($hotels, $client_markup)
    {
        return $hotels->each(function (Hotel $hotel) use ($client_markup) {
            // Filtrar los Taxes and services que serán aplicados al hotel según si el cliente es local o extranjero
            $client_procedence_type = (!$this->client()['country_id'] or ($this->client()['country_id'] == $hotel['country_id'])) ? 'local' : 'foreign';
            $apply_fees = $this->calculateApplicableFees($hotel, $client_procedence_type);

            $hotel->rooms->each(function (Room $room) use ($hotel, $client_markup, $apply_fees) {
                $room->rates_plan_room->each(function (RatesPlansRooms $rate_plan_room) use (
                    $hotel,
                    $client_markup,
                    $apply_fees
                ) {
                    $markup = (float)$this->getMarkupFromsearch(
                        $client_markup,
                        $hotel['markup'],
                        $rate_plan_room->toArray()
                    )['markup']['markup'];

                    $rate_plan_room->calendarys->each(function (RatesPlansCalendarys $calendary) use (
                        $hotel,
                        $rate_plan_room,
                        $markup,
                        $apply_fees
                    ) {
                        $calendary->rate->transform(function (Rates $rate) use (
                            $hotel,
                            $rate_plan_room,
                            $markup,
                            $apply_fees
                        ) {
                            return $this->applyMarkupAndFeesToRate($rate, $markup, $apply_fees, $rate_plan_room);
                        });
                    });
                });
            });
        });
    }

    private function processHotelsWithoutClient($hotels)
    {
        return $hotels->each(function (Hotel $hotel) {
            $hotel->rooms->each(function (Room $room) use ($hotel) {
                $room->rates_plan_room->each(function (RatesPlansRooms $rate_plan_room) use ($hotel) {
                    $rate_plan_room->calendarys->each(function (RatesPlansCalendarys $calendary) use (
                        $hotel,
                        $rate_plan_room
                    ) {
                        $calendary->rate->transform(function (Rates $rate) use ($hotel, $rate_plan_room) {
                            $rate->price_extra = $rate->price_extra ? $rate->price_extra : 0;
                            $rate->price_adult = roundLito((float)number_format(
                                $rate->price_adult + $rate->price_extra,
                                2,
                                '.',
                                ''
                            ));

                            foreach (['price_adult', 'price_child', 'price_extra', 'price_total'] as $price_type) {
                                if ($rate_plan_room->rate_plan !== null) {
                                    $rate->$price_type = $this->addHotelRateTaxesAndServicesNoClient(
                                        $hotel,
                                        $rate_plan_room->rate_plan,
                                        $rate->$price_type
                                    );
                                }
                            }
                            return $rate;
                        });
                    });
                });
            });
        });
    }

    private function assignHotelTypeClass($hotels, $date_from, $type_classes)
    {
        $type_class_id = isset($type_classes[0]) ?  $type_classes[0] : null;
        foreach ($hotels as &$hotel) {
            // Asigna un nuevo valor a 'typeclass_id'
            $hotel_type_class = HotelTypeClass::where('hotel_id', $hotel['id'])
                ->where('year', Carbon::parse($date_from)->year)
                ->when($type_class_id, function ($q) use ($type_class_id) {
                    $q->where('typeclass_id', $type_class_id);
                })
                ->first();
            if ($hotel_type_class) {
                $hotel['typeclass_id'] = $hotel_type_class->typeclass_id;
            }
        }

        return $hotels;
    }
}

<?php

namespace App\Http\Multichannel\Hyperguest\Traits;

use App\Hotel;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

trait HyperguestGatewayTrait
{
    private $channelId = 6; // Hyperguest Channel ID
    private $typeChannel = 2;

    public function validateFieldsSearchAvailability(Request $request, ?array $extraRequest): array
    {
        $quantityPersonsRooms = $request->get('quantity_persons_rooms', []);

        if (empty($quantityPersonsRooms)) {
            $quantityPersonsRooms = [
                [
                    "room" => 1,
                    "adults" => 1,
                    "child" => 0,
                    "ages_child" => []
                ],
                [
                    "room" => 1,
                    "adults" => 2,
                    "child" => 0,
                    "ages_child" => []
                ],
                [
                    "room" => 1,
                    "adults" => 3,
                    "child" => 0,
                    "ages_child" => []
                ]
                // ,
                // [
                //     "room" => 1,
                //     "adults" => 4,
                //     "child" => 0,
                //     "ages_child" => []
                // ]
                // [
                //     [
                //         "room" => 1,
                //         "adults" => 1,
                //         "child" => 0,
                //         "ages_child" => []
                //     ]
                // ],
                // [
                //     [
                //         "room" => 1,
                //         "adults" => 2,
                //         "child" => 0,
                //         "ages_child" => []
                //     ]
                // ],
                // [
                //     [
                //         "room" => 1,
                //         "adults" => 3,
                //         "child" => 0,
                //         "ages_child" => []
                //     ]
                // ],
                // [
                //     [
                //         "room" => 1,
                //         "adults" => 4,
                //         "child" => 0,
                //         "ages_child" => []
                //     ]
                // ]
            ];
            $request->merge([
                'quantity_persons_rooms' => $quantityPersonsRooms
            ]);
        }

        $dateFrom = $request->get('date_from');
        $channelId = $request->get('channel_id', $this->channelId); // 6 es HyperGuest
        $dateTo = $request->get('date_to');
        $quantityRooms = $request->get('quantity_rooms', 1);
        $countryId = $extraRequest['country_id'] ?? null;
        $stateId = $extraRequest['state_id'] ?? null;
        $cityId = $extraRequest['city_id'] ?? null;
        $districtId = $extraRequest['district_id'] ?? null;
        $channelHotels = $this->getChannelHotels($request, $channelId, $countryId, $stateId, $cityId, $districtId); // 19912

        if (empty($dateFrom) || empty($dateTo) || empty($quantityRooms) || empty($quantityPersonsRooms) || empty($countryId)) {
            throw new BadRequestHttpException("Los parámetros de búsqueda son inválidos o están incompletos.");
        }

        // Retornar un array asociado
        return [
            'channelHotels' => $channelHotels,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'countryISO' => $countryId,
            'quantityRooms' => $quantityRooms,
            'quantityPersonsRooms' => $quantityPersonsRooms,
        ];
    }

    private function getChannelHotels(Request $request, int $channelId, ?string $country_iso, ?string $stateId, ?string $cityId, ?String $districtId): array
    {
        $hotelCodeName = $request->input('hotels_search_code', '');
        // if (empty($hotelCodeName)) {
        //     throw new BadRequestHttpException("No se proporcionó el código o nombre del hotel.");
        // }

        $query = Hotel::query();

        if (!empty($hotelCodeName)) {
            $query->where(function ($q) use ($hotelCodeName, $channelId) {
                // Coincidencia por nombre del hotel
                // $q->orWhere('name', 'like', "%{$hotelCodeName}%");
                $codes = explode(',', $hotelCodeName);

                $q->whereHas('channel', function ($q2) use ($codes, $channelId) {
                    $q2->where('channel_id', $channelId)
                        ->where('type', 2)
                        ->where('state', 1)
                        ->whereIn('code', $codes);
                });
            });
        }

        // Cargamos los ChannelHotels con los mismos filtros
        $query->with(['channel' => function ($q) use ($channelId, $hotelCodeName) {
            $q->where('channel_id', $channelId)
                // ->where('type', 2)
                ->where('state', 1);
        }]);

        if (!empty($country_iso)) {
            $query->whereHas('country', function ($q) use ($country_iso) {
                $q->where('iso', $country_iso);
            });
        }

        if (!empty($stateId)) {
            $query->where('state_id', $stateId);
        }

        if (!empty($cityId)) {
            $query->where('city_id', $cityId);
        }

        if (!empty($districtId)) {
            $query->where('district_id', $cityId);
        }

        // BUSQUEDA POR TYPE CLASS

        $hotels = $query->get()->toArray();

        if (!empty($hotels)) {
            return $hotels;
        }

        return [];
    }

    public function mergeClientHotels(array $lastHotels, array $newHotels, ?string $roomId = null): array
    {
        // Si se proporciona un roomId específico, buscar y hacer merge solo de ese room
        if ($roomId !== null) {
            return $this->mergeSpecificRoom($lastHotels, $newHotels, $roomId);
        }

        // Comportamiento original: merge de todos los rooms
        foreach ($lastHotels as &$hotel) {
            $hotelId = $hotel['hotel']['id'] ?? null;
            if (!$hotelId) continue;

            // Buscar el mismo hotel en los nuevos resultados
            $newHotel = collect($newHotels)->firstWhere('hotel.id', $hotelId);
            if (!$newHotel) continue;

            foreach ($hotel['hotel']['rooms'] as &$room) {
                $currentRoomId = $room['id'] ?? $room['name'] ?? null;
                if (!$currentRoomId) continue;

                $newRoom = collect($newHotel['hotel']['rooms'])->firstWhere('id', $currentRoomId);
                if (!$newRoom) continue;

                $this->mergeRoomRates($room, $newRoom);
                $this->updateRoomFields($room, $newRoom);
            }
        }

        return $lastHotels;
    }

    /**
     * Busca un room específico por room_id en newHotels y lo mergea en lastHotels
     */
    private function mergeSpecificRoom(array $lastHotels, array $newHotels, string $roomId): array
    {
        // Buscar el room en newHotels
        $foundRoom = null;
        $foundHotel = null;

        foreach ($newHotels as $newHotel) {
            $rooms = $newHotel['hotel']['rooms'] ?? [];
            $foundRoom = collect($rooms)->firstWhere('id', $roomId);
            if ($foundRoom) {
                $foundHotel = $newHotel;
                break;
            }
        }

        // Si no se encuentra el room en newHotels, retornar lastHotels sin cambios
        if (!$foundRoom || !$foundHotel) {
            return $lastHotels;
        }

        $hotelId = $foundHotel['hotel']['id'] ?? null;
        if (!$hotelId) {
            return $lastHotels;
        }

        // Buscar el mismo hotel en lastHotels
        foreach ($lastHotels as &$lastHotel) {
            $lastHotelId = $lastHotel['hotel']['id'] ?? null;
            if ($lastHotelId !== $hotelId) continue;

            // Buscar el room en lastHotels
            $rooms = &$lastHotel['hotel']['rooms'];
            foreach ($rooms as &$lastRoom) {
                $lastRoomId = $lastRoom['id'] ?? $lastRoom['name'] ?? null;
                if ($lastRoomId !== $roomId) continue;

                // Hacer merge de rates
                $this->mergeRoomRates($lastRoom, $foundRoom);

                // Actualizar rates_plan_room y token_search_channel
                $this->updateRoomFields($lastRoom, $foundRoom);

                // Solo procesar el primer room encontrado
                break;
            }
        }

        return $lastHotels;
    }

    /**
     * Hace merge de los rates de un room
     */
    private function mergeRoomRates(array &$room, array $newRoom): void
    {
        if (!isset($room['rates']) || !isset($newRoom['rates'])) {
            return;
        }

        foreach ($room['rates'] as &$rate) {
            // Construimos un criterio de equivalencia estable
            $criteria = [
                'ratePlanId' => $rate['ratePlanId'] ?? '',
                'ratePlanCode' => $rate['ratePlanCode'] ?? '',
            ];

            // Buscar en nuevas tarifas la más parecida
            $newRate = collect($newRoom['rates'])->first(function ($r) use ($criteria) {
                return (
                    ($r['ratePlanId'] ?? '') === $criteria['ratePlanId'] &&
                    ($r['ratePlanCode'] ?? '') === $criteria['ratePlanCode']
                );
            });

            // Si existe, actualizamos campos dinámicos
            if ($newRate) {
                // Claves que NO se deben pisar
                $preserveKeys = ['id', 'rateId'];

                // Quitamos del newRate las claves a preservar
                $newRateFiltered = array_diff_key($newRate, array_flip($preserveKeys));

                // (Opcional) evita sobreescribir con nulls
                $newRateFiltered = array_filter($newRateFiltered, static function ($v) {
                    return $v !== null;
                });

                // Mezcla: se actualiza todo excepto id/rateId
                $rate = array_replace($rate, $newRateFiltered);
                $rate['is_merged'] = true; // Marca que fue actualizado
                $rate['merge_last_updated'] = now()->toDateTimeString();
            }
        }
    }

    /**
     * Actualiza rates_plan_room y token_search_channel del room
     */
    private function updateRoomFields(array &$room, array $newRoom): void
    {
        // Actualizar rates_plan_room desde el nuevo room si existe
        if (isset($newRoom['rates_plan_room'])) {
            $room['rates_plan_room'] = $newRoom['rates_plan_room'];
        } else {
            // Si no existe rates_plan_room en newRoom, usar rates actualizados
            $room['rates_plan_room'] = $room['rates'] ?? [];
        }

        // Actualizar token_search_channel si existe en newRoom
        if (isset($newRoom['token_search_channel'])) {
            $room['token_search_channel'] = $newRoom['token_search_channel'];
        }

        // Mantener tarifas_seleccionadas sincronizadas
        $room['tarifas_seleccionadas'] = $room['rates'] ?? [];
    }
}

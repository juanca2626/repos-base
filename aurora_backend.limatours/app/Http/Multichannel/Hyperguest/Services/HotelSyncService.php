<?php

namespace App\Http\Multichannel\Hyperguest\Services;

use App\Http\Multichannel\Hyperguest\Common\Constants\ChannelHyperguestConfig;
use App\Http\Multichannel\Hyperguest\Traits\HotelSyncTrait;
use App\Http\Traits\Translations;
use App\Chain;
use App\ChannelHotel;
use App\ChannelRoom;
use App\City;
use App\HyperguestHotel;
use App\RatesPlans;
use App\Room;
use App\State;
use App\Translation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class HotelSyncService
{
    use HotelSyncTrait, Translations;

    private $service;
    private $channelId = ChannelHyperguestConfig::CHANNEL_ID;  // Hyperguest channel ID
    private $typeChannel = ChannelHyperguestConfig::TYPE_CHANNEL;  // 1 = PUSH , 2 = PULL
    private $channelIdAurora = ChannelHyperguestConfig::CHANNEL_ID_AURORA;
    private $batchId = null;  // ID del batch para registrar errores individuales

    public function __construct(HyperguestGatewayService $service)
    {
        $this->service = $service;
    }

    public function sync($limit, $countries, $from, $hotels_id, ?int $batchId = null): void
    {
        $this->batchId = $batchId;

        if(empty($countries) && empty($hotels_id)) {
            // Actualizar todos los hoteles que sean de tipo hyperguest y que sean de type PULL
            $hotels_id = ChannelHotel::where('channel_id', $this->channelId)->where('type', $this->typeChannel)->pluck('hotel_id')->toArray();
        }

        // Obtener hoteles de la tabla hyperguest_hotels
        $query = HyperguestHotel::query();

        // Filtrar por países si se proporcionan
        if (!empty($countries)) {
            $query->whereIn('country', $countries);
        }

        // Filtrar por IDs de hoteles si se proporcionan
        if (!empty($hotels_id)) {
            $query->whereIn('hotel_id', $hotels_id);
        }

        $hyperguestHotels = $query->get();

        // Transformar hoteles de la BD al formato esperado por syncHotels
        $hotels = $hyperguestHotels->map(function ($hotel) {
            return [
                'hotelId' => $hotel->hotel_id,
                'name' => $hotel->name,
                'country' => $hotel->country,
                'city' => $hotel->city,
                'cityId' => $hotel->city_id,
                'region' => $hotel->region,
                'chainId' => $hotel->chain_id,
                'chainName' => $hotel->chain_name,
                'lastUpdated' => $hotel->last_updated ? $hotel->last_updated->toIso8601String() : null,
                'version' => $hotel->version,
            ];
        })->toArray();

        if (!empty($limit)) {
            if ($limit > 0) {
                $hotels = array_slice($hotels, $from, $limit);
            } else {
                $hotels = array_slice($hotels, $from);
            }
        }

        $this->syncHotels($hotels);

    }

    // HOTELES

    protected function syncHotels(array $hotels): void
    {
        foreach ($hotels as $hotel) {
            $externalHotel = $hotel;
            $lastUpdated = isset($externalHotel['lastUpdated']) ? Carbon::parse($externalHotel['lastUpdated']) : null;

            $externalHotelId = $externalHotel['hotelId'] ?? null;
            $channelHotel = $this->buscarCodigoChannelHotel($externalHotelId);

            if ($channelHotel) {
                if ($channelHotel->type == 2) {  // PULL
                    if (!$this->hotelRequiereActualizacion($channelHotel, $lastUpdated))
                        continue;

                    $response = $this->service->getHotelDetail([
                        'channelIntegration' => [
                            'channel' => 'hyperguest',
                            'type' => 'PULL',
                            'version' => 'v1',
                            'isActive' => true
                        ],
                        'hotelId' => $externalHotelId
                    ]);
                    $detalleExternalHotel = $response['result']['data'] ?? null;
                    $detalleExternalHotel = array_merge($detalleExternalHotel, ['city' => $externalHotel['city'] ?? null]);
                    $hotelId = $channelHotel['hotel_id'];

                    $hotelUpdated = $this->actualizarOInsertarHotel($detalleExternalHotel, true, $hotelId);
                    if (!$hotelUpdated)
                        continue;

                    $this->actualizarOInsertarChannelHotel($externalHotelId, $hotelId);
                    $this->actualizarOInsertarChannelHotelAurora($hotelId);
                    $this->actualizarOInsertarImagenesHotel($detalleExternalHotel, $hotelId, true);
                    $this->actualizarOInsertarContactoHotel($detalleExternalHotel, $hotelId);
                }
            } else {

                if ($this->encontrarHotelPorNombre($hotel)){
                    continue;
                }else{
                    $response = $this->service->getHotelDetail([
                        'channelIntegration' => [
                            'channel' => 'hyperguest',
                            'type' => 'PULL',
                            'version' => 'v1',
                            'isActive' => true
                        ],
                        'hotelId' => $externalHotelId
                    ]);
                    $detalleExternalHotel = $response['result']['data'] ?? null;
                    $detalleExternalHotel = array_merge($detalleExternalHotel, ['city' => $externalHotel['city'] ?? null]);

                    $hotelId = $this->actualizarOInsertarHotel($detalleExternalHotel);
                    if (!$hotelId)
                        continue;

                    $this->actualizarOInsertarChannelHotel($externalHotelId, $hotelId);
                    $this->actualizarOInsertarChannelHotelAurora($hotelId);
                    $this->actualizarOInsertarImagenesHotel($detalleExternalHotel, $hotelId);
                    $this->actualizarOInsertarContactoHotel($detalleExternalHotel, $hotelId);
                }

            }
        }
    }

    protected function encontrarHotelPorNombre(array $externalHotel): ?int
    {
        $name = $externalHotel['name'] ?? null;
        if (!$name) {
            return null;
        }

        $hotel = DB::table('hotels')
            ->where('name', '=', trim($name))
            ->first();

        return $hotel ? $hotel->id : null;
    }

    protected function buscarCodigoChannelHotel(string $externalHotelId): ?ChannelHotel
    {
        return ChannelHotel::where('code', $externalHotelId)
            ->where('channel_id', $this->channelId)
            // ->where('type', $this->typeChannel) // se comento por que existe solo un registro para hyperguest
            ->first();
    }

    protected function hotelRequiereActualizacion($channelHotel, ?Carbon $lastUpdated): bool
    {
        if (!$lastUpdated) {
            return false;
        }

        $channel_last_updated = $channelHotel->updated_at ?? $channelHotel->created_at;

        return Carbon::parse($channel_last_updated)->lt($lastUpdated);
    }

    protected function actualizarOInsertarHotel($externalHotel, bool $isUpdate = false, $hotelId = null): ?int
    {
        $hotelIdExternal = $externalHotel['hotelId'] ?? null;
        $fields = $this->validarCamposHotel($externalHotel, $hotelIdExternal);
        if ($fields === false) {
            return null;
        }

        [
            'countryId' => $countryId,
            'currencyId' => $currencyId,
            'checkInTime' => $checkInTime,
            'checkOutTime' => $checkOutTime,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'name' => $name,
            'stars' => $stars,
            'webSite' => $webSite,
            'now' => $now,
            'city' => $city,
            'chain' => $chain,
            'rooms' => $rooms,
            'settings' => $settings,
            'location' => $location
        ] = $fields;

        // Territorio y cadena
        $territorio = $this->actualizarOInsertarTerritorio($countryId, $city, $location);
        $cadena = $this->actualizarOInsertarCadena($chain);

        $hotel = $isUpdate && $hotelId
            ? DB::table('hotels')->where('id', $hotelId)->first()
            : null;

        $values = [
            'name' => $name,
            'stars' => $stars,
            'status' => 1,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'check_in_time' => $checkInTime,
            'check_out_time' => $checkOutTime,
            'percentage_completion' => $hotel->percentage_completion ?? 0,
            'currency_id' => $currencyId,
            'hotel_type_id' => $hotel->hotel_type_id ?? 1,
            'hotelcategory_id' => $hotel->hotelcategory_id ?? 3,
            'country_id' => $countryId,
            'web_site' => $webSite,
            'state_id' => $territorio['state_id'],
            'city_id' => $territorio['city_id'],
            'chain_id' => $cadena['cadena_id'] ?? 1,
            'rate_order' => $hotel->rate_order ?? 1,
            'order' => $hotel->order ?? 1,
            'allows_child' => (($settings['maxChildAge'] ?? 0) > 0) ? 1 : 0,
            'allows_teenagers' => (($settings['maxInfantAge'] ?? 0) > 0) ? 1 : 0,
            'min_age_child' => ($settings['maxInfantAge'] ?? 0) + 1,
            'max_age_child' => ($settings['maxChildAge'] ?? 0),
            'min_age_teenagers' => 0,
            'max_age_teenagers' => $settings['maxInfantAge'] ?? 0,
            'updated_at' => $now,
        ];

        if ($isUpdate) {
            if (!is_int($hotelId) || $hotelId <= 0) {
                return null;
            }

            // Actualiza por ID, sin tocar created_at
            DB::table('hotels')->where('id', $hotelId)->update($values);
        } else {
            $values['created_at'] = $now;
            $hotelId = DB::table('hotels')->insertGetId($values);
        }

        // Sincronizar habitaciones
        if (!empty($rooms)) {
            $this->syncRooms($hotelId, $externalHotel);
        }

        return $hotelId;
    }

    protected function actualizarOInsertarTerritorio($countryId, $city, $location = []): array
    {
        $city = trim($city);
        $local = $location['region'] ?? $city;

        $territorio = $this->buscarTerritorio($countryId, $local);

        if (!$territorio) {
            $territorio = State::create([
                'country_id' => $countryId,
            ]);

            // Guardar traducciones del territorio
            $this->guardarTraducciones(
                $territorio->id,
                $this->generarRegistrosTraduccion('state_name', $local),
                'state',
                'state_name'
            );
        }

        $ciudad = $this->buscarCiudad($territorio->id, $city);

        if (!$ciudad) {
            $ciudad = City::create([
                'state_id' => $territorio->id,
            ]);

            $this->guardarTraducciones(
                $ciudad->id,
                $this->generarRegistrosTraduccion('city_name', $city),
                'city',
                'city_name'
            );
        }

        return ['state_id' => $territorio->id ?? 1, 'city_id' => $ciudad->id ?? 1];
    }

    protected function actualizarOInsertarCadena(?array $cadena): array
    {
        if (!$cadena) {
            return [
                'cadena_id' => null
            ];
        }

        $name = trim($cadena['name']);

        $busquedaCadena = $this->buscarCadena($name);

        if (!$busquedaCadena) {
            $busquedaCadena = Chain::create([
                'name' => $name,
                'status' => 1
            ]);
        }

        return [
            'cadena_id' => $busquedaCadena->id ?? 1,
        ];
    }

    protected function generarRegistrosTraduccion(string $campo, string $valor): array
    {
        $idiomas = [1, 2, 3];

        return array_map(function ($langId) use ($campo, $valor) {
            return [
                $campo => trim($valor),
                'language_id' => $langId,
            ];
        }, $idiomas);
    }

    protected function guardarTraducciones(int $id, array $traducciones, string $type, string $slug): void
    {
        foreach ($traducciones as $dato) {
            Translation::updateOrCreate([
                'type' => $type,
                'object_id' => $id,
                'slug' => $slug,
                'language_id' => $dato['language_id'],
                'value' => trim($dato[$slug]),
            ]);
        }
    }

    protected function buscarTerritorio(int $countryId, string $city): ?State
    {
        return State::where('country_id', $countryId)
            ->where('iso', $city)
            ->orWhereHas('translations', function ($query) use ($city) {
                $query
                    ->where('type', 'state')
                    ->where('value', '=', trim($city));
            })
            ->first();
    }

    protected function buscarCiudad(int $stateId, string $city): ?City
    {
        return City::where('state_id', $stateId)
            ->whereHas('translations', function ($query) use ($city) {
                $query
                    ->where('type', 'city')
                    ->where('value', '=', trim($city));
            })
            ->first();
    }

    protected function buscarCadena(string $cadena): ?Chain
    {
        return Chain::where('name', 'like', '%' . trim($cadena) . '%')->get()->first();
    }

    private function actualizarOInsertarContactoHotel(array $detalleExternalHotel, int $hotelId): void
    {
        $now = now();

        $attributes = [
            'hotel_id' => $hotelId,
            'email' => $detalleExternalHotel['contact']['email'],
        ];
        $values = [
            'hotel_id' => $hotelId,
            'email' => $detalleExternalHotel['contact']['email'],
            'name' => 'Reservas',  // Default
            'surname' => null,
            'lastname' => null,
            'position' => 'Reservas',  // Default
            'principal' => 1,
            'status' => 1,
            'updated_at' => $now,
        ];

        $exists = DB::table('contacts')->where($attributes)->exists();

        if ($exists) {
            DB::table('contacts')->where($attributes)->update(['updated_at' => $now]);
        } else {
            $values['created_at'] = $now;
            DB::table('contacts')->insert($values);
        }
    }

    protected function actualizarOInsertarChannelHotelAurora(int $hotelId): void
    {
        $exists = DB::table('channel_hotel')->where([
            'channel_id' => $this->channelIdAurora,
            'hotel_id' => $hotelId,
        ])->exists();

        $values = [
            'state' => 1,  // Activo
            'hotel_id' => $hotelId,
            'channel_id' => $this->channelIdAurora,
            'type' => null,
            'updated_at' => now(),
        ];

        if (!$exists) {
            $values['created_at'] = now();
        }

        DB::table('channel_hotel')->updateOrInsert(
            [
                'channel_id' => $this->channelIdAurora,
                'hotel_id' => $hotelId,
            ],
            $values
        );
    }

    protected function actualizarOInsertarChannelHotel(string $externalHotelId, int $hotelId): void
    {
        $exists = DB::table('channel_hotel')->where([
            'code' => $externalHotelId,
            'channel_id' => $this->channelId
        ])->exists();

        $values = [
            'state' => 1,  // Activo
            'hotel_id' => $hotelId,
            'channel_id' => $this->channelId,
            'type' => $this->typeChannel,
            'updated_at' => now(),
        ];

        if (!$exists) {
            $values['created_at'] = now();
        }

        DB::table('channel_hotel')->updateOrInsert(
            [
                'code' => $externalHotelId,
                'channel_id' => $this->channelId,
            ],
            $values
        );
    }

    protected function actualizarOInsertarImagenesHotel(array $externalHotel, int $hotelId, bool $isUpdate = false)
    {
        $images = $externalHotel['images'] ?? [];

        foreach ($images as $index => $image) {
            $imageData = [
                'channel_gallery_id' => $image['id'] ?? null,
                'object_id' => $hotelId,
                'url' => $image['uri'] ?? '',
                'position' => $index + 1,
            ];
            $this->actualizarOInsertarImagenes($imageData, 'hotel', $isUpdate, 'hotel_gallery');
        }
    }

    // HABITACIONES

    protected function syncRooms(int $hotelId, $externalHotel): void
    {
        $rooms = $externalHotel['rooms'] ?? [];

        // desactivar habitaciones, se debe comprobar habitacion por habitacion
        // $this->desactivarHabitaciones($hotelId);

        foreach ($rooms as $indexRoom => $room) {
            $externalRoom = $room;
            if (empty($externalRoom)) {
                continue;
            }

            $externalRoomId = $externalRoom['id'] ?? null;
            $externalRoomCode = $externalRoom['pmsCode'] ?? null;
            if (!$externalRoomId || !$externalRoomCode) {
                continue;
            }

            $externalRoomUUID = "$externalRoomId-$externalRoomCode";
            $channelHabitacion = $this->buscarCodigoChannelHabitacion($externalRoomId, $externalRoomCode);

            if ($channelHabitacion) {
                if (!$this->habitacionRequiereActualizacion($channelHabitacion, $externalRoom))
                    continue;

                $roomId = $channelHabitacion['room_id'];

                $roomUpdated = $this->actualizarOInsertarHabitacion($externalRoom, $hotelId, true, $roomId);
                if (!$roomUpdated)
                    continue;

                // SE AGREGO PARA ACTUALIZAR LOS CANALES
                $this->insertarOActualizarChannelHabitacion($externalRoomId, $externalRoomCode, $roomId);

                $this->actualizarOInsertarImagenesHabitacion($externalRoom, $roomId, true);
                $this->syncRatePlans($roomId, $hotelId, $externalHotel, $indexRoom);
            } else {
                $roomId = $this->actualizarOInsertarHabitacion($externalRoom, $hotelId);
                if (!$roomId)
                    continue;

                // $this->insertarChannelHabitacion($externalRoomId, $externalRoomCode, $roomId);
                $this->insertarOActualizarChannelHabitacion($externalRoomId, $externalRoomCode, $roomId);
                $this->actualizarOInsertarImagenesHabitacion($externalRoom, $roomId);
                $this->syncRatePlans($roomId, $hotelId, $externalHotel, $indexRoom);
            }
        }
    }

    protected function buscarCodigoChannelHabitacion(string $externalRoomId, string $externalRoomCode): ?ChannelRoom
    {
        return ChannelRoom::where('channel_room_id', $externalRoomId)
            ->where('channel_id', $this->channelId)
            // ->where('type', $this->typeChannel) //Solo existe un registro para hyperguest
            ->where('code', $externalRoomCode)
            ->first();
    }

    protected function habitacionRequiereActualizacion(ChannelRoom $channelHabitacion, $externalRoom): bool
    {
        // $nuevoEstado = in_array($externalRoom['status'], ['enabled','new']) ? 1 : 0;
        $nuevoEstado = 1;  // siempre activo
        // $estadoActualizado = $channelHabitacion['state'] !== $nuevoEstado;
        // $maximaCapacidad = $externalRoom['settings']['maxOccupancy'] ?? 0;
        // $ocupantesActualizado = $channelHabitacion['max_capacity'] !== $maximaCapacidad; siempre devuelve false

        // $resultado = $estadoActualizado || $ocupantesActualizado;
        // $resultado = $estadoActualizado;

        return $nuevoEstado;
    }

    protected function actualizarOInsertarHabitacion($externalRoom, int $hotelId, bool $isUpdate = false, ?int $roomId = null): int
    {
        $fields = $this->validarCamposHabitacion($externalRoom, $hotelId);
        if ($fields === false) {
            return false;
        }

        [
            'maxCapacity' => $maxCapacity,
            'minAdults' => $minAdults,
            'maxAdults' => $maxAdults,
            'maxChildren' => $maxChildren,
            'maxInfants' => $maxInfants,
            'minInventory' => $minInventory,
            'state' => $state,
            'seeInRates' => $seeInRates,
            'order' => $order,
            'inventory' => $inventory,
            'bedAdditional' => $bedAdditional,
            'ignoreRateChild' => $ignoreRateChild,
            'roomTypeId' => $roomTypeId,
            'now' => $now,
            'name' => $name
        ] = $fields;

        // $state = 0; // momentaneamente se desactiva la habitacion

        $values = [
            'hotel_id' => $hotelId,
            'estela_id' => null,
            'max_capacity' => $maxCapacity,
            'min_adults' => $minAdults,
            'max_adults' => $maxAdults,
            'max_child' => $maxChildren,
            'max_infants' => $maxInfants,
            'min_inventory' => $minInventory,
            'state' => $state,
            'see_in_rates' => $seeInRates,
            'room_type_id' => $roomTypeId,
            'order' => $order,
            'inventory' => $inventory,
            'bed_additional' => $bedAdditional,
            'ignore_rate_child' => $ignoreRateChild,
            'updated_at' => $now
        ];

        if ($isUpdate) {
            if (!is_int($roomId) || $roomId <= 0) {
                return false;
            }

            // VALIDAR SI EL ESTADO DE LA HABITACION ES DIFERENTE AL ESTADO DE LA HABITACION EN LA BASE DE DATOS
            $room = Room::where('id', $roomId)->first();

            if ($room->state !== $state && $state === 0) {
                // enviar un correo cuando se desactiva una habitacion
                $this->enviarCorreoCambioEstadoHabitacion($room);
            }

            DB::table('rooms')->where('id', $roomId)->update($values);
        } else {
            $values['created_at'] = $now;
            $roomId = DB::table('rooms')->insertGetId($values);
            $traducciones = $this->generarRegistrosTraduccion('room_name', $name);
            $this->guardarTraducciones($roomId, $traducciones, 'room', 'room_name');
        }
        return $roomId;
    }

    protected function insertarOActualizarChannelHabitacion(string $externalRoomId, string $externalRoomCode, int $roomId): void
    {
        $values = [
            'code' => $externalRoomCode,
            'channel_room_id' => $externalRoomId,
            'state' => 1,  // Activo
            'room_id' => $roomId,
            'channel_id' => $this->channelId,
            'type' => $this->typeChannel,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        // Actualizar los channels a su estado 0
        DB::table('channel_room')->updateOrInsert(
            [
                'room_id' => $roomId,
                'code' => $externalRoomCode,
                'channel_room_id' => $externalRoomId,
            ],  // condiciones para encontrar el registro
            $values
        );
    }

    protected function actualizarOInsertarImagenesHabitacion($externalRoom, int $roomId, bool $isUpdate = false)
    {
        $images = $externalRoom['images'] ?? [];

        foreach ($images as $indexImage => $image) {
            $imageData = [
                'channel_gallery_id' => $image['id'] ?? null,
                'object_id' => $roomId,
                'url' => $image['uri'] ?? '',
                'position' => $indexImage + 1,
            ];
            $this->actualizarOInsertarImagenes($imageData, 'room', $isUpdate, 'room_gallery');
        }
    }

    // PLANES TARIFARIOS

    protected function syncRatePlans(int $roomId, int $hotelId, $externalHotel, int $indexRoom): void
    {
        $ratePlans = $externalHotel['rooms'][$indexRoom]['ratePlans'] ?? [];

        foreach ($ratePlans as $ratePlan) {
            $externalRatePlan = $ratePlan;
            if (empty($externalRatePlan)) {
                continue;
            }

            $externalRatePlanId = $externalRatePlan['id'] ?? null;
            $externalRatePlanCode = $externalRatePlan['pmsCode'] ?? null;
            if (!$externalRatePlanId || !$externalRatePlanCode) {
                continue;
            }

            $externalRatePlanUUID = "$externalRatePlanId-$externalRatePlanCode";
            $channelRatePlan = $this->buscarCodigoPlanTarifa($externalRatePlanId, $externalRatePlanCode);

            if ($channelRatePlan) {
                if ($this->planTarifaRequiereActualizacion($channelRatePlan, $externalRatePlan)) {
                    $ratePlanId = $channelRatePlan['id'];
                    $ratePlanUpdated = $this->actualizarOInsertarPlanTarifa($externalRatePlan, $hotelId, true, $ratePlanId);
                    if (!$ratePlanUpdated)
                        continue;
                }
            } else {
                $ratePlanId = $this->actualizarOInsertarPlanTarifa($externalRatePlan, $hotelId);
                if (!$ratePlanId)
                    continue;
            }
        }
    }

    protected function buscarCodigoPlanTarifa(string $externalRatePlanId, string $externalRatePlanCode): ?RatesPlans
    {
        return RatesPlans::where('channel_rate_plan_id', $externalRatePlanId)
            ->where('channel_id', $this->channelId)
            ->where('code', $externalRatePlanCode)
            ->first();
    }

    protected function planTarifaRequiereActualizacion(RatesPlans $localPlan, $externalPlan): bool
    {
        return $localPlan->name !== $externalPlan['name'] ||
            $localPlan->status !== ($externalPlan['settings']['status'] === 'enabled' ? 1 : 0);
    }

    protected function actualizarOInsertarPlanTarifa($externalRatePlan, int $hotelId, bool $isUpdate = false, $ratePlanId = null): int
    {
        $externalRatePlanId = $externalRatePlan['id'];
        $externalRatePlanCode = $externalRatePlan['pmsCode'];

        $fields = $this->validarCamposPlanTarifa($externalRatePlan, $hotelId);
        if ($fields === false) {
            return false;
        }

        [
            'status' => $status,
            'name' => $name,
            'mealId' => $mealId,
            'typeId' => $typeId,
            'chargeTypeId' => $chargeTypeId,
            'now' => $now
        ] = $fields;

        $attributes = [
            'hotel_id' => $hotelId,
            'channel_rate_plan_id' => $externalRatePlanId,
            'channel_id' => $this->channelId,
            'code' => $externalRatePlanCode,
        ];
        $values = [
            'channel_rate_plan_id' => $externalRatePlanId,
            'name' => $name,
            'meal_id' => $mealId,
            'rates_plans_type_id' => $typeId,
            'type_channel' => $this->typeChannel,
            'charge_type_id' => $chargeTypeId,
            'status' => $status,
            'price_dynamic' => 0,
            'rate' => 0,
            'allotment' => 0,
            'taxes' => 0,
            'services' => 0,
            'timeshares' => 0,
            'promotions' => 0,
            'flag_process_markup' => 0,
            'updated_at' => $now
        ];

        if ($isUpdate) {
            if (!$ratePlanId) {
                return false;
            }

            $attributes['id'] = $ratePlanId;
            $values['updated_at'] = $now;
        } else {
            $attributes['name'] = $name;
            $values['created_at'] = $now;
        }

        DB::table('rates_plans')->updateOrInsert($attributes, $values);

        if (!$isUpdate) {
            $ratePlanId = DB::table('rates_plans')->where($attributes)->value('id');
        }else {
            $ratePlan = RatesPlans::where('id', $ratePlanId)->first();
            // if ($ratePlanId == 5468) {
            //     $status = 0;
            // }
            if ($ratePlan->status !== $status && $status === 0) {
                $this->enviarCorreoCambioEstadoPlanTarifa($ratePlan);

                $ratePlan->status = $status;
                $ratePlan->save();
            }
        }

        return $ratePlanId;
    }

    protected function insertarOactualizarPlanTarifaHabitacion(int $ratePlanId, int $roomId): void
    {
        // DB::table('rates_plans_rooms')->update(['state' => 0]);
        $attributes = [
            'rates_plans_id' => $ratePlanId,
            'room_id' => $roomId,
            'channel_id' => $this->channelId,
        ];
        $values = [
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'bag' => 0,
            'channel_infant_price' => 0,
            'channel_child_price' => 0,
        ];
        // DB::table('rates_plans_rooms')->updateOrInsert($attributes, $values);
    }

    protected function desactivarHabitaciones(int $hotelId): void
    {
        DB::table('rooms')->where('hotel_id', $hotelId)->where('state', 1)->update(['state' => 0]);
    }
}

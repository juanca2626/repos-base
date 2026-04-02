<?php

namespace App\Http\Aurora\Hotels\Traits;

use App\Meal;
use App\Room;
use Carbon\Carbon;
use App\RatesPlans;
use App\PoliciesCancelations;
use App\Http\Aurora\Hotels\Traits\AvailabilityHotelSearchUtil;
use App\Http\Aurora\Hotels\Traits\CancellationPolicyHyperguestPull;
use App\Http\Multichannel\Hyperguest\Common\Constants\ChannelHyperguestConfig;

trait AvailabilityHotelMapper
{
    use CancellationPolicyHyperguestPull, AvailabilityHotelSearchUtil;

    public function filterChannelRooms(array $params, int $hotelId): array
    {
        [
            'externalRooms' => $channelRooms,
            'hotel' => $hotel,
            'tokenSearchChannel' => $tokenSearchChannel,
        ] = $params;

        $roomIds = Room::where('hotel_id', $hotelId)->where('state', 1)->pluck('id')->toArray();
        $rooms = $hotel['rooms'];

        $mapped = collect($rooms)
            ->filter(function ($room) use ($roomIds, $channelRooms) {
                $isActive = in_array($room['id'], $roomIds);
                $hasChannelRoom = $this->hasChannelRoom($room, $channelRooms);
                return $isActive && $hasChannelRoom;
            })
            ->map(function ($room) use ($hotelId, $params, $channelRooms, $tokenSearchChannel) {
                $channelRoom = $this->findChannelRoom($channelRooms, $room);

                if (!$channelRoom) {
                    return null;
                }

                /* Agregados manualmente por ALEX QUISPE */

                $room['room_code'] = $channelRoom['roomCode'];
                $room['room_id'] = $room['id'];
                $room['room_id_hyperguest'] = $room['channels'][0]['channel_room_id'];
                $room['occupation'] = $room['room_type']['occupation'];
                $room['name'] = $channelRoom['name'] ?? "No hay nombre de habitacion";
                $room['description'] = $channelRoom['description'] ?? "No hay descripcion";
                $room['best_price'] = "0.00";
                $room['gallery'] = $this->mapRoomGallery($room);
                $room['token_search_channel'] = $tokenSearchChannel;

                /* Agregados manualmente por ALEX QUISPE */

                $room['rates'] = $this->mapChannelRatePlans($params, $hotelId, $room, $channelRoom);
                $room['rates_plan_room'] = $room['rates'];
                $room['best_price'] = $room['rates'][0]['rate'][0]['total_amount'] ?? 0.00;
                $room['tarifas_seleccionadas'] = $room['rates'];

                return $room;
            })
            ->values()
            ->toArray();

        return $mapped;
    }

    public function mapChannelRatePlans(array $params, int $hotelId, array $room, array $channelRoom): array
    {
        $ratesPlans = RatesPlans::where('hotel_id', $hotelId)
            ->where('status', 1)
            ->select('id', 'channel_rate_plan_id', 'rates_plans_type_id')
            ->get();

        $channelRatePlanIds = $ratesPlans->pluck('channel_rate_plan_id')->toArray();
        $channelRates = $channelRoom['rates'] ?? [];

        $allRatePlanIds = $ratesPlans->toArray();
        $ratePlans = $ratesPlans->pluck('id')->toArray();

        $channelRatePlans = array_column($allRatePlanIds, 'channel_rate_plan_id');
        $ratePlanIds = array_combine($channelRatePlans, $ratePlans);

        $ratePlanTypes = array_column($allRatePlanIds, 'rates_plans_type_id');
        $ratePlanTypeIds = array_combine($channelRatePlans, $ratePlanTypes);

        // Busqueda de meal por momento se buscara el ID 1 , pero deberia de venir de RatesPlans el campo es meal_id
        $meal = Meal::with('translations')->find(1);

        $mapped = collect($channelRates)
            ->filter(function ($channelRate) use ($channelRatePlanIds) {
                $allowed = in_array($channelRate['rateId'], $channelRatePlanIds); // Filtrar por rateId permitido
                $available = (int) $channelRate['available'] > 0; // Filtrar por disponibilidad mayor a 0
                return $allowed && $available;
            })
            ->map(function ($channelRate) use ($params, $room, $ratePlanIds, $ratePlanTypeIds, $meal) {
                [
                    'quantityPersonsRooms' => $quantityPersonsRooms,
                ] = $params;

                $priceRatePlan = (float) $channelRate['rate'][0]['price_rateplan'];
                $ratePlanId = $ratePlanIds[$channelRate['ratePlanId']] ?? null;
                $ratePlanRoomId = $this->generateRatePlanRoomId();

                $channelRate['token_search_channel'] = $room['token_search_channel'] ?? null;

                $channelRate['rateId'] = $ratePlanRoomId;
                $channelRate['name_commercial'] = $this->getBoardTypeDescription($channelRate['board']);

                $occupancies = $this->getRoomOccupancy($quantityPersonsRooms);
                $channelRate['num_adult'] = $occupancies['adults'];
                $channelRate['num_child'] = $occupancies['children'];
                $channelRate['num_infant'] = 0;

                $channelRate['rates_plans_type_id'] = $ratePlanTypeIds[$channelRate['ratePlanId']] ?? null;

                $channelRate['id'] = $ratePlanRoomId;

                $channelRate['status'] = true;
                $channelRate['ratePlanId'] = $ratePlanId;
                $channelRate['rateProviderMethod'] = ChannelHyperguestConfig::TYPE_CHANNEL;  // Este valor tambien deberia de venir de RatesPlans el campo es type_channel, por defecto para hyperguest pull el valor es 2
                $channelRate['channel'] = $room['channels'][0] ?? null;
                $channelRate['channel_id'] = ChannelHyperguestConfig::CHANNEL_ID;
                $channelRate['channel_type'] = ChannelHyperguestConfig::TYPE_CHANNEL;

                $params = array_merge($params, [
                    'rate_plan_id' => $ratePlanId,
                ]);
                $markup = $this->getMarkup($params);
                $channelRate['markup'] = [
                    'markup' => $markup
                ];

                $days = $channelRate['rate'][0]['amount_days'];
                $totalDays = count($days);
                $priceForDay = $priceRatePlan / $totalDays;
                $params['rate_plan_id'] = $ratePlanId;
                $amount_days = $this->mapAmountDays($priceForDay, $days, $params);

                $channelRate['total_amount'] = collect($amount_days)->sum('total_amount');
                $channelRate['total_amount_base'] = $priceRatePlan;
                $channelRate['total_amount_adult_base'] = $priceRatePlan;

                $channelRate['total_amount_adult'] = collect($amount_days)->sum('total_adult');
                $channelRate['total_amount_child'] = 0; // Niños e infantes en CERO
                $channelRate['total_amount_infants'] = 0; // Niños e infantes en CERO
                $channelRate['total_taxes_and_services'] = 0;

                $channelRate['avgPrice'] = 0;
                $channelRate['quantity_adults'] = $occupancies['adults'];
                $channelRate['quantity_child'] = $occupancies['children'];
                $channelRate['quantity_adults_total'] = $occupancies['adults'];
                $channelRate['quantity_child_total'] = $occupancies['children'];
                $channelRate['quantity_extras_total'] = 0;

                $channelRate['rate'][0]['total_amount'] = collect($amount_days)->sum('total_amount');
                $channelRate['rate'][0]['amount_days'] = $amount_days;
                $channelRate['amount_days'] = $amount_days;
                $channelRate['calendarys'] = $amount_days;

                $channelRate['code'] = $channelRate['ratePlanCode'];

                $channelRate['meal'] = $meal;

                $channelRate['rate_plan'] = $channelRate;

                $channelRate['tarifas_seleccionadas'] = ['rate' => $channelRate];

                $channelRate['policies_cancelation'] = $this->mapPoliciesCancellation($channelRate);
                $channelRate['political'] = $this->mapPoliciesCancellation($channelRate);

                $channelRate['rate'] = collect($channelRate['rate'])->map(function ($rate) use ($occupancies) {
                    $newRate = $rate;
                    $newRate['ages_child'] = $occupancies['ages_child'];
                    $newRate['quantity_adults'] = $occupancies['adults'];
                    $newRate['quantity_adults_total'] = $occupancies['adults'];
                    $newRate['quantity_child'] = $occupancies['children'];
                    $newRate['quantity_child_total'] = $occupancies['children'];
                    $newRate['flag_migrate'] = 1;
                    return $newRate;
                })->toArray();

                $taxes = $channelRate['prices']['net']['taxes'] ?? [];
                $channelRate['display_taxes'] = $this->getDisplayTaxes($taxes); // Mostrar dato importante por Hyperguest

                return $channelRate;
            })
            ->values()
            ->toArray();

        return $mapped ?? [];
    }

    public function mapAmountDays(float $priceRatePlan, array $days, array $params): array
    {
        $mapped = collect($days)
            ->map(function ($day) use ($params, $priceRatePlan) {
                $occupancies = $this->getRoomOccupancy($params['quantityPersonsRooms']);

                $day['num_adult'] = $occupancies['adults'];
                $day['num_child'] = $occupancies['children'];
                $day['num_infant'] = 0;

                $day['price_adult'] = $this->applyMarkup($priceRatePlan / $day['num_adult'], $params);
                $day['total_adult'] = $this->applyMarkup($priceRatePlan, $params);
                $day['total_child'] = 0; // Niños e infantes en CERO
                $day['total_extra'] = 0; // Niños e infantes en CERO
                $day['total_amount'] = $this->applyMarkup($priceRatePlan, $params);

                $day['price_adult_base'] = $priceRatePlan / $day['num_adult']; // Base ADULT
                $day['total_adult_base'] = $priceRatePlan;
                $day['total_child_base'] = 0; // Niños e infantes en CERO
                $day['total_extra_base'] = 0; // Niños e infantes en CERO
                $day['total_amount_base'] = $priceRatePlan;
                $day['total_amount_adult_base'] = $priceRatePlan;

                $rates = $day['rate'];
                $day['rate'] = $this->mapRateAmountDays($priceRatePlan, $rates, $params);

                $day['policies_rates'] = [
                    'name' => 1,
                    'translations' => [
                        [
                            'language_id' => 1,
                            'value' => 'Política de cancelación estándard'
                        ]
                    ],
                    'min_length_stay' => 1,
                ];

                return $day;
            })
            ->toArray();

        return $mapped ?? [];
    }

    public function mapRateAmountDays(float $priceRatePlan, array $rates, array $params): array
    {
        $mapped = collect($rates)
            ->map(function ($rate) use ($params, $priceRatePlan) {
                [
                    'quantityPersonsRooms' => $quantityPersonsRooms,
                ] = $params;

                $occupancies = $this->getRoomOccupancy($quantityPersonsRooms);
                $rate['num_adult'] = $occupancies['adults'];
                $rate['num_child'] = $occupancies['children'];
                $rate['num_infant'] = 0;

                $rate['price_adult'] = $this->applyMarkup($priceRatePlan, $params);
                $rate['price_adult_base'] = $priceRatePlan;
                $rate['price_child'] = 0; // Niños e infantes en CERO
                $rate['price_infant'] = 0; // Niños e infantes en CERO
                $rate['price_extra'] = 0; // Niños e infantes en CERO
                $rate['price_total'] = $this->applyMarkup($priceRatePlan, $params);

                $rate['total_adult'] = $this->applyMarkup($priceRatePlan, $params);
                $rate['total_child'] = 0; // Niños e infantes en CERO
                $rate['total_extra'] = 0; // Niños e infantes en CERO
                $rate['total_amount'] = 0;

                $rate['total_adult_base'] = $priceRatePlan;
                $rate['total_child_base'] = 0; // Niños e infantes en CERO
                $rate['total_extra_base'] = 0; // Niños e infantes en CERO
                $rate['total_amount_base'] = 0;
                $rate['total_amount_adult_base'] = $priceRatePlan;

                return $rate;
            })
            ->toArray();

        return $mapped ?? [];
    }

    public function mapPoliciesCancellation(array $channelRate): array
    {
        $cancellation = [
            'name' => null,
            'details' => []
        ];

        $cancellationPolicies = $channelRate['political']['cancellation']['details'] ?? null;

        $result = [];
        if ($cancellationPolicies) {
            $reservationDate = date('Y-m-d H:i:s');
            $checkIn = $channelRate['rate'][0]['amount_days'][0]['date'] . ' 14:00:00';

            $result = $this->generarPoliticasCancelacion($channelRate['total_amount'], $checkIn, $reservationDate, $cancellationPolicies);

            if (count($result) > 0) {
                $police_apply = $result[0];
                $police_apply_next = isset($result[1]) ? $result[1] : NULL;
                if ((float) $police_apply['penalizacion_usd'] <= 0) {
                    $hasta = new \DateTime($police_apply['hasta']);
                    $fechaFormateada = $hasta->format('d/m/Y');
                    $mesage = sprintf(
                        "You have until %s before check-in to cancel without penalties.",
                        $fechaFormateada
                    );
                } else {
                    $mesage = sprintf(
                        "You will have to pay a penalty of USD %s if you cancel",
                        $police_apply['penalizacion_usd']
                    );
                }

                $cancellation = [
                    'hayperguest_pull' => true,
                    'name' => $mesage,
                    'details' => $result,
                    'cancellationPolicies' => $cancellationPolicies
                ];
            }
        }

        return [
            'rate' => [],
            'cancellation' => $cancellation,
            'no_show' => []
        ];
    }
}

<?php

namespace App\Http\Aurora\Hotels\Traits;

use App\Contact;
use App\Http\Traits\Reservations;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;

trait SetReservations
{
    use Reservations;

    public function getHyperguestHotelsReservationData($reservations): Collection
    {
        $reservationData = collect();

        foreach ($reservations as $key => $selectedRoomRate) {
            $roomIdent = (isset($selectedRoomRate['room_ident'])) ? $selectedRoomRate['room_ident'] : (string)(random_int(100000,
                999999));
            $optional = (isset($selectedRoomRate['optional'])) ? $selectedRoomRate['optional'] : 0;
            $best_option = $selectedRoomRate['best_option'];
            $package_id = $selectedRoomRate['package_id'] ?? null;

            $this->addGuestsAmount([
                'adults' => $selectedRoomRate['quantity_adults'],
                'childs' => $selectedRoomRate['quantity_child']
            ]);

            $token_search = $selectedRoomRate['token_search'];
            $hotel_id = $selectedRoomRate['hotel_id'];

            $contactHotel = Contact::select('email')->whereHotelId($hotel_id)->whereNotNull('email')->pluck('email')->toArray();

            if (count($contactHotel) == 0) {
                throw new Exception(trans('validations.reservations.emails_notifications_hotel_required',
                    ['hotel' => $hotel_id]));
            }

            $rate_plan_room_id = $selectedRoomRate['rate_plan_room_id']; //$selectedRoomRate['rate_plan']['rate']['ratePlanId']; // Modificado por Alex reciente
            $check_in = $selectedRoomRate['date_from'];
            $check_out = $selectedRoomRate['date_to'];

            $ratePlanRoomKey = $rate_plan_room_id . '_' . $key;

            // $channel_id = $selectedRoomRate['rate_plan']['rate']['channel_id'] ?? 1;
            // $channel_type = $selectedRoomRate['rate_plan']['rate']['channel_type'] ?? null;

            // Extraes la data del cache seleccionado (token_search)
            if ($best_option) {
                $selectedRoomData = $this->setRoomRateData($token_search, $roomIdent, $hotel_id, $rate_plan_room_id,
                    $check_in, $check_out, $best_option);
            } else {

                $passengers = isset($selectedRoomRate['passengers']) ? $selectedRoomRate['passengers'] : NULL;
                $selectedRoomData = $this->setRoomRateData($token_search, $roomIdent, $hotel_id, $rate_plan_room_id,
                    $check_in, $check_out, $best_option, $selectedRoomRate['quantity_adults'],
                    $selectedRoomRate['quantity_child'], $selectedRoomRate['child_ages'], $passengers);
            }

            $rateProviderMethod = NULL;

            if (isset($selectedRoomData['selectedRatePlanRoom']['rate']['rateProviderMethod'])) {
                $rateProviderMethod = $selectedRoomData['selectedRatePlanRoom']['rate']['rateProviderMethod'];
            }

            if ($selectedRoomData['selectedRatePlanRoom']['rate']['channel_id'] == 6 and $rateProviderMethod === 2) {

                $exists_hotels_hyperguest = true;
                $selectedRatePlanRoom = $selectedRoomData['selectedRatePlanRoom'];
                // $selectedRate = $selectedRatePlanRoom['rate'][0]; // TENER CUIDADO AQUI, ASUMIMOS QUE SIEMPRE VIENE 1 SOLO RATE
                $selectedRate = $selectedRatePlanRoom['rate']; // TENER CUIDADO AQUI, ASUMIMOS QUE SIEMPRE VIENE 1 SOLO RATE
                $firstPenaltyDate = !empty($selectedRatePlanRoom['policy_cancellation']['apply_date']) ? $selectedRatePlanRoom['policy_cancellation']['apply_date'] : Carbon::now('America/Lima')->startOfDay()->format('Y-m-d');
                $room_id = $selectedRoomData['selectedRoom']['id'];
                // $rates_plan_id = $selectedRatePlanRoom['rate_plan']['id'];
                // $channel_id = $selectedRatePlanRoom['channel_id'];
                $rates_plan_id = $selectedRate['ratePlanId'];
                $channel_id = $selectedRate['channel_id'];
                $roomKey = $room_id . '_' . $key;
                $ratesPlanKey = $rates_plan_id . '_' . $key;
                $hotelChannelById = array_column($selectedRoomData['selectedHotel']['hotel']['channels'], 'pivot',
                    'id');
                $hotelChannelByCode = array_column($selectedRoomData['selectedHotel']['hotel']['channels'], 'pivot',
                    'code');
                $roomChannelsByById = array_column($selectedRoomData['selectedRoom']['channels'], 'pivot', 'id');
                $roomChannelsByCode = array_column($selectedRoomData['selectedRoom']['channels'], 'pivot', 'code');
                $token_dates = !empty($selectedRoomRate['reservation_hotel_code']) ? $selectedRoomRate['reservation_hotel_code'] : $selectedRoomRate['date_from'] . '|' . $selectedRoomRate['date_to'];

                if (!$reservationData->offsetExists($token_dates)) {
                    $reservationData->offsetSet($token_dates, collect([
                        'reservation_hotel_code' => $token_dates,
                        'check_in' => $selectedRoomRate['date_from'],
                        'check_out' => $selectedRoomRate['date_to'],
                        'package_id' => $package_id,
                        'optional' => $optional,
                        'nights' => $selectedRoomData['selectedHotel']['nights'],
                        'hotels' => collect()
                    ]));
                }

                $hotels_token = $reservationData->offsetGet($token_dates)['hotels'];

                if (!$hotels_token->offsetExists($hotel_id)) {
                    $hotel = $selectedRoomData['selectedHotel']['hotel'];
                    $hotel['hotel_code'] = empty($hotelChannelByCode['HYPERGUEST']['code']) ? '' : $hotelChannelByCode['HYPERGUEST']['code'];
                    $hotel['notes'] = (isset($selectedRoomRate['notes']) and !empty($selectedRoomRate['notes'])) ? $selectedRoomRate['notes'] : null;

                    $hotels_token->offsetSet($hotel_id, collect([
                        'hotel' => collect($hotel),
                        'rooms' => collect(),
                        'rates_plans' => collect(),
                        'rates_plans_rooms' => collect(),
                    ]));
                }

                $hotel = $hotels_token->offsetGet($hotel_id);
                if (!$hotel['rooms']->offsetExists($roomKey)) {
                    $roomTranslations = array_column($selectedRoomData['selectedRoom']['translations'], 'value',
                        'slug');
                    $hotel['rooms']->offsetSet($roomKey, collect([
                        'id' => $selectedRoomData['selectedRoom']['id'],
                        'hotel_id' => $selectedRoomData['selectedRoom']['hotel_id'],
                        'room_type_id' => $selectedRoomData['selectedRoom']['room_type_id'],
                        'max_capacity' => $selectedRoomData['selectedRoom']['max_capacity'],
                        'min_adults' => $selectedRoomData['selectedRoom']['min_adults'],
                        'max_adults' => $selectedRoomData['selectedRoom']['max_adults'],
                        'max_child' => $selectedRoomData['selectedRoom']['max_child'],
                        'room_code' => empty($roomChannelsByCode['HYPERGUEST']['code']) ? '' : $roomChannelsByCode['HYPERGUEST']['code'],
                        'name' => empty($roomTranslations['room_name']) ? '' : $roomTranslations['room_name'],
                        'description' => empty($roomTranslations['room_description']) ? '' : $roomTranslations['room_description'],
                    ]));
                }

                if (!$hotel['rates_plans']->offsetExists($ratesPlanKey)) {
                    // $hotel['rates_plans']->offsetSet($ratesPlanKey, collect($selectedRatePlanRoom['rate_plan']));
                    $hotel['rates_plans']->offsetSet($ratesPlanKey, collect($selectedRate));

                }

                if (!$hotel['rates_plans_rooms']->offsetExists($ratePlanRoomKey)) {
                    $hotel['rates_plans_rooms']->offsetSet($ratePlanRoomKey, collect());
                }

                $rates_plans_rooms = $hotel['rates_plans_rooms']->offsetGet($selectedRate['rateId'] . '_' . $key);


                $meal = [
                    'id' => $selectedRate['rate_plan']['meal']['id'],
                    'name' => collect($selectedRate['rate_plan']['meal']['translations'])->first(function ($item) {
                        return $item['slug'] == 'meal_name' and $item['language_id'] == 1;
                    })['value']
                ];

                $rates_plan_room_new = $selectedRatePlanRoom;
                // $rates_plan_room_new = $this->getChannelsAvailableRates(
                //     $selectedRate,
                //     $selectedRate['quantity_adults'],
                //     empty($selectedRate['quantity_child']) ? 0 : $selectedRate['quantity_child'],
                //     empty($selectedRate['ages_child']) ? [] : $selectedRate['ages_child'],
                //     $hotel['hotel'],
                //     $selectedRoomData['selectedRoom'],
                //     true
                // );

                // Variables intermedias para depuración y test
                $chainId = $hotel['hotel']['chain_id'] ?? null;
                $hotelId = $hotel['hotel']['id'] ?? null;
                $hotelName = $hotel['hotel']['name'] ?? '';
                $hotelCode = $hotel['hotel']['hotel_code'] ?? '';

                $policiesCancelation = $rates_plan_room_new['policies_cancelation'] ?? null;
                $channelId = $rates_plan_room_new['channel_id'] ?? null;
                $channelCode = $rates_plan_room_new['channel']['code'] ?? '';
                $channelHotelCode = $hotelChannelById[$channel_id]['code'] ?? '';
                $channelRoomCode = $roomChannelsByById[$channel_id]['code'] ?? '';

                $roomData = $hotel['rooms'][$roomKey] ?? [];
                $roomId = $roomData['id'] ?? null;
                $roomName = $roomData['name'] ?? '';
                $roomCode = $roomData['room_code'] ?? '';
                $roomDescription = $roomData['description'] ?? '';
                $roomTypeId = $roomData['room_type_id'] ?? null;
                $maxCapacity = $roomData['max_capacity'] ?? 0;
                $minAdults = $roomData['min_adults'] ?? 0;
                $maxAdults = $roomData['max_adults'] ?? 0;
                $maxChild = $roomData['max_child'] ?? 0;

                $ratePlanData = $hotel['rates_plans'][$ratesPlanKey] ?? [];
                $ratePlanId = $ratePlanData['id'] ?? null;
                $ratePlanCode = $ratePlanData['code'] ?? '';
                $ratePlanName = $ratePlanData['name'] ?? '';
                $ratePlanCommercial = !empty($ratePlanData['translations'])
                    ? $ratePlanData['translations'][0]['value']
                    : $ratePlanName;

                $markup = (float)($rates_plan_room_new['markup']['markup'] ?? 0);

                $mealId = $meal['id'] ?? null;
                $mealName = $meal['name'] ?? '';

                $ratesPlansRoomId = $rates_plan_room_new['id'] ?? null;
                $onRequest = (!empty($selectedRoomRate['only_on_request']))
                    ? 0
                    : ($rates_plan_room_new['status'] ?? 0);
                $adultNum = $rates_plan_room_new['quantity_adults'] ?? 0;
                $childNum = $rates_plan_room_new['quantity_child'] ?? 0;
                $hasChannelChildRate = $rates_plan_room_new['has_channel_child_rate'] ?? 0;
                $guestNote = $selectedRoomRate['guest_note'] ?? '';
                $isModification = $selectedRoomRate['is_modification'] ?? 0;

                $totalAmount = $rates_plan_room_new['total_amount'] ?? 0;
                $totalAmountBase = $selectedRate['total_amount_base'] ?? 0;
                $totalAdultBase = $selectedRate['total_amount_adult_base'] ?? 0;
                $totalChildBase = $rates_plan_room_new['total_amount_child_base'] ?? 0;

                $calendarys = $rates_plan_room_new['calendarys'] ?? [];
                $taxesAndServices = $selectedRatePlanRoom['taxes_and_services'] ?? [];

                // Array final usando las variables
                $rates_plans_rooms[$roomIdent] = [
                    'chain_id' => $chainId,
                    'hotel_id' => $hotelId,
                    'hotel_name' => $hotelName,
                    'hotel_code' => $hotelCode,
                    'check_in' => $check_in,
                    'check_out' => $check_out,
                    'first_penalty_date' => $firstPenaltyDate,
                    'policies_cancelation' => $policiesCancelation,
                    'channel_id' => $channelId,
                    'channel_code' => $channelCode,
                    'channel_hotel_code' => $channelHotelCode,
                    'channel_room_code' => $channelRoomCode,
                    'room_id' => $roomId,
                    'room_key' => $roomKey,
                    'room_name' => $roomName,
                    'room_code' => $roomCode,
                    'room_description' => $roomDescription,
                    'room_type_id' => $roomTypeId,
                    'max_capacity' => $maxCapacity,
                    'min_adults' => $minAdults,
                    'max_adults' => $maxAdults,
                    'max_child' => $maxChild,
                    'rates_plan_id' => $ratePlanId,
                    'rate_plan_code' => $ratePlanCode,
                    'rate_plan_name' => $ratePlanName,
                    'rate_plan_name_commercial' => $ratePlanCommercial,
                    'markup' => $markup,
                    'meal_id' => $mealId,
                    'meal_name' => $mealName,
                    'rates_plans_room_id' => $ratesPlansRoomId,
                    'onRequest' => $onRequest,
                    'adult_num' => $adultNum,
                    'child_num' => $childNum,
                    'has_channel_child_rate' => $hasChannelChildRate,
                    'guest_note' => $guestNote,
                    'is_modification' => $isModification,
                    'total_amount' => $totalAmount,
                    'total_amount_base' => $totalAmountBase,
                    'total_amount_adult_base' => $totalAdultBase,
                    'total_amount_child_base' => $totalChildBase,
                    'calendarys' => $calendarys,
                    'taxes_and_services' => $taxesAndServices,
                ];
            }
        }

        return $reservationData;
    }

    public function setRoomRateData4(
        $token_search,
        $roomIdent,
        $hotel_id,
        $rate_plan_room_id,
        $check_in,
        $check_out,
        $best_option,
        $quantity_adults = null,
        $quantity_child = null,
        $ages_child = [],
        $passengers = null // viene de files_ms
        //$zeroRates = false
    ): array
    {

        $hotelsSearch = $this->getTokenSearchData($token_search);
        $selectedHotel = null;
        $selectedRoom = null;
        $selectedRatePlanRoom = null;

        $selectedHotel = collect($hotelsSearch)->first(function ($hotel) use ($hotel_id, $check_in, $check_out) {
            return $hotel['hotel_id'] == $hotel_id and $hotel['check_in'] == $check_in and $hotel['check_out'] == $check_out;
        });

        if (!$selectedHotel) {
            throw new \Exception(trans('validations.reservations.token_search_hotel_not_found',
                ['hotel' => $hotel_id]));
        }
        if ($best_option) {
            $selectedRoom = collect($selectedHotel['best_options']['rooms'])->first(function ($room) use (
                $rate_plan_room_id
            ) {
                return collect($room['rates_plan_room'])->contains('id', $rate_plan_room_id);
            });

            if (!$selectedRoom) {
                throw new \Exception("rate_plan_room_id = {$rate_plan_room_id} not found in selection");
            }
        } else {
            $selectedRoom = collect($selectedHotel['hotel']['rooms'])->first(function ($room) use ($rate_plan_room_id) {
                return collect($room['rates_plan_room'])->contains('id', $rate_plan_room_id) || collect($room['rates_plan_room'])->contains('rateId', $rate_plan_room_id);
            });


            if (!$selectedRoom) {
                throw new \Exception("rate_plan_room_id = {$rate_plan_room_id} not found in selection");
            }

            $selectedRoom = $this->getBestRateByOccupation($selectedHotel, $selectedRoom, $rate_plan_room_id,
                $quantity_adults, $quantity_child, null, $ages_child);


            if (empty($selectedRoom['total_taxes_and_services_amount'])) {
                $selectedRoom['total_taxes_and_services_amount'] = 0;
            }

            // Calcular taxes and services
            $applicable_fees = $this->getHotelApplicableFees(session()->get('selected_client'),
                $selectedHotel['hotel']);

            foreach ($selectedRoom['tarifas_seleccionadas'] as $tarInd => $tarifa) {
                if (empty($selectedRoom['tarifas_seleccionadas'][$tarInd]['total_taxes_and_services_amount'])) {
                    $selectedRoom['tarifas_seleccionadas'][$tarInd]['total_taxes_and_services_amount'] = 0;
                }

                $taxes_and_services = $this->addHotelExtraFees($applicable_fees,
                    $tarifa['rate']["rate_plan"], $tarifa["total_amount"]);

                $selectedRoom['tarifas_seleccionadas'][$tarInd]['total_amount'] += $taxes_and_services['amount_fees'];
                $selectedRoom['tarifas_seleccionadas'][$tarInd]['taxes_and_services'] = $taxes_and_services;
                $selectedRoom['tarifas_seleccionadas'][$tarInd]['total_taxes_and_services_amount'] += $taxes_and_services['amount_fees'];

                $selectedRoom['total_amount'] += $taxes_and_services['amount_fees'];
                $selectedRoom['total_taxes_and_services_amount'] += $taxes_and_services['amount_fees'];
            }
        }

        //Todo Validamos el numero de noches minimo
        $this->validationMinimumHotelNights($selectedHotel, $selectedRoom['rates_plan_room'], $rate_plan_room_id);

        $keyRatePlanRoom = array_search($rate_plan_room_id,
            array_column(array_column($selectedRoom['tarifas_seleccionadas'], 'rate'), 'id'));
        if ($keyRatePlanRoom === false) {
            throw new \Exception("RatePlanRoom {$rate_plan_room_id} not found on token {$token_search}");
        }

        $selectedRatePlanRoom = $selectedRoom['tarifas_seleccionadas'][$keyRatePlanRoom];

        return [
            'roomIdent' => $roomIdent,
            'selectedHotel' => $selectedHotel,
            'selectedRoom' => $selectedRoom,
            'selectedRatePlanRoom' => $selectedRatePlanRoom,
            'passengers' => $passengers
        ];
    }

    public function setRoomRateData2(array $params): array
    {
        [
            'token_search' => $token_search,
            'room_ident' => $roomIdent,
            'hotel_id' => $hotel_id,
            'rate_plan_room_id' => $rate_plan_room_id,
            'check_in' => $check_in,
            'check_out' => $check_out,
            'passengers' => $passengers,
        ] = $params;

        $hotelsSearch = $this->getTokenSearchData($token_search);

        $selectedHotel = collect($hotelsSearch)->first(function ($hotel) use ($hotel_id, $check_in, $check_out) {
            return $hotel['hotel_id'] == $hotel_id and $hotel['check_in'] == $check_in and $hotel['check_out'] == $check_out;
        });

        if (!$selectedHotel) {
            throw new Exception(trans('validations.reservations.token_search_hotel_not_found',
                ['hotel' => $hotel_id]));
        }

        $selectedRoom = collect($selectedHotel['hotel']['rooms'])->first(function ($room) use (
            $rate_plan_room_id
        ) {
            return collect($room['rates_plan_room'])->contains('id', $rate_plan_room_id) || collect($room['rates_plan_room'])->contains('ratePlanId', $rate_plan_room_id);
        });

        if (!$selectedRoom) {
            throw new Exception("rate_plan_room_id = $rate_plan_room_id not found in selection");
        }

        $ratesSelected = array_column($selectedRoom['tarifas_seleccionadas'], 'ratePlanId');
        $keyRatePlanRoom = array_search($rate_plan_room_id, $ratesSelected);
        if ($keyRatePlanRoom === false) {
            throw new Exception("RatePlanRoom $rate_plan_room_id not found on token $token_search");
        }

        $selectedRatePlanRoom = $selectedRoom['tarifas_seleccionadas'][$keyRatePlanRoom];

        return [
            'roomIdent' => $roomIdent,
            'selectedHotel' => $selectedHotel,
            'selectedRoom' => $selectedRoom,
            'selectedRatePlanRoom' => $selectedRatePlanRoom,
            'passengers' => $passengers
        ];
    }
}

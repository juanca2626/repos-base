<?php

namespace App\Http\Aurora\Hotels\Services;

use App\ChannelHotel;
use App\Contact;
use App\Http\Aurora\Hotels\Traits\SetReservations;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;

class ReservationHotelService
{
    use SetReservations;

    private $reservation_errors;
    private $reservation = null;
    private $selection = [
        'booking_code' => '',
        'file_code' => '',
        'payment_code' => '',
        'selected_client' => '',
        'selected_client_e_commerce' => '',
        'selected_excecutive' => '',
        'customer_name' => '',
        'given_name' => '',
        'surname' => '',
        'reference' => '',
        'customer_country' => '',
        'hotels' => [],
        'rooms' => [],
        'entity' => '',
        'rates_plans' => [],
        'rates_plans_rooms' => [],
        'policies_rates' => [],
        'policies_cancelation' => [],
        'room_comments' => '',
        'hotels_stays' => [],
        'total_guests' => [
            'adults' => 0,
            'childs' => 0,
        ],
        'total' => [
            'total_amount' => 0,
            'total_amount_discount' => 0,
            'total_amount_adult' => 0,
            'total_amount_child' => 0,
            'total_amount_infants' => 0,
            'total_amount_extra' => 0,
            'total_amount_taxes' => 0,
            'total_amount_services' => 0,
        ],
        'discounts' => [],
        'date_init' => null,
        'reference_new' => null,
        'type_class_id' => null,
        'object_id' => null,
        'total_amount' => 0,
        'total_tax' => 0,
        'subtotal_amount' => 0,
        'total_discounts' => 0,
        'markup' => 0,
        'order_number' => '',
        'hotel_allowed_on_request' => true,
        'service_allowed_on_request' => true,
    ];
    private $selectec_tokens_search = [];

    public function getHotelReservationData($reservations, $frontend = false, $send_mail = 1, $files_ms_parameters = NULL): Collection
    {
        if (count($reservations) == 0) {
            return collect();
        }

        $totalAdults = collect($reservations)->sum('quantity_adults');
        if (!$totalAdults) {
            $this->reservation_errors->push(trans('validations.reservations.quantity_adults_required'));
        }

        $reservationData = collect();
        foreach ($reservations as $key => $selectedRoomRate) {
            $roomIdent = (isset($selectedRoomRate['room_ident'])) ? $selectedRoomRate['room_ident'] : (string)(random_int(100000,
                999999));
            $optional = (isset($selectedRoomRate['optional'])) ? $selectedRoomRate['optional'] : 0;
            $best_option = $selectedRoomRate['best_option'];
            $package_id = isset($selectedRoomRate['package_id']) ? $selectedRoomRate['package_id'] : null;

            $this->addGuestsAmount([
                'adults' => $selectedRoomRate['quantity_adults'],
                'childs' => $selectedRoomRate['quantity_child']
            ]);

            $token_search = $selectedRoomRate['token_search'];
            $hotel_id = $selectedRoomRate['hotel_id'];

            $contactHotel = Contact::select('email')->whereHotelId($hotel_id)->whereNotNull('email')->pluck('email')->toArray();
            if (count($contactHotel) == 0) {
                throw new Exception(trans('validations.reservations.emails_notifications_hotel_required',
                    ['hotel' => $hotel_id]), 1001);
            }

            $rate_plan_room_id = $selectedRoomRate['rate_plan_room_id'];
            $check_in = $selectedRoomRate['date_from'];
            $check_out = $selectedRoomRate['date_to'];
            $from = Carbon::parse($check_in);
            $to = Carbon::parse($check_out);
            $reservation_days = $from->diffInDays($to);
            $to = $to->subDay(1)->format('Y-m-d');

            //Todo extraes la data del cache seleccionado (token_search)
            if ($best_option) {
                $selectedRoomData = $this->setRoomRateData(
                    $token_search,
                    $roomIdent,
                    $hotel_id,
                    $rate_plan_room_id,
                    $check_in,
                    $check_out,
                    $best_option);

            } else {
                $passengers = isset($selectedRoomRate['passengers']) ? $selectedRoomRate['passengers'] : NULL; // VIENE DE FILES_MS

                $selectedRoomData = $this->setRoomRateData(
                    $token_search,
                    $roomIdent,
                    $hotel_id,
                    $rate_plan_room_id,
                    $check_in,
                    $check_out,
                    $best_option,
                    $selectedRoomRate['quantity_adults'],
                    $selectedRoomRate['quantity_child'],
                    $selectedRoomRate['child_ages'],
                    $passengers);
            }

            $hotel_id = $selectedRoomData['selectedHotel']['hotel']['id'];

            $room_id = $selectedRoomData['selectedRoom']['id'];
            $rates_plan_id = $selectedRoomData['selectedRatePlanRoom']['rate']['rate_plan']['id'];
            $channel_id = $selectedRoomData['selectedRatePlanRoom']['rate']['channel_id'];

            $hotelChannelById = array_column($selectedRoomData['selectedHotel']['hotel']['channels'], 'pivot', 'id');
            $hotelChannelByCode = array_column($selectedRoomData['selectedHotel']['hotel']['channels'], 'pivot',
                'code');
            $roomChannelsByById = array_column($selectedRoomData['selectedRoom']['channels'], 'pivot', 'id');
            $roomChannelsByCode = array_column($selectedRoomData['selectedRoom']['channels'], 'pivot', 'code');

            $token_dates = $selectedRoomRate['date_from'] . '|' . $selectedRoomRate['date_to'];

            if (!empty($selectedRoomRate['reservation_hotel_code'])) {
                $token_dates .= '|' . $selectedRoomRate['reservation_hotel_code'];
                if (!$reservationData->offsetExists($token_dates)) {
                    $reservationData->offsetSet($token_dates, collect([
                        'reservation_hotel_code' => $selectedRoomRate['reservation_hotel_code'],
                        'check_in' => $selectedRoomRate['date_from'],
                        'check_out' => $selectedRoomRate['date_to'],
                        'package_id' => $package_id,
                        'optional' => $optional,
                        'nights' => $selectedRoomData['selectedHotel']['nights'],
                        'hotels' => collect(),
                        'send_mail' => $send_mail
                    ]));
                }
            } else {
                if (!$reservationData->offsetExists($token_dates)) {
                    $reservationData->offsetSet($token_dates, collect([
                        'check_in' => $selectedRoomRate['date_from'],
                        'check_out' => $selectedRoomRate['date_to'],
                        'nights' => $selectedRoomData['selectedHotel']['nights'],
                        'package_id' => $package_id,
                        'optional' => $optional,
                        'hotels' => collect(),
                        'send_mail' => $send_mail
                    ]));
                }
            }

            /** @var Collection $hotels_token */
            $hotels_token = $reservationData->offsetGet($token_dates)['hotels'];

            if (!$hotels_token->offsetExists($hotel_id)) {


                $hotel_filter = null;
                if (isset($files_ms_parameters)) {
                    $hotel_filter = array_filter($files_ms_parameters, function ($param) use ($token_search, $hotel_id) {
                        return $param['token_search'] == $token_search and $param['hotel_id'] == $hotel_id;
                    });

                    $hotel_filter = reset($hotel_filter);
                    if ($hotel_filter !== false) {
                        $hotel_filter = json_encode($hotel_filter);
                    } else {
                        $hotel_filter = null;
                    }

                }

                $hotel = $selectedRoomData['selectedHotel']['hotel'];
                $channelAurora = ChannelHotel::where('hotel_id', $hotel['id'])->where('channel_id', 1)->first();

                if (!$channelAurora) {
                    throw new Exception("El hotel {$hotel['hotel']['name']} no tiene configurado el canal Aurora.", 1001);
                }
                $channelAuroraCode = $channelAurora->code;
                
                $hotel['hotel_code'] = $channelAuroraCode;
                $hotel['notes'] = (isset($selectedRoomRate['notes']) and !empty($selectedRoomRate['notes'])) ? $selectedRoomRate['notes'] : null;

                $hotels_token->offsetSet($hotel_id, collect([
                    'hotel' => collect($hotel),
                    'rooms' => collect(),
                    'rates_plans' => collect(),
                    'rates_plans_rooms' => collect(),
                    'token_search' => $token_search,
                    'files_ms_parameters' => $hotel_filter
                ]));
            }

            /** @var Collection $hotel */
            $hotel = $hotels_token->offsetGet($hotel_id);

            $roomTranslations = array_column($selectedRoomData['selectedRoom']['translations'], 'value', 'slug');

            if (!$hotel['rooms']->offsetExists($room_id . '_' . $key)) {
                $hotel['rooms']->offsetSet($room_id . '_' . $key, collect([
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
            } else {
                $hotel['rooms']->add([
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
                ]);
            }

            if (!$hotel['rates_plans']->offsetExists($rates_plan_id . '_' . $key)) {
                $hotel['rates_plans']->offsetSet($rates_plan_id . '_' . $key,
                    collect($selectedRoomData['selectedRatePlanRoom']['rate']['rate_plan']));
            } else {
                $hotel['rates_plans']->add([$selectedRoomData['selectedRatePlanRoom']['rate']['rate_plan']]);
            }

            if (!$hotel['rates_plans_rooms']->offsetExists($rate_plan_room_id . '_' . $key)) {
                $hotel['rates_plans_rooms']->offsetSet($rate_plan_room_id . '_' . $key, collect());
            }

            $rates_plans_rooms = $hotel['rates_plans_rooms']->offsetGet($selectedRoomData['selectedRatePlanRoom']['rate']['id'] . '_' . $key);

            $meal = [
                'id' => $selectedRoomData['selectedRatePlanRoom']['rate']['rate_plan']['meal']['id'],
                'name' => collect($selectedRoomData['selectedRatePlanRoom']['rate']['rate_plan']['meal']['translations'])->first(function (
                    $item
                ) {
                    return $item['slug'] == 'meal_name' and $item['language_id'] == 1;
                })['value']
            ];

            if ($selectedRoomData['selectedRatePlanRoom']['rate']['total_amount'] <= 0 || $selectedRoomData['selectedRatePlanRoom']['rate']['total_amount_base'] <= 0) {
                throw new Exception(trans('validations.reservations.hotel_rate_total_zero_not_allowed'));
            }

            $rate_plan_name_commercial = $hotel['rates_plans'][$rates_plan_id . '_' . $key]['name'];

            if (isset($hotel['rates_plans'][$rates_plan_id . '_' . $key]['translations'])) {
                $rate_plan_name_commercial = (count($hotel['rates_plans'][$rates_plan_id . '_' . $key]['translations']) > 0) ? $hotel['rates_plans'][$rates_plan_id . '_' . $key]['translations'][0]['value'] : $hotel['rates_plans'][$rates_plan_id . '_' . $key]['name'];
            }

            $rates_plan_id_flex = $hotel['rates_plans'][$rates_plan_id . '_' . $key]['id'];
            if (isset($hotel['rates_plans'][$rates_plan_id . '_' . $key]['rateProviderMethod'])) {
                $rates_plan_id_flex = $hotel['rates_plans'][$rates_plan_id . '_' . $key]['rateProviderMethod'] == 2 ? $hotel['rates_plans'][$rates_plan_id . '_' . $key]['ratePlanId'] : $hotel['rates_plans'][$rates_plan_id . '_' . $key]['id'];
            }

            $rates_plans_rooms[$roomIdent] = [
                'chain_id' => $hotel['hotel']['chain_id'],
                'hotel_id' => $hotel['hotel']['id'],
                'hotel_name' => $hotel['hotel']['name'],
                'hotel_code' => $channelAuroraCode ?? null,
                'check_in' => $check_in,
                'check_out' => $check_out,
                'first_penalty_date' => !empty($selectedRoomData['selectedRatePlanRoom']['policy_cancellation']['apply_date']) ? $selectedRoomData['selectedRatePlanRoom']['policy_cancellation']['apply_date'] : Carbon::now('America/Lima')->startOfDay()->format('Y-m-d'),
                'policies_cancelation' => isset($selectedRoomData['selectedRatePlanRoom']['rate']['policies_cancelation']) ? $selectedRoomData['selectedRatePlanRoom']['rate']['policies_cancelation'] : [],
                'channel_id' => $selectedRoomData['selectedRatePlanRoom']['rate']['channel_id'],
                'channel_code' => $selectedRoomData['selectedRatePlanRoom']['rate']['channel']['code'],
                'channel_hotel_code' => empty($hotelChannelById[$channel_id]) ? '' : $hotelChannelById[$channel_id]['code'],
                'channel_room_code' => empty($roomChannelsByById[$channel_id]) ? '' : $roomChannelsByById[$channel_id]['code'],
                'room_id' => $hotel['rooms'][$room_id . '_' . $key]['id'],
                'room_name' => $hotel['rooms'][$room_id . '_' . $key]['name'],
                'room_code' => $hotel['rooms'][$room_id . '_' . $key]['room_code'],
                'room_description' => $hotel['rooms'][$room_id . '_' . $key]['description'],
                'room_type_id' => $hotel['rooms'][$room_id . '_' . $key]['room_type_id'],
                'max_capacity' => $hotel['rooms'][$room_id . '_' . $key]['max_capacity'],
                'min_adults' => $hotel['rooms'][$room_id . '_' . $key]['min_adults'],
                'max_adults' => $hotel['rooms'][$room_id . '_' . $key]['max_adults'],
                'max_child' => $hotel['rooms'][$room_id . '_' . $key]['max_child'],
                'rates_plan_id' => $rates_plan_id_flex,
                'rate_plan_code' => $hotel['rates_plans'][$rates_plan_id . '_' . $key]['code'],
                'rate_plan_name' => $hotel['rates_plans'][$rates_plan_id . '_' . $key]['name'],
                'rate_plan_name_commercial' => $rate_plan_name_commercial,
                'markup' => (float)$selectedRoomData['selectedRatePlanRoom']['rate']['markup']['markup'],
                'meal_id' => $meal['id'],
                'meal_name' => $meal['name'],
                'rates_plans_room_id' => $selectedRoomData['selectedRatePlanRoom']['rate']['id'],
                'onRequest' => (isset($selectedRoomRate['only_on_request']) and $selectedRoomRate['only_on_request'] == true) ? 0 : $selectedRoomData['selectedRatePlanRoom']['rate']['status'],
                'adult_num' => $selectedRoomData['selectedRatePlanRoom']['rate']['quantity_adults'],
                'child_num' => empty($selectedRoomData['selectedRatePlanRoom']['rate']['quantity_child']) ? 0 : $selectedRoomData['selectedRatePlanRoom']['rate']['quantity_child'],
                'guest_note' => empty($selectedRoomRate['guest_note']) ? '' : $selectedRoomRate['guest_note'],
                'is_modification' => empty($selectedRoomRate['is_modification']) ? 0 : $selectedRoomRate['is_modification'],
                'total_amount' => $selectedRoomData['selectedRatePlanRoom']['rate']['total_amount'],
                'total_amount_base' => $selectedRoomData['selectedRatePlanRoom']['rate']['total_amount_base'],
                'calendarys' => $selectedRoomData['selectedRatePlanRoom']['rate']['calendarys'],
                'taxes_and_services' => $selectedRoomData['selectedRatePlanRoom']['taxes_and_services'],
                'passengers' => $selectedRoomData['passengers']
            ];
        }

        return $reservationData;
    }
}

<?php

namespace App\Http\Traits;

use App\ChainsMultipleProperty;
use App\BusinessRegionsCountry;
use App\ChannelHotel;
use App\ChannelsLogs;
use App\Client;
use App\ClientConfiguration;
use App\Contact;
use App\Country;
use App\Galery;
use App\Hotel;
use App\Http\Aurora\Hotels\Services\ReservationHotelService;
use App\Http\Multichannel\Hyperguest\Common\Constants\ChannelHyperguestConfig;
use App\Http\Multichannel\Hyperguest\Services\CancelReservationHyperguestGatewayService;
use App\Http\Multichannel\Hyperguest\Services\CreateReservationHyperguestGatewayService;
use App\Http\Services\Traits\ClientTrait;
use App\Http\Siteminder\OTA_HotelResNotifRQ as SiteminderReservations;
use App\Http\Stella\StellaService;
use App\IntegrationHyperguest;
use App\Jobs\SendHotelReservationsEmails;
use App\Jobs\SendNotificationHyperguest;
use App\MarkupService;
use App\Penalty;
use App\Reservation;
use App\ReservationBilling;
use App\ReservationPassenger;
use App\ReservationsDiscounts;
use App\ReservationsFlight;
use App\ReservationsHotel;
use App\ReservationsHotelsRatesPlansRooms;
use App\ReservationsHotelsRatesPlansRoomsCalendarys;
use App\ReservationsHotelsRatesPlansRoomsCalendarysRates;
use App\ReservationsHotelsRatesPlansRoomsCancellationPollicies;
use App\ReservationsPackage;
use App\ReservationsService;
use App\ReservationsServicesRatesPlans;
use App\ReservationsServicesRatesPlansCancellationPolicies;
use App\Service;
use App\Support\AgeValidator;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

trait Reservations
{
    use ValidateHotelSearch,
        ManageSearchHotel,
        ClientTrait,
        AddHotelRateTaxesAndServices,
        HyperguestGeneral,
        ChannelLogs;

    /** @var Collection */
    private $reservation_errors;
    /** @var Reservation null */
    private $reservation = null;
    /** @var array Estructura de la reserva de hoteles */
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

    /**
     * @param $token_search
     * @param $roomIdent
     * @param $hotel_id
     * @param $rate_plan_room_id
     * @param $check_in
     * @param $check_out
     * @param $best_option
     * @param null $quantity_adults
     * @param null $quantity_child
     * @param array $ages_child
     * @return array
     * @throws Exception
     */
    public function setRoomRateData(
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
        $passengers = null, // viene de files_ms
        $set_markup_personalice=null
        //$zeroRates = false
    )
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
                return collect($room['rates_plan_room'])->contains('id', $rate_plan_room_id);
            });

            if (!$selectedRoom) {
                throw new \Exception("rate_plan_room_id = {$rate_plan_room_id} not found in selection");
            }

            $selectedRoom = $this->getBestRateByOccupation($selectedHotel, $selectedRoom, $rate_plan_room_id,
                $quantity_adults, $quantity_child, null, $ages_child, $set_markup_personalice);


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

    /**
     * @param $room_comments
     */
    public function addGuestsAmount($guestAmount)
    {
        $this->selection['total_guests']['adults'] += (int)$guestAmount['adults'];
        $this->selection['total_guests']['childs'] += (int)$guestAmount['childs'];
    }

    /**
     * @param $token_search
     * @return array
     * @throws Exception
     */
    public function getTokenSearchData($token_search)
    {
        if (empty($this->selectec_tokens_search[$token_search])) {
            $hotelsSearch = $this->getHotelsByTokenSearch($token_search);
            if (!empty($hotelsSearch['error'])) {
                throw new \Exception($hotelsSearch['error'], $hotelsSearch['error_code']);
            }
            $this->selectec_tokens_search[$token_search] = $hotelsSearch;
        }

        return $this->selectec_tokens_search[$token_search];
    }

    /**
     * @param $selectedRoomSearch
     * @return mixed
     * @throws Exception
     */
    private function getBestRateByOccupation(
        $selectedHotel,
        $selectedRoomSearch,
        $rate_plan_room_id,
        $quantity_adults = null,
        $quantity_child = null,
        $quantity_extras = null,
        $ages_child = [],
        $set_markup_personalice=null
    )
    {
//        if (!$quantity_adults) {
//            throw new Exception('quantity_adults can not be null');
//        }
        $selectedRoomSearch['tarifas_seleccionadas'] = [];
        $selectedRoomSearch["quantity_adults"] = $quantity_adults;
        $selectedRoomSearch["quantity_child"] = $quantity_child;
        $selectedRoomSearch["quantity_infants"] = 0;
        $selectedRoomSearch["quantity_rooms"] = 1;
        $selectedRoomSearch["total_amount"] = 0;

        foreach ($selectedRoomSearch['rates_plan_room'] as $rates_plan_room) {
            if ($rates_plan_room['id'] == $rate_plan_room_id) {
                $quantity_persons = $this->getQuantityPersons(
                    $quantity_adults,
                    $quantity_child,
                    $selectedRoomSearch["max_capacity"],
                    $selectedRoomSearch['room_type']['occupation'],
                    $selectedRoomSearch["min_adults"],
                    $selectedRoomSearch["max_adults"],
                    $selectedRoomSearch["max_child"]
                );

                $rates_plan_room_markup = null;

                // Si no es Hyperguest PULL, aplicamos markup
                $channel_type = isset($rates_plan_room['channel_type']) ? $rates_plan_room['channel_type'] : 0;

                if ((int)$channel_type != 2) {
                    $markup_personailice = $this->selection['markup']>0 ? $this->selection['markup'] : $set_markup_personalice;
                    $rates_plan_room_markup = $this->getMarkupFromsearch(
                        $selectedHotel['client_markup'],
                        $selectedHotel['hotel']['markup'],
                        $rates_plan_room, $markup_personailice
                    );
                }


                $rates_plan_room_new = [];
                if ($rates_plan_room["channel_id"] == 1) {
                    $rates_plan_room_new = $this->calculateRatePlanRoomCalendarByPersons(
                        $rates_plan_room_markup,
                        $quantity_persons["quantity_adults"],
                        $quantity_persons["quantity_child"],
                        $quantity_persons["quantity_extras"],
                        $ages_child,
                        $selectedHotel['hotel'],
                        0,
                        $selectedRoomSearch
                    );
                } else {
                    if (strtoupper($rates_plan_room['channel']['code']) === 'HYPERGUEST') {
                        // Reseteando el markup en caso de Hyperguest..
                        if ((int)$rates_plan_room['channel_id'] === ChannelHyperguestConfig::CHANNEL_ID && (int)$channel_type === ChannelHyperguestConfig::TYPE_CHANNEL) {
                            $calendarys = $rates_plan_room['calendarys'];
                            $calendarys = array_map(function ($day) {

                                    $rates = $day['rate'];

                                    $rates = array_map(function ($rate) {
                                        $rate['price_adult'] = $rate['price_adult_base'];
                                        $rate['total_adult'] = $rate['total_adult_base'];

                                        return $rate;
                                    }, $rates);

                                    $day['rate'] = $rates;
                                    $day['total_adult'] = $day['total_adult_base'];
                                    $day['total_amount'] = $day['total_amount_base'];

                                    return $day;
                                }, $calendarys);

                            $rates_plan_room['calendarys'] = $calendarys;
                        }else{
                            $rates_plan_room = $rates_plan_room_markup;
                        }
                        $rates_plan_room_new = $this->getChannelsAvailableRates(
                            $rates_plan_room,
                            $quantity_persons["quantity_adults"],
                            $quantity_persons["quantity_child"],
                            $ages_child,
                            $selectedHotel['hotel'],
                            $selectedRoomSearch
                        );
                    }
                }

                if (!count($rates_plan_room_new)) {
                    throw new Exception('Invalid channel selected: ' . $rates_plan_room['channel']['code'], 1003);
                }

                if ($rates_plan_room_new['show_message_error']) {
                    // dd($rates_plan_room["channel_id"],$channel_type, $rates_plan_room_new);
                    throw new Exception($rates_plan_room_new['message_error'], $rates_plan_room_new['code_error']);
                }

                $rate = [
                    'total_amount' => $rates_plan_room_new["total_amount"],
                    'total_amount_adult' => $rates_plan_room_new["total_amount_adult"],
                    'total_amount_child' => $rates_plan_room_new["total_amount_child"],
                    'total_amount_infants' => 0,
                    'total_amount_extras' => $rates_plan_room_new["total_amount_extra"],
                    'quantity_adults' => $quantity_persons["quantity_adults"],
                    'quantity_child' => $quantity_persons["quantity_child"],
                    'quantity_infants' => 0,
                    'quantity_extras' => $quantity_persons["quantity_extras"],
                    'ages_child' => $ages_child,
                    'people_coverage' => $quantity_persons["quantity_adults"] + $quantity_persons["quantity_child"] + $quantity_persons["quantity_extras"],
                    'quantity_inventory_taken' => 1,
                    'policy_cancellation' => [],
                    'policies_cancellation' => [],
                    'taxes_and_services' => [],
                    'supplements' => [],
                    'rate' => $rates_plan_room_new,
                ];

                $selectedRoomSearch["total_amount"] = $rates_plan_room_new["total_amount"];
                $selectedRoomSearch['tarifas_seleccionadas'] = [$rate];

                break;
            }
        }
        return $selectedRoomSearch;
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    private function validateReservationAuth(Request $request)
    {
        // TODO: refactotizar a un trait la validacion del usuario cliente y ejecutivo
        if (auth_user()->user_type_id == 4) {// Todo cliente vendedor
            $user_type = 'client';


            // Todo Obtenemos el cliente del Usuario vendedor (Seller)
            /** @var Client $client */
            $client = auth_user()->clientSellers()
                ->where('clients.status', 1)
                ->wherePivot('status', 1)
                ->first();

            //Todo Verificamos si el cliente esta activo
            if (!$client) {
                throw new \Exception(trans('validations.reservations.client_authorized_booking'), 1008);
            }

            if ($request->has('executive_id')) {
                $executive = $this->getClientExecutives($client->id, $request->input('executive_id'));
                if ($executive) {
                    $executive['id'] = $executive['user_id'];
                } else {
                    throw new \Exception(trans('validations.reservations.executive_not_assigned_to_your_account'),
                        1021);
                }

            } else {
                //Todo Busco a la primera ejecutiva de la lista de ejecutvas asignadas al cliente
                $executive = $this->getClientExecutives($client->id);
                if ($executive and $executive->user) {
                    $executive = $executive->user;
                }

                if (!$executive) {
                    throw new \Exception(trans('validations.reservations.your_account_not_assigned_to_specialist'),
                        1022);
                }
            }

            $client_user_id = auth_user()['id'];
            $executive_id = $executive['id'];
        } elseif (auth_user()->user_type_id == 3) { //Todo ejecutivo
            $user_type = 'excecutive';

            $client = auth_user()->clients()
                ->where('clients.id', $request->input('client_id'))
                ->first();

            if (!$client) {
                throw new \Exception(trans('validations.reservations.client_not_related_to_executive'), 1023);
            } elseif ($client->status != 1) {
                throw new \Exception(trans('validations.reservations.client_authorized_booking'), 1024);
            }

            // client_seller
            $clientSeller = $client->client_sellers()
                ->where('users.user_type_id', 4)
                ->where('users.status', 1)
                ->wherePivot('status', 1)
                ->first();

            if (!$clientSeller) {
                $client_user_id = null;
            } else {
                $client_user_id = $clientSeller["user_id"];
            }

            $executive_id = auth_user()->id;
        } else {
            throw new \Exception(trans('validations.reservations.user_cannot_send_reservations'), 1025);
        }

        $client["country_id"] = $client["country_id"] ? $client["country_id"] : 89;// 89 = peru

        $country = Country::find($client["country_id"]);

        $executive = User::find($executive_id);

        if (empty($executive->email)) {
            throw new Exception(trans('validations.reservations.executive_has_not_notification_defined',
                ['executive' => $executive_id]), 1026);
        }

        session()->put('selected_client_user_id', $client_user_id);
        session()->put('selected_client', $client);
        session()->put('selected_client_country', $country);
        session()->put('selected_executive', $executive);
        session()->put('user_type', $user_type);
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    private function reservationRecheck(Request $request)
    {
        $this->selectionConstructor($request, null, true);
        $this->reservation->offsetUnset('file_code');

        $data = $this->reservationToApiResponse($this->reservation);
        return ['success' => true, 'data' => $data];
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    private function reservationPush(Request $request): array
    {
        $lang = ($request->has('lang')) ? $request->input('lang') : 'en';

        if (!in_array($lang, ['es', 'en', 'pt'])) {
            App::setLocale('en');
        } else {
            App::setLocale($lang);
        }

        $this->validateInputs($request);

        $guest = current($request->input('guests'));
        $file_code = $request->input('file_code');
        $payment_code = $request->input('payment_code');

        $given_name = $guest['given_name'];
        $surname = $guest['surname'];
        $reference = $request->input('reference');
        $markup = $request->input('set_markup');

        if (!isset($markup) or empty($markup)) {
            $markup = 0;
        }
        $order_number = ($request->has('order_number')) ? $request->input('order_number') : '';
        $reference_new = $request->input('reference_new');

        $total_amount = ($request->has('total_amount')) ? $request->input('total_amount') : 0;
        $tax_total = ($request->has('total_tax')) ? $request->input('total_tax') : 0;
        $total_discounts = ($request->has('total_discounts')) ? $request->input('total_discounts') : 0;
        $subtotal_amount = ($request->has('subtotal_amount')) ? $request->input('subtotal_amount') : 0;

        $this->selection['reference'] = empty($reference) ? '' : $reference;
        $this->selection['given_name'] = $given_name;
        $this->selection['surname'] = $surname;
        $this->selection['file_code'] = empty($file_code) ? '' : $file_code;
        $this->selection['payment_code'] = empty($payment_code) ? null : $payment_code;
        $this->selection['date_init'] = null;
        $this->selection['reference_new'] = empty($reference_new) ? null : $reference_new;
        $this->selection['total_amount'] = $total_amount;
        $this->selection['total_tax'] = $tax_total;
        $this->selection['total_discounts'] = $total_discounts;
        $this->selection['subtotal_amount'] = $subtotal_amount;
        $this->selection['order_number'] = $order_number;
        $this->selection['markup'] = $markup;
        $this->selection['entity'] = ($request->has('entity')) ? $request->input('entity') : 'External-Reservation';
        $this->selection['object_id'] = ($request->has('object_id')) ? $request->input('object_id') : null;
        $this->selection['type_class_id'] = ($request->has('type_class_id')) ? $request->input('type_class_id') : null;

        $channel_hyperguest_reservations = null;

        $this->selectionConstructor($request, $channel_hyperguest_reservations);
        if ($this->reservation_errors->count()) {
            $this->reservation_errors->push(trans('validations.reservations.quantity_adults_required'));
            throw new Exception($this->reservation_errors[0], 1001);
        }
        $this->saveReservation();

        $maxConsecutiveHotel = $this->reservation->getConsecutiveHotelPrev();

        $this->reservation = Reservation::getReservations(
            [
                'file_code' => $this->reservation->file_code,
                'hotel_consecutive_from' => $maxConsecutiveHotel,
                'service_consecutive_from' => $this->reservation->getConsecutiveServicePrev(),
                // Todo: Tomara los servicios u hoteles nuevos cuando se crea o agregan al file
                'status_email' => Reservation::STATUS_CRONJOB_WITHOUT_SEND_EMAIL_RESERVE
            ],
            true
        );

        $reservaHyperGuest = $this->validationHyperguestHotels($request, $reference, $this->reservation);

        //Todo Guaramos en el log lo que llega del request
        $this->putLogAurora(
            json_encode($request->all()), json_encode($reservaHyperGuest),
            '', '',
            'RequestCreateFile', $this->reservation->id, true, 'json');

        $this->updateReservation($this->reservation, $reservaHyperGuest, $request);

        //Todo Si la reserva viene de una cotizacion
        if ($this->selection['entity'] === 'Quote' and !empty($this->selection['object_id'])) {
            //Todo Bloqueamos los servicios de esa cotizacion
            $this->blockServicesQuote($this->selection['object_id']);
        }

        $this->set_parent_ids($this->reservation->id);

        //Todo Guaramos en el log lo que llega del request
        $this->putLogAurora(
            json_encode($request->all()), '{}',
            '', '',
            'RequestCreateFile', $this->reservation->id, true, 'json');

        //Todo Eliminar token_search de hoteles y servicios para liberar espacio
        $this->removeTokensFromRequest($request);

        $response = $this->reservationToApiResponse($this->reservation);
        return ['success' => true, 'data' => $response];
    }

    // Función para eliminar los tokens
    private function removeTokensFromRequest(Request $request)
    {
        $data = $request->all();
        // Procesar reservations
        if (isset($data['reservations']) && is_array($data['reservations'])) {
            foreach ($data['reservations'] as $reservation) {
                if (isset($reservation['token_search'])) {
                    Cache::forget($reservation['token_search']);
                }
            }
        }
        // Procesar reservations_services
        if (isset($data['reservations_services']) && is_array($data['reservations_services'])) {
            foreach ($data['reservations_services'] as $reservation_service) {
                if (isset($reservation_service['token_search'])) {
                    Cache::forget($reservation_service['token_search']);
                }
            }
        }
    }


    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    private function preReservation(Request $request)
    {
        $lang = ($request->has('lang')) ? $request->input('lang') : 'en';

        if (!in_array($lang, ['es', 'en', 'pt'])) {
            App::setLocale('en');
        } else {
            App::setLocale($lang);
        }

        $this->validateInputs($request);

        $guest = current($request->input('guests'));
        $file_code = $request->input('file_code');
        $payment_code = $request->input('payment_code');

        $given_name = $guest['given_name'];
        $surname = $guest['surname'];
        $reference = $request->input('reference');
        $markup = $request->input('set_markup');

        if (!isset($markup) or empty($markup)) {
            $markup = 0;
        }
        $order_number = ($request->has('order_number')) ? $request->input('order_number') : '';
        $reference_new = $request->input('reference_new');

        $total_amount = ($request->has('total_amount')) ? $request->input('total_amount') : 0;
        $tax_total = ($request->has('total_tax')) ? $request->input('total_tax') : 0;
        $total_discounts = ($request->has('total_discounts')) ? $request->input('total_discounts') : 0;
        $subtotal_amount = ($request->has('subtotal_amount')) ? $request->input('subtotal_amount') : 0;

        $this->selection['no_generate_nro_file'] = 1;
        $this->selection['reference'] = empty($reference) ? '' : $reference;
        $this->selection['given_name'] = $given_name;
        $this->selection['surname'] = $surname;
        $this->selection['file_code'] = empty($file_code) ? '' : $file_code;
        $this->selection['payment_code'] = empty($payment_code) ? null : $payment_code;
        $this->selection['date_init'] = null;
        $this->selection['reference_new'] = empty($reference_new) ? null : $reference_new;
        $this->selection['total_amount'] = $total_amount;
        $this->selection['total_tax'] = $tax_total;
        $this->selection['total_discounts'] = $total_discounts;
        $this->selection['subtotal_amount'] = $subtotal_amount;
        $this->selection['order_number'] = $order_number;
        $this->selection['markup'] = $markup;
        $this->selection['entity'] = ($request->has('entity')) ? $request->input('entity') : null;
        $this->selection['object_id'] = ($request->has('object_id')) ? $request->input('object_id') : null;
        $this->selection['type_class_id'] = ($request->has('type_class_id')) ? $request->input('type_class_id') : null;

        $channel_hyperguest_reservations = null;

        $this->selectionConstructor($request, $channel_hyperguest_reservations);
        if ($this->reservation_errors->count()) {
            $this->reservation_errors->push(trans('validations.reservations.quantity_adults_required'));
            throw new Exception($this->reservation_errors[0], 1001);
        }


        return $this->reservation;

        //Todo: Hotel
        // $this->reservation->reservationsHotel()->saveMany($this->reservation->reservationsHotel);
        // foreach ($this->reservation->reservationsHotel as $reservationsHotel) {
        //     $reservationsHotel->reservationsHotelRooms()->saveMany($reservationsHotel->reservationsHotelRooms);
        //     foreach ($reservationsHotel->reservationsHotelRooms as $reservationsHotelRoom) {
        //         $reservationsHotelRoom->reservationHotelCancelPolicies()->saveMany($reservationsHotelRoom->reservationHotelCancelPolicies);

        //         $reservationsHotelRoom->reservationsHotelsCalendarys()->saveMany($reservationsHotelRoom->reservationsHotelsCalendarys);
        //         foreach ($reservationsHotelRoom->reservationsHotelsCalendarys as $reservationsHotelsCalendary) {
        //             $reservationsHotelsCalendary->reservationHotelRoomDateRate()->saveMany($reservationsHotelsCalendary->reservationHotelRoomDateRate);
        //         }

        //         $reservationsHotelRoom->supplements()->saveMany($reservationsHotelRoom->supplements);
        //     }
        // }

    }


    private function updateReservation($reservation, $reservaHyperGuest, $request)
    {

        $file_code = empty($request->input('file_code')) ? '' : $request->input('file_code');

        if ($reservaHyperGuest["success"] == true) {

            $channel_hyperguest_reservations = $reservaHyperGuest["hotels_reservation"];

            foreach ($reservation->reservationsHotel as $reservations_hotel) {

                $check_in = Carbon::parse($reservations_hotel->check_in);
                $check_out = Carbon::parse($reservations_hotel->check_out);

                foreach ($channel_hyperguest_reservations as $channel_hyperguest_reservation) {

                    if ($channel_hyperguest_reservation["hotel_id"] == $reservations_hotel->hotel_id &&
                        $channel_hyperguest_reservation["date_from"] == $check_in->format('Y-m-d') &&
                        $channel_hyperguest_reservation["date_to"] == $check_out->format('Y-m-d')) {

                        $channel_hyperguest_reservation_code = $channel_hyperguest_reservation["booking_id"];

                        foreach ($reservations_hotel->reservationsHotelRooms as $reservations_hotel_room) {

                            if ($reservations_hotel_room->channel_id == '6') {

                                $reservationsHotelRatesPlansRoom = ReservationsHotelsRatesPlansRooms::find($reservations_hotel_room->id);
                                if ($channel_hyperguest_reservation["success"]) {
                                    $reservationsHotelRatesPlansRoom->status = 1;//0: cancelada, 1: activa, 2: modificada, 3: por confirmar
                                    $reservationsHotelRatesPlansRoom->status_in_channel = 1;//0: cancelada, 1: activa, 2: modificada, 3: por confirmar
                                    $reservationsHotelRatesPlansRoom->channel_reservation_code = $channel_hyperguest_reservation_code;
                                    $reservationsHotelRatesPlansRoom->channel_reservation_code_master = $channel_hyperguest_reservation_code;
                                } else {
                                    $reservationsHotelRatesPlansRoom->status = 3;//0: cancelada, 1: activa, 2: modificada, 3: por confirmar
                                    $reservationsHotelRatesPlansRoom->status_in_channel = 1;//0: cancelada, 1: activa, 2: modificada, 3: por confirmar
                                    $reservationsHotelRatesPlansRoom->onRequest = 0;
                                    $reservationsHotelRatesPlansRoom->channel_reservation_code = '';
                                    $reservationsHotelRatesPlansRoom->channel_reservation_code_master = '';
                                }

                                $reservationsHotelRatesPlansRoom->save();
                            }
                        }
                    }
                }

                $confirm = true;
                $reservationsHotelRatesPlansRooms = ReservationsHotelsRatesPlansRooms::where('reservations_hotel_id',
                    $reservations_hotel->id)->get();
                foreach ($reservationsHotelRatesPlansRooms as $reservationsHotelRatesPlansRoom) {
                    if ($reservationsHotelRatesPlansRoom->onRequest != "1") {
                        $confirm = false;
                    }
                }

                if ($confirm) {
                    $reservationsHotel = ReservationsHotel::find($reservations_hotel->id);
                    $reservationsHotel->status = 1; //0: cancelada, 1: activa, 2: modificada, 3: por confirmar
                    $reservationsHotel->status_in_channel = 1;

                    // Agregados manualmente al final de reservar
                    $reservationsHotel->hotel_code = $reservations_hotel->hotel_code;
                    $reservationsHotel->business_region_id = $reservations_hotel->business_region_id;

                    $reservationsHotel->save();
                }

            }

        }
    }

    private function set_parent_ids($reservation_id)
    {

        $reservation_additionals = ReservationsService::where('reservation_id', $reservation_id)
            ->where('parent_id', '!=', null)
            ->get();

        foreach ($reservation_additionals as $additional) {
            $custom_key = explode('-', $additional->parent_id); // 1214-2021-02-06
            $parent = ReservationsService::where('reservation_id', $reservation_id)
                ->where('service_id', $custom_key[0])
                ->where('date', $custom_key[1] . '-' . $custom_key[2] . '-' . $custom_key[3])
                ->first();
            if ($parent) {
                $additional->parent_id = $parent->id;
                $additional->save();
            }
        }

    }

    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    private function reservationAddHotelRoom(Request $request)
    {
        $this->validateReservationAuth($request);

        if (!is_array($request->input('reservations')) or count($request->input('reservations')) == 0) {
            throw new \Exception('At least one reservation room is required');
        }

        $this->selectionInit(
            session()->get('user_type'),
            session()->get('selected_executive')['id'],
            session()->get('selected_executive')['name'],
            session()->get('selected_executive')['code'],
            session()->get('selected_client')['id'],
            session()->get('selected_client')["code"],
            session()->get('selected_client_country')['iso'],
            session()->get('selected_client_user_id'),
            0
        );

        $reservation_hotel_id = null;
        foreach ($request->input('reservations') as $reservations) {
            if (empty($reservations['reservation_hotel_code']) or trim($reservations['reservation_hotel_code']) == '') {
                throw new \Exception('Missing reservation_hotel_code to add rooms');
            } else {
                if ($reservation_hotel_id and $reservation_hotel_id != $reservations['reservation_hotel_code']) {
                    throw new \Exception('Can only edit one Hotel at same time');
                }

                $reservation_hotel_id = trim($reservations['reservation_hotel_code']);
            }
        }

        $reservationData = $this->getHotelReservationData($request->input('reservations'));
        if ($reservationData->count() > 1) {
            throw new \Exception('Can only edit one Hotel for the same dates');
        }

        $reservationData = $reservationData->first();
        $current_date = Carbon::now('America/Lima')->startOfDay();
        $check_in = Carbon::parse($reservationData['check_in']);
        $check_out = Carbon::parse($reservationData['check_out']);
        $nights = $reservationData['nights'];

        $RH = ReservationsHotel::find($reservation_hotel_id);
        $reservationHotelData = $reservationData['hotels'][$RH['hotel_id']];
        $RH = $this->setReservationRoomData($RH, $reservationHotelData, $current_date, $check_in, $check_out, $nights);

        $RH->reservationsHotelRooms()->saveMany($RH->reservationsHotelRooms);
        foreach ($RH->reservationsHotelRooms as $reservationsHotelRoom) {
            $reservationsHotelRoom->reservationHotelCancelPolicies()->saveMany($reservationsHotelRoom->reservationHotelCancelPolicies);

            $reservationsHotelRoom->reservationsHotelsCalendarys()->saveMany($reservationsHotelRoom->reservationsHotelsCalendarys);
            foreach ($reservationsHotelRoom->reservationsHotelsCalendarys as $reservationsHotelsCalendary) {
                $reservationsHotelsCalendary->reservationHotelRoomDateRate()->saveMany($reservationsHotelsCalendary->reservationHotelRoomDateRate);
            }

            $reservationsHotelRoom->supplements()->saveMany($reservationsHotelRoom->supplements);
            foreach ($reservationsHotelRoom->supplements as $reservationsHotelsSupplements) {
                $reservationsHotelsSupplements->calendaries()->saveMany($reservationsHotelsSupplements->calendaries);
            }
        }

        $reservation_id_to_send = $this->getReservationRoomPending($RH->reservationsHotelRooms);
        if (count($reservation_id_to_send) == 0) {
            return null;
        }

        $this->reservation = Reservation::getReservations([
            'reservation_hotel_id' => $RH['id'],
            'reservation_hotel_room_id' => $reservation_id_to_send,
        ], true);

        // Save on Channels
        $channelRes = $this->saveOnChannels($this->reservation);

        $reservation_id_to_send = $this->getReservationRoomToNotif($channelRes['reservation']->reservationsHotel[0]->reservationsHotelRooms);

        $this->reservation = Reservation::getReservations([
            'reservation_hotel_id' => $channelRes['reservation']->reservationsHotel[0]['id'],
            'reservation_hotel_room_id' => $reservation_id_to_send,
        ], true);

        // Send Emails
        // SendHotelReservationsEmails::dispatch($this->reservation, 'modification');
        SendHotelReservationsEmails::dispatchNow($this->reservation, 'modification');

        return ['success' => true, 'data' => $this->reservationToApiResponse($this->reservation)];
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    private function reservationChangeDatesHotel(Request $request)
    {
        // Valida las cencenciales y el cliente / ejecutivo de la reervas
        $this->validateReservationAuth($request);

        if (!is_array($request->input('reservations')) or count($request->input('reservations')) == 0) {
            throw new \Exception('At least one reservation room is required');
        }

        // cargamos los datos de la cabecere de la reserva
        $this->selectionInit(
            session()->get('user_type'),
            session()->get('selected_executive')['id'],
            session()->get('selected_executive')['name'],
            session()->get('selected_executive')['code'],
            session()->get('selected_client')['id'],
            session()->get('selected_client')["code"],
            session()->get('selected_client_country')['iso'],
            session()->get('selected_client_user_id'),
            0
        );

        $reservation_hotel_id = null;
        foreach ($request->input('reservations') as $reservations) {
            // si n se envia el ID de la reserva hotel en la reserva no hay forma de saber que se quiere modificar asi que
            // se retorna un error en el que se indica que el paramedro del ID (reservation_hotel_code) es obligatorio
            if (empty($reservations['reservation_hotel_code']) or trim($reservations['reservation_hotel_code']) == '') {
                throw new \Exception('Missing reservation_hotel_code to add rooms');
            } else {
                // Ese metodo solo puede canelar un hotel al mismo tiempo
                if ($reservation_hotel_id and $reservation_hotel_id != $reservations['reservation_hotel_code']) {
                    throw new \Exception('Can only edit one Hotel at same time');
                }

                $reservation_hotel_id = trim($reservations['reservation_hotel_code']);
            }
        }

        // obtenermos el registro de la reserva hotel que queremos modificar
        $reservation = Reservation::getReservations([
            'reservation_hotel_id' => $reservation_hotel_id,
        ], true);

        // cancelamos en los channels como esta actualmente para enviarla nuevamente con las fechas nuevas
        DB::transaction(function () use ($reservation) {
            $this->cancelOnChannels($reservation);
        });

        // Comprobar que se cancelaron todas las habitaciones correctamente
        $all_caceled = true;
        foreach ($reservation->reservationsHotel as $reservationsHotel) {
            foreach ($reservationsHotel->reservationsHotelRooms as $reservationsHotelRoom) {
                if ($reservationsHotelRoom['channel_code'] == 'AURORA' and $reservationsHotelRoom['status'] == 3) {
                    if (
                        $reservationsHotelRoom['status'] != 0
                    ) {
                        $all_caceled = true;
                    }
                } else {
                    if (
                        $reservationsHotelRoom['status_in_channel'] != 0
                    ) {
                        $all_caceled = true;
                    }
                }
            }
        }

        // si $all_caceled == true entonces debemos retornar un error indicando que hay problemas para modificar esta reserva
        if (!$all_caceled) {
            throw new \Exception('Some rooms have problems to be modify');
        }

        // eliminamos los registro de la reservas hodel habitacion que se cancelaron ya que no se requieren mas esos datos
        Schema::disableForeignKeyConstraints();
        foreach ($reservation->reservationsHotel as $reservationsHotel) {
            foreach ($reservationsHotel->reservationsHotelRooms as $reservationsHotelRoom) {
                $reservationsHotelRoom->delete();
            }
        }
        Schema::enableForeignKeyConstraints();

        // Armamos la nueva reserva con los datos de las nueva fecha
        /* TODO: Backup de JC */
        // $reservationData = $this->getHotelReservationData($request->input('reservations'));
        /* TODO: ALEX QUISPE */
        $reservationData = [];
        $reservations = $request->input('reservations');

        foreach ($reservations as $reservation) {
            $token_search = $reservation['token_search'] ?? null;
            $rate_plan_room_id = $reservation['rate_plan_room_id'] ?? null;
            $hotel_id = $reservation['hotel_id'] ?? null;
            $check_in = $reservation['date_from'] ?? null;
            $check_out = $reservation['date_to'] ?? null;

            $hotelsSearch = $this->getTokenSearchData($token_search);

            $selectedHotel = collect($hotelsSearch)->first(function ($hotel) use ($hotel_id, $check_in, $check_out) {
                return $hotel['hotel_id'] == $hotel_id and $hotel['check_in'] == $check_in and $hotel['check_out'] == $check_out;
            });

            $rooms = $selectedHotel['hotel']['rooms'] ?? [];
            $selectedRoom = collect($rooms)->first(function ($room) use ($rate_plan_room_id) {
                return collect($room['rates_plan_room'])->contains('id', $rate_plan_room_id) || collect($room['rates_plan_room'])->contains('rateId', $rate_plan_room_id);
            });

            $channel = $selectedRoom['channels'] ?? [];
            $channelId = $channel[0]['id'] ?? null;
            $channelType = $channel[0]['pivot']['type'] ?? null;

            if ($channelId == ChannelHyperguestConfig::CHANNEL_ID && $channelType == ChannelHyperguestConfig::TYPE_CHANNEL) {
                $reservationHotelService = new ReservationHotelService();
                $reservationData = $reservationHotelService->getHotelReservationData(
                    $reservations
                );
            } else {
                // Otros canales
                $reservationData = $this->getHotelReservationData(
                    $reservations
                );
            }
        }
        /* TODO: ALEX QUISPE */

        // Este metodo solo puede cancelar una reserva al mismo tiempo
        if ($reservationData->count() > 1) {
            throw new \Exception('Can only edit one Hotel for the same dates');
        }

        $reservationData = $reservationData->first();
        $current_date = Carbon::now('America/Lima')->startOfDay();
        $check_in = Carbon::parse($reservationData['check_in']);
        $check_out = Carbon::parse($reservationData['check_out']);
        $nights = $reservationData['nights'];

        // obtenemos el registro de la reserva hotel y le incorporamos los datos de la nueva fecha
        $RH = ReservationsHotel::find($reservation_hotel_id);

        $RH->check_in = $reservationData['check_in'];
        $RH->check_out = $reservationData['check_out'];
        $RH->nights = $reservationData['nights'];

        $reservationHotelData = $reservationData['hotels'][$RH['hotel_id']];
        // El incorporamos los nuevos registro de habitacion para la nueva fecha
        $RH = $this->setReservationRoomData($RH, $reservationHotelData, $current_date, $check_in, $check_out, $nights);

        // cuardamos toda la data en la base de datos
        $RH->reservationsHotelRooms()->saveMany($RH->reservationsHotelRooms);
        foreach ($RH->reservationsHotelRooms as $reservationsHotelRoom) {
            $reservationsHotelRoom->reservationHotelCancelPolicies()->saveMany($reservationsHotelRoom->reservationHotelCancelPolicies);

            $reservationsHotelRoom->reservationsHotelsCalendarys()->saveMany($reservationsHotelRoom->reservationsHotelsCalendarys);
            foreach ($reservationsHotelRoom->reservationsHotelsCalendarys as $reservationsHotelsCalendary) {
                $reservationsHotelsCalendary->reservationHotelRoomDateRate()->saveMany($reservationsHotelsCalendary->reservationHotelRoomDateRate);
            }

            $reservationsHotelRoom->supplements()->saveMany($reservationsHotelRoom->supplements);
            foreach ($reservationsHotelRoom->supplements as $reservationsHotelsSupplements) {
                $reservationsHotelsSupplements->calendaries()->saveMany($reservationsHotelsSupplements->calendaries);
            }
        }

        $RH->save();

        // obtenemos los registros de las reservas que aun no se notificr a los channles
        $reservation_id_to_send = $this->getReservationRoomPending($RH->reservationsHotelRooms);
        if (count($reservation_id_to_send) == 0) {
            return null;
        }

        // obtenemos el objeto de la reserva solamente con las habitaciones que faltan por ser enviadas tanto a stela como
        // a los channels
        $this->reservation = Reservation::getReservations([
            'reservation_hotel_id' => $RH['id'],
            'reservation_hotel_room_id' => $reservation_id_to_send,
        ], true);

        // Guardamos las reservas en lpo channles
        $channelRes = $this->saveOnChannels($this->reservation);

        // obtenemos el listado de los id de las reservas hotel habitacion que se encuentran activas en los channels y
        // en stela para enviar los mailing correspondientes
        $reservation_id_to_send = $this->getReservationRoomToNotif($channelRes['reservation']->reservationsHotel[0]->reservationsHotelRooms);
        $this->reservation = Reservation::getReservations([
            'reservation_hotel_id' => $channelRes['reservation']->reservationsHotel[0]['id'],
            'reservation_hotel_room_id' => $reservation_id_to_send,
        ], true);

        // enviamos los mails que se aran en base al objeto de la reserva
        // SendHotelReservationsEmails::dispatch($this->reservation, 'modification');
        SendHotelReservationsEmails::dispatchNow($this->reservation,
            'modification'); // usar para ejecutar de inmediato (test)

        return ['success' => true, 'data' => $this->reservationToApiResponse($this->reservation)];
    }

    /**
     * @param $reservationsHotelRooms
     * @return array
     */
    public function getReservationRoomPending($reservationsHotelRooms)
    {
        $res_room_ids = [];
        foreach ($reservationsHotelRooms as $reservationsHotelRoom) {
            if ($reservationsHotelRoom['channel_code'] == 'AURORA' and $reservationsHotelRoom['status'] == 3) {
                $res_room_ids[] = $reservationsHotelRoom['id'];
            } elseif ($reservationsHotelRoom['channel_code'] != 'AURORA' and $reservationsHotelRoom['status_in_channel'] == 3) {
                $reservation_id_to_send[] = $reservationsHotelRoom['id'];
            }
        }

        return $res_room_ids;
    }

    /**
     * @param $reservationsHotelRooms
     * @return array
     */
    public function getReservationRoomToNotif($reservationsHotelRooms)
    {
        $res_room_ids = [];
        foreach ($reservationsHotelRooms as $reservationsHotelRoom) {
            if ($reservationsHotelRoom['channel_code'] == 'AURORA' and $reservationsHotelRoom['status'] == 3) {
                if (
                    $reservationsHotelRoom['status'] == 1 or
                    $reservationsHotelRoom['status'] == 2 or
                    $reservationsHotelRoom['status'] == 3
                ) {
                    $res_room_ids[] = $reservationsHotelRoom['id'];
                }
            } else {
                if (
                    $reservationsHotelRoom['status_in_channel'] == 1 or
                    $reservationsHotelRoom['status_in_channel'] == 2
                ) {
                    $res_room_ids[] = $reservationsHotelRoom['id'];
                }
            }
        }

        return $res_room_ids;
    }

    /**
     * @param Request $request
     * @param null $channel_hyperguest_reservations
     * @return void
     * @throws Exception
     */
    private function selectionConstructor(Request $request, $channel_hyperguest_reservations = null, $frontend = false)
    {
        $this->validateReservationAuth($request);

        $this->selectionInit(
            session()->get('user_type'),
            session()->get('selected_executive')['id'],
            session()->get('selected_executive')['name'],
            session()->get('selected_executive')['code'],
            session()->get('selected_client')['id'],
            session()->get('selected_client')["code"],
            session()->get('selected_client_country')['iso'],
            session()->get('selected_client_user_id'),
            session()->get('selected_client')['ecommerce']
        );

        $reservationBilling = collect();
        //Todo: Guardando datos de facturacion
        if ($request->has('billing')) {
            $reservationBilling = $this->getBillingData($request->input('billing'));
        }

        // este parametro solo afecta a las notificaciones que se le he envian al hotel, esta notificacion sera disparada desde files a3 porque estan moficando una reserva y enviaran un formato de moficacion
        $send_mail = $request->has('send_mail') ? $request->input('send_mail') : 1;
        // $files_ms_parameters =  $request->has('files_ms_parameters') ? json_encode($request->input('files_ms_parameters')) : NULL;

        //Todo Guardo datos segun la entidad => [Package,Quote, ....]
        $reservationEntity = $this->getEntityData($request->input('entity_data'), $request->input('entity'));

        //Todo: Guardando pasajeros
        $reservationPassengers = $this->getPassengersData($request->input('guests'));

        // Todo: Guardando hoteles
        $reservationData = $this->getHotelReservationData($request->input('reservations'), $frontend, $send_mail, $request->input('files_ms_parameters'));

        //Todo: Guardando servicios
        $reservationDataService = $this->getServiceReservationData($request->input('reservations_services'));

        //Todo: Guardando vuelos
        $reservationDataFlight = $this->getFlightReservationData($request->input('reservations_flights'));

        //Todo: armar el objeto de la reserva
        $this->setReservationData(
            $reservationData,
            $reservationDataService,
            $reservationDataFlight,
            $reservationBilling,
            $reservationPassengers,
            $reservationEntity,
            $channel_hyperguest_reservations,
            $frontend
        );
    }

    /**
     * @param $reservator_type
     * @param $excecutive_id
     * @param $client_id
     * @param $client_code
     * @param $country_iso
     * @param $client_user_id
     * @param int $client_e_commerce
     * @param string $reference
     * @param string $given_name
     * @param string $surname
     * @param string $customer_country
     * @param string $file_code
     * @return void
     */
    private function selectionInit(
        $reservator_type,
        $excecutive_id,
        $excecutive_name,
        $excecutive_code,
        $client_id,
        $client_code,
        $country_iso,
        $client_user_id,
        $client_e_commerce
    )
    {
        $this->selection['reservator_type'] = $reservator_type;

        $this->selection['selected_excecutive'] = $excecutive_id;

        $this->selection['selected_excecutive_name'] = $excecutive_name;

        $this->selection['executive_code'] = strtoupper(trim($excecutive_code));

        $this->selection['selected_client'] = $client_id;

        $this->selection['client_code'] = $client_code;

        $this->selection['selected_client_country_iso'] = $country_iso;

        $this->selection['client_user_id'] = $client_user_id;

        $this->selection['selected_client_e_commerce'] = $client_e_commerce;

        $this->selection['hotel_allowed_on_request'] = true;
        $this->selection['service_allowed_on_request'] = true;

        //Todo Consulta para saber si el cliente permite hoteles/servicios en onrequest
        $client_config = ClientConfiguration::where('client_id', $client_id)->first();
        if ($client_config) {
            // Todo Permite hoteles/servicios en onrequest = false / permite = true
            $this->selection['hotel_allowed_on_request'] = (bool)$client_config->hotel_allowed_on_request;
            $this->selection['service_allowed_on_request'] = (bool)$client_config->service_allowed_on_request;
        }

        $this->selection['entity'] = (isset($this->selection['entity'])) ? $this->selection['entity'] : null;

        $this->selection['object_id'] = (isset($this->selection['object_id'])) ? $this->selection['object_id'] : null;
    }

    /**
     * @param $reservations
     * @param bool $frontend // si la consulta es desde el front es true
     * @param int $send_mail
     * @param null $files_ms_parameters
     * @return Collection
     */
    private function getHotelReservationData($reservations, $frontend = false, $send_mail = 1, $files_ms_parameters = NULL)
    {
        if (count($reservations) == 0) {
            return collect();
        }

        $totalAdults = collect($reservations)->sum('quantity_adults');

        if (!$totalAdults) {
            $this->reservation_errors->push(trans('validations.reservations.quantity_adults_required'));
        }

        // Validaciones para controlar OnRequest
        $hotelAllowsOnRequest = (bool)data_get($this->selection, 'hotel_allowed_on_request', false);

        // TODO: si el campo hotel_allowed_on_request es false valida que todos los hoteles tengan disponibilidad
        // Validaciones para controlar OnRequest
        if (!$hotelAllowsOnRequest) {
            $checkInventoryAvailabilityRoomInRates = $this->checkInventoryAvailabilityRoomInRates($reservations);
            if ($checkInventoryAvailabilityRoomInRates->count()) {
                throw new Exception($checkInventoryAvailabilityRoomInRates->toJson(), 1004);
            }
        }

        $reservationData = collect();
        $inventories = [];
        $inventoryCache = [];

        foreach ($reservations as $key => $selectedRoomRate) {
            $rnd = (string)(random_int(100000, 999999));
            $roomIdent = $selectedRoomRate['room_ident'] ?? $rnd;
            $optional = $selectedRoomRate['optional'] ?? 0;
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
                    ['hotel' => $hotel_id]), 1001);
            }

            $rate_plan_room_id = $selectedRoomRate['rate_plan_room_id'];
            $check_in = $selectedRoomRate['date_from'];
            $check_out = $selectedRoomRate['date_to'];
            $from = Carbon::parse($check_in);
            $to = Carbon::parse($check_out);
            $reservation_days = $from->diffInDays($to);
            $to = $to->subDay(1)->format('Y-m-d');

            // Todo extraes la data del cache seleccionado (token_search)
            if ($best_option) {
                $selectedRoomData = $this->setRoomRateData($token_search, $roomIdent, $hotel_id, $rate_plan_room_id,
                    $check_in, $check_out, $best_option);
            } else {
                $passengers = $selectedRoomRate['passengers'] ?? NULL; // VIENE DE FILES_MS
                $selectedRoomData = $this->setRoomRateData($token_search, $roomIdent, $hotel_id, $rate_plan_room_id,
                    $check_in, $check_out, $best_option, $selectedRoomRate['quantity_adults'],
                    $selectedRoomRate['quantity_child'], $selectedRoomRate['child_ages'], $passengers);
            }

            $hotel_id = $selectedRoomData['selectedHotel']['hotel']['id'];
            $hotel_name = $selectedRoomData['selectedHotel']['hotel']['name'];
            $hotel_room_name = $selectedRoomData['selectedRoom']['translations'][0]['value'];

            $rateProviderMethod = isset($selectedRoomData['selectedRatePlanRoom']['rate']['rateProviderMethod']) ? $selectedRoomData['selectedRatePlanRoom']['rate']['rateProviderMethod'] : 0;
            // cualquiera que no sea  hyperguest pull
            if ($rateProviderMethod != 2) {

            /* ALEX_QUISPE */

                /**
                 * Reglas de negocio:
                 * - hotel_allowed_on_request puede no existir -> considerar FALSE (no permite RQ)
                 * - onRequest = 0 (RQ)
                 * - onRequest = 1 (OK) -> debe tener inventario
                 * - Hyperguest (channel_id=6) no debe ir en RQ (bloquear si llega only_on_request=true)
                 */

                // onRequest: 0 => RQ | 1 => OK (viene en rate_plan.rate.onRequest)
                $onRequest = (int)data_get($selectedRoomRate, 'rate_plan.rate.onRequest', 1); // default OK

                // Normalizar estrictamente a 0/1
                $onRequest = ($onRequest === 0) ? 0 : 1;

                $isRQ = ($onRequest === 0);
                $isOK = ($onRequest === 1);

                // Canal seleccionado (el rate que se está reservando)
                $selectedChannelId = (int)data_get($selectedRoomData, 'selectedRatePlanRoom.rate.channel_id', 1);
                $isAuroraSelected = ($selectedChannelId === 1);
                $isHyperguestSelected = ($selectedChannelId === 6);

                // Hyperguest nunca permite RQ
                if ($isHyperguestSelected && $isRQ) {
                    throw new Exception(trans('validations.reservations.hotel_room_day_availability', [
                        'hotel_name' => $hotel_name,
                        'room_name' => $hotel_room_name
                    ]), 1004);
                }

                // Aurora solo bloquea RQ si el hotel NO permite onRequest
                if ($isAuroraSelected && !$hotelAllowsOnRequest && $isRQ) {
                    throw new Exception(trans('validations.reservations.hotel_room_day_availability', [
                        'hotel_name' => $hotel_name,
                        'room_name' => $hotel_room_name
                    ]), 1004);
                }

                // 2) Validaciones de inventario SOLO si es Confirmado (onRequest=1)
                if ($isOK && $isHyperguestSelected) { // Hyperguest no valida inventario
                    $errors = $this->checkInventoryAvailabilityRoomInRatesForItem($selectedRoomRate, $reservations, $inventoryCache);
                    if ($errors->count()) {
                        throw new Exception($errors->toJson(), 1004);
                    }

                    // (2.b) Validación puntual por rate/hotel/rango: también una sola vez (mínimo cambio)
                    $rateId = (int)data_get($selectedRoomData, 'selectedRatePlanRoom.rate.id');
                    $hotelId = (int)data_get($selectedRoomData, 'selectedHotel.hotel_id');

                    $hasInventory = $this->getArrayIdsRatesPlansRoomsHaveOneInventory(
                        [$rateId], [$hotelId], $from, $to, $reservation_days
                    );

                    if (count($hasInventory) === 0) {
                        throw new Exception(trans('validations.reservations.hotel_room_day_availability', [
                            'hotel_name' => $hotel_name, 'room_name' => $hotel_room_name
                        ]), 1004);
                    }
                }

            /* ALEX_QUISPE */

            }

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

                $code_ = ChannelHotel::where("hotel_id", $hotel["id"])
                    ->where("channel_id", 1)->first();

                $hotel['hotel_code'] = empty($hotelChannelByCode['AURORA']['code']) ? $code_['code'] : $hotelChannelByCode['AURORA']['code'];


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
            if ($selectedRoomData['selectedRatePlanRoom']['rate']['channel']['code'] == 'HYPERGUEST') {
                $room_code = empty($roomChannelsByCode['HYPERGUEST']['code']) ? '' : $roomChannelsByCode['HYPERGUEST']['code'];
            } else {
                $room_code = empty($roomChannelsByCode['AURORA']['code']) ? '' : $roomChannelsByCode['AURORA']['code'];
            }

            if (!$hotel['rooms']->offsetExists($room_id . '_' . $key)) {
                $roomTranslations = array_column($selectedRoomData['selectedRoom']['translations'], 'value', 'slug');
                $hotel['rooms']->offsetSet($room_id . '_' . $key, collect([
                    'id' => $selectedRoomData['selectedRoom']['id'],
                    'hotel_id' => $selectedRoomData['selectedRoom']['hotel_id'],
                    'room_type_id' => $selectedRoomData['selectedRoom']['room_type_id'],
                    'max_capacity' => $selectedRoomData['selectedRoom']['max_capacity'],
                    'min_adults' => $selectedRoomData['selectedRoom']['min_adults'],
                    'max_adults' => $selectedRoomData['selectedRoom']['max_adults'],
                    'max_child' => $selectedRoomData['selectedRoom']['max_child'],
                    'room_code' => $room_code,
                    'name' => empty($roomTranslations['room_name']) ? '' : $roomTranslations['room_name'],
                    'description' => empty($roomTranslations['room_description']) ? '' : $roomTranslations['room_description'],
                ]));
            } else {
                $roomTranslations = array_column($selectedRoomData['selectedRoom']['translations'], 'value', 'slug');
                $hotel['rooms']->add([
                    'id' => $selectedRoomData['selectedRoom']['id'],
                    'hotel_id' => $selectedRoomData['selectedRoom']['hotel_id'],
                    'room_type_id' => $selectedRoomData['selectedRoom']['room_type_id'],
                    'max_capacity' => $selectedRoomData['selectedRoom']['max_capacity'],
                    'min_adults' => $selectedRoomData['selectedRoom']['min_adults'],
                    'max_adults' => $selectedRoomData['selectedRoom']['max_adults'],
                    'max_child' => $selectedRoomData['selectedRoom']['max_child'],
                    'room_code' => $room_code,
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

            /** @var Collection $hotel */
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

            $rateProviderMethod = isset($selectedRoomData['selectedRatePlanRoom']['rate']['rateProviderMethod']) ? $selectedRoomData['selectedRatePlanRoom']['rate']['rateProviderMethod'] : 0;

            $policies_cancelation = [];
            // es hyperguest pull
            if ($rateProviderMethod == 2) {
                $policies_cancelation = $selectedRoomData['selectedRatePlanRoom']['rate']['policies_cancelation']['cancellation'];
                $policies_cancelation_details = $policies_cancelation['details'];

                $police_apply = $policies_cancelation_details[0];
                if ((float)$police_apply['penalizacion_usd'] <= 0) {
                    $first_penalty_date = Carbon::parse($police_apply['hasta'])->format('Y-m-d');
                } else {
                    $first_penalty_date = Carbon::now('America/Lima')->startOfDay()->format('Y-m-d'); // Le ponemos la fecha de hoy para que cobre penalidad
                }
            } else {
                $first_penalty_date = !empty($selectedRoomData['selectedRatePlanRoom']['policy_cancellation']['apply_date']) ? $selectedRoomData['selectedRatePlanRoom']['policy_cancellation']['apply_date'] : Carbon::now('America/Lima')->startOfDay()->format('Y-m-d');
                $policies_cancelation = $selectedRoomData['selectedRatePlanRoom']['rate']['policies_cancelation'];
            }

            // dd($policies_cancelation, $police_apply);
            // Validación de Inventory..

            if (($selectedRoomData['selectedRatePlanRoom']['rate']['rateProvider'] ?? 1) != ChannelHyperguestConfig::CHANNEL_ID && ($selectedRoomData['selectedRatePlanRoom']['rate']['rateProviderMethod'] ?? null) != ChannelHyperguestConfig::TYPE_CHANNEL) {

                if (isset($selectedRoomData['selectedRatePlanRoom']['rate']['bag'])) {
                    if ($selectedRoomData['selectedRatePlanRoom']['rate']['bag'] == 1) {
                        $__inventories = $selectedRoomData['selectedRatePlanRoom']['rate']['bag_rate']['bag_room']['inventory_bags'] ?? [];
                    } else {
                        if (isset($selectedRoomData['selectedRatePlanRoom']['rate']['inventories'])) {
                            $__inventories = $selectedRoomData['selectedRatePlanRoom']['rate']['inventories'];
                        }
                    }
                }

                if (isset($__inventories) && count($__inventories) >= $reservation_days) {
                    $key_group_room = date("dmY", strtotime($check_in)) . '_' . $hotel['hotel']['hotel_code'] . '_' . $selectedRoomData['selectedRatePlanRoom']['rate']['id'];

                    $inventory = $inventories[$key_group_room] ?? null;
                    $locked = false;

                    if (is_null($inventory)) {
                        $min_inventory = $__inventories[0]['inventory_num'] ?? 0;
                        $locked = $__inventories[0]['locked'] ?? false;

                        foreach ($__inventories as $inventory) {
                            if ($inventory['inventory_num'] < $min_inventory) {
                                $min_inventory = $inventory['inventory_num'];
                            }

                            if ($inventory['locked']) {
                                $locked = $inventory['locked'];
                            }
                        }

                        $inventory = $min_inventory;
                    }
                    $selectedRoomData['selectedRatePlanRoom']['rate']['status'] = ($inventory > 0 && !$locked) ? 1 : 0;
                    $inventories[$key_group_room] = $inventory - 1;

                } else {
                    $selectedRoomData['selectedRatePlanRoom']['rate']['status'] = 0;
                }
            }

            $rates_plans_rooms[$roomIdent] = [
                'chain_id' => $hotel['hotel']['chain_id'],
                'hotel_id' => $hotel['hotel']['id'],
                'hotel_name' => $hotel['hotel']['name'],
                'hotel_code' => $hotel['hotel']['hotel_code'],
                'check_in' => $check_in,
                'check_out' => $check_out,
                'first_penalty_date' => $first_penalty_date,
                'policies_cancelation' => $policies_cancelation,
                'token_search_channel' => $selectedRoomRate['rate_plan']['rate']['token_search_channel'] ?? null,
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

    /**
     * @param $reservations
     * @return Collection
     * @throws Exception
     */
    private function getServiceReservationData($reservations): Collection
    {
        //TODO Recorro los servicios
        $reservationDataService = collect();
        if ($reservations) {
            foreach ($reservations as $selectedService) {
                $serviceIdent = $selectedService['service_ident'];
                $optional = (isset($selectedService['optional'])) ? $selectedService['optional'] : 0;
                $token_search = $selectedService['token_search'];
                $service_id = $selectedService['service_id'];
                $assigned_passengers = (isset($selectedService['assigned_passengers'])) ? $selectedService['assigned_passengers'] : [];
                $passengers = (isset($selectedService['passengers'])) ? json_encode($selectedService['passengers']) : NULL; // viene de files_ms
                $package_id = isset($selectedService['package_id']) ? $selectedService['package_id'] : null;
                $reservation_time = ($selectedService['reservation_time'] == null) ? Carbon::now('America/Lima')->format('H:i') : $selectedService['reservation_time'];
                $date = $selectedService['date_from'];
                $selectedServiceFind = $this->setServiceRateData($token_search, $service_id, $date,
                    $selectedService['quantity_adults'], $selectedService['quantity_child'],
                    $selectedService['child_ages']);
                $supplements = [];
                if (isset($selectedService['supplements']) && count($selectedService['supplements']) > 0) {
                    $supplements = $this->setServiceDataAdditional($selectedService['supplements']);
                }

                $multiservices = [];
                if (isset($selectedService['multiservices']) && count($selectedService['multiservices']) > 0) {
                    foreach ($selectedService['multiservices'] as $multiservice) {

                        foreach ($selectedServiceFind['components'] as $multi_s) {
                            if ($multiservice['token_search'] === $multi_s['token_search']) {
                                array_push($multiservices, $multi_s);
                                break;
                            }
                            foreach ($multi_s['substitutes'] as $substitute) {
                                if ($multiservice['token_search'] === $substitute['token_search']) {
                                    array_push($multiservices, $substitute);
                                    break;
                                }
                            }
                        }

                    }
                }

                $reservationDataService->add(collect([
                    'service' => $selectedServiceFind,
                    'supplements' => $supplements,
                    'multiservices' => $multiservices,
                    'service_ident' => $serviceIdent,
                    'package_id' => $package_id,
                    'reservation_time' => $reservation_time,
                    'assigned_passengers' => $assigned_passengers,
                    'file_associated_passenger' => $passengers,
                    'optional' => $optional,
                    'date' => $date
                ]));

            }
        }
        return $reservationDataService;
    }

    /**
     * @param $reservations
     * @return array
     * @throws Exception
     */
    private function getPackageReservationData($reservations)
    {
        //TODO Recorro los servicios del paquete
        $reservationDataPackage = collect();
        if ($reservations) {
            foreach ($reservations as $selectedPackage) {

            }

        }
    }

    /**
     * @param $flights
     * @return array
     * @throws Exception
     */
    private function getFlightReservationData($reservations)
    {
        //TODO Recorro los servicios
        $reservationDataFlight = collect();
        if ($reservations) {
            foreach ($reservations as $selectedFlight) {
                $reservationDataFlight->add(collect([
                    'origin' => $selectedFlight['origin'],
                    'destiny' => $selectedFlight['destiny'],
                    'date' => $selectedFlight['date'],
                    'code_flight' => $selectedFlight['code_flight'],
                    'quantity_persons' => $selectedFlight['quantity_persons']
                ]));
            }
        }

        return $reservationDataFlight;
    }

    private function getBillingData($billing)
    {
        $billingData = collect();
        if ($billing) {
            $billingData->add([
                'name' => $billing['name'],
                'surnames' => $billing['surnames'],
                'phone' => isset($billing['phone']) ? $billing['phone'] : null,
                'email' => isset($billing['email']) ? $billing['email'] : null,
                'document_type_id' => $billing['document_type_id'],
                'document_number' => $billing['document_number'],
                'address' => isset($billing['address']) ? $billing['address'] : null,
                'country_id' => $billing['country_id'],
                'state_id' => isset($billing['state_id']) ? $billing['state_id'] : null,
                'state_ifx_iso' => isset($billing['state_iso']) ? $billing['state_iso'] : null
            ]);
        }
        return $billingData;
    }

    private function getPassengersData($passengers)
    {
        $passengersData = collect();
        if ($passengers) {
            foreach ($passengers as $index => $passenger) {

                $date_birth = null;
                $ageValidator = new AgeValidator();

                if (isset($passenger['date_birth'])) {
                    $date_birth = $passenger['date_birth'];
                    $dateIsInvalid = !$ageValidator->isValidDate($date_birth);

                    if ($dateIsInvalid) {
                        $date_birth = null;
                    }
                }

                if ($date_birth !== null) {
                    $isUnderdage = $ageValidator->isChild($date_birth);

                    if ($passenger['type'] == 'ADL' && $isUnderdage) {
                        $date_birth = null;
                    }
                }


                $passengersData->add([
                    'doctype_iso' => isset($passenger['doctype_iso']) ? $passenger['doctype_iso'] : null,
                    'sequence_number' => (isset($passenger['sequence_number']) and !empty($passenger['sequence_number'])) ? $passenger['sequence_number'] : $index + 1,
                    'document_number' => isset($passenger['document_number']) ? $passenger['document_number'] : null,
                    'name' => $passenger['given_name'],
                    'surnames' => $passenger['surname'],
                    'date_birth' => $date_birth,
                    'genre' => isset($passenger['genre']) ? $passenger['genre'] : null,
                    'type' => isset($passenger['type']) ? $passenger['type'] : 'ADL',
                    'dietary_restrictions' => isset($passenger['dietary_restrictions']) ? $passenger['dietary_restrictions'] : null,
                    'medical_restrictions' => isset($passenger['medical_restrictions']) ? $passenger['medical_restrictions'] : null,
                    'email' => isset($passenger['email']) ? $passenger['email'] : null,
                    'phone' => isset($passenger['phone']) ? $passenger['phone'] : null,
                    'country_iso' => isset($passenger['country_iso']) ? $passenger['country_iso'] : null,
                    'city_iso' => isset($passenger['city_iso']) ? $passenger['city_iso'] : null,
                    'notes' => isset($passenger['notes']) ? $passenger['notes'] : null,
                    'used' => false,
                    'document_url' => isset($passenger['document_url']) ? $passenger['document_url'] : null,
                ]);
            }
        }

        return $passengersData;
    }

    private function getEntityData($entity_data, $entity = '')
    {
        $entityData = collect();
        if (!empty($entity_data)) {
            if ($entity == 'Package') {
                $entityData->add([
                    'name' => $entity_data['name'],
                    'quantity_adults' => $entity_data['adults'],
                    'type_class_id' => $entity_data['type_class_id'],
                    'service_type_id' => $entity_data['service_type_id'],
                    'quantity_child_with_bed' => $entity_data['children_with_bed'],
                    'quantity_child_without_bed' => $entity_data['children_without_bed'],
                    'quantity_sgl' => $entity_data['quantity_sgl'],
                    'quantity_dbl' => $entity_data['quantity_dbl'],
                    'quantity_child_dbl' => $entity_data['quantity_child_dbl'],
                    'quantity_tpl' => $entity_data['quantity_tpl'],
                    'quantity_child_tpl' => $entity_data['quantity_child_tpl'],
                    'price_per_adult_sgl' => $entity_data['price_per_adult_sgl'],
                    'price_per_adult_dbl' => $entity_data['price_per_adult_dbl'],
                    'price_per_adult_tpl' => $entity_data['price_per_adult_tpl'],
                    'price_per_child_with_bed' => $entity_data['price_per_child_with_bed'],
                    'price_per_child_without_bed' => $entity_data['price_per_child_without_bed'],
                    'cancellation_policy' => $entity_data['cancellation_policy'],
                ]);
            }
        }

        return $entityData;
    }

    /**
     * @param $reservationData
     * @param $reservationDataService
     * @param $reservationDataFlight
     * @param $reservationBilling
     * @param $reservationPassengers
     * @param $reservationEntity
     * @param null $channel_hyperguest_reservation_code
     * @return Reservation
     * @throws Exception
     */
    public function setReservationData(
        $reservationData,
        $reservationDataService,
        $reservationDataFlight,
        $reservationBilling,
        $reservationPassengers,
        $reservationEntity,
        $channel_hyperguest_reservations = null,
        $frontend=false
    )
    {
        $totals_previous_reservation = [
            'hotels' => 0,
            'hotels_subs' => 0,
            'hotels_discounts' => 0,
            'services' => 0,
            'services_subs' => 0,
            'services_taxes' => 0,
            'total_tax' => 0,
            'subtotal_amount' => 0,
            'total_amount' => 0,
            'total_discounts' => 0
        ];

        $new_booking = true;
        if (!$this->reservation) {
            if ($this->selection['file_code'] == '') {
                //Todo Insertamos datos de facturacion (Pasajero directo)
                $billing_id = $this->createBilling($reservationBilling);
                //Todo Verificamos si no tiene datos de facturacion creamos el file de forma directa
                if ($billing_id == null) {
                    //Todo Si no hay datos de facturacion pasamos a crear el file
                    $status_cron_job_reservation_stella = Reservation::STATUS_CRONJOB_CREATE_FILE;
                } else {
                    //Todo hay datos de facturacion creamos el cliente primero
                    $status_cron_job_reservation_stella = Reservation::STATUS_CRONJOB_CREATE_BILLING_DATA;
                }
                $booking_code = createFileCode($this->selection['selected_client_country_iso']);


                if($frontend == false)
                {
                    $get_file_code = $this->getNumberFile();
                    if (!$get_file_code->success) {
                        throw new \Exception(trans('validations.reservations.could_not_generated_file_number'));
                    } else {
                        $file_code = $get_file_code->nroref;
                    }
                }else{
                    $file_code = $booking_code;
                }


                $date_init = $this->getDateInitServices($file_code, $reservationDataService, $reservationData);
                $this->selection['booking_code'] = $booking_code;
                $this->selection['file_code'] = $file_code;
                $this->reservation = new Reservation();
                $this->reservation->booking_code = $this->selection['booking_code'];
                $this->reservation->file_code = $this->selection['file_code'];
                $this->reservation->payment_code = $this->selection['payment_code'];
                $this->reservation->reservation_billing_id = $billing_id;
                $this->reservation->status = 1;
                //Todo reservator_type = [client,executive]
                $this->reservation->reservator_type = $this->selection['reservator_type'];
                //Todo entity = [Package,Quote,Stella,Cart]
                $this->reservation->entity = $this->selection['entity'];
                $this->reservation->object_id = $this->selection['object_id'];
                $this->reservation->client_id = $this->selection['selected_client'];
                $this->reservation->client_code = $this->selection['client_code'];
                $this->reservation->executive_id = $this->selection['selected_excecutive'];
                $this->reservation->executive_name = $this->selection['selected_excecutive_name'];
                $this->reservation->customer_name = $this->getReferenceFile($this->selection['reference'],
                    $reservationPassengers);
                $this->reservation->given_name = $this->selection['given_name'];
                $this->reservation->surname = $this->selection['surname'];
                $this->reservation->customer_country = $this->selection['customer_country'];
                $this->reservation->order_number = $this->selection['order_number'];
                $this->reservation->markup = $this->selection['markup'];
                $this->reservation->type_class_id = $this->selection['type_class_id'];
                $this->reservation->total_hotels_taxes = 0;
                $this->reservation->total_hotels_services = 0;
                $this->reservation->total_hotels_discounts = 0;
                $this->reservation->total_hotels_subs = 0;
                $this->reservation->total_hotels = 0;
                //Servicios - totales
                $this->reservation->total_services = 0;
                $this->reservation->total_services_subs = 0;
                $this->reservation->total_services_taxes = 0;
                $this->reservation->date_init = $date_init;
                $this->reservation->create_user_id = Auth::id();
                $this->reservation->update_user_id = Auth::id();
                $this->reservation->reference = $this->selection['reference_new'];
                $this->reservation->status_cron_job_reservation_stella = $status_cron_job_reservation_stella;
//                $this->reservation->status_cron_job_send_email = Reservation::STATUS_CRONJOB_SEND_EMAIL_RESERVE;
            } else {
                $new_booking = false;
                // $this->reservation = Reservation::where('file_code', '=', $this->selection['file_code'])->latest('created_at')->first();
                $this->reservation = Reservation::where('file_code', '=', $this->selection['file_code'])->first();
                if ($this->reservation) {
                    // Asignación de valores de total de reservation, solo cuando sea paquete
                    if ($this->selection['entity'] === 'Package') {
                        $totals_previous_reservation = [
                            'hotels' => $this->reservation->total_hotels,
                            'hotels_subs' => $this->reservation->total_hotels_subs,
                            'hotels_discounts' => $this->reservation->total_hotels_discounts,
                            'services' => $this->reservation->total_services,
                            'services_subs' => $this->reservation->total_services_subs,
                            'services_taxes' => $this->reservation->total_services_taxes,
                            'total_tax' => $this->reservation->total_tax,
                            'subtotal_amount' => $this->reservation->subtotal_amount,
                            'total_amount' => $this->reservation->total_amount,
                            'total_discounts' => $this->reservation->total_discounts
                        ];
                    }
                    //Todo Validamos que el cliente que se selecciono se el mismo del file a modificar
                    if ($this->reservation->client_id != $this->selection['selected_client']) {
                        throw new Exception(
                            trans('validations.reservations.client_does_not_belong_to_the_file',
                                [
                                    'file' => $this->reservation->client_id . " - " . $this->selection['selected_client'],
                                    'selected_client' => $this->selection['selected_client'],
                                    'client_id' => $this->reservation->client_id
                                ]
                            )
                        );
                    }

                    $date_init = $this->getDateInitServices($this->selection['file_code'], $reservationDataService,
                        $reservationData, true);
                    $this->reservation->date_init = $date_init;
                    $this->reservation->version = ((int)$this->reservation->version + 1);
                    $this->reservation->update_user_id = Auth::id();
                    $this->reservation->status_cron_job_reservation_stella = Reservation::STATUS_CRONJOB_CREATE_FILE;
                    $this->reservation->status_cron_job_send_email = Reservation::STATUS_CRONJOB_WITHOUT_SEND_EMAIL_RESERVE;

                    $reference = $this->getReferenceFile($this->selection['reference'], $reservationPassengers);
                    if (trim($reference)) {
                        $this->reservation->customer_name = trim($reference);
                    }

                } else {
                    throw new Exception(trans('validations.reservations.file_number_not_found',
                        ['file' => $this->selection['file_code']]), 404);
                }
            }
        }

        $this->reservation->setConsecutiveHotelPrev();

        //Todo Hoteles: Guardo en la tabla reservations_hotels
        foreach ($reservationData as $token_dates => $reservationDatum) {
            $current_date = Carbon::now('America/Lima')->startOfDay();
            $check_in = Carbon::parse($reservationDatum['check_in']);
            $check_out = Carbon::parse($reservationDatum['check_out']);
            $nights = $reservationDatum['nights'];
            $package_id = $reservationDatum['package_id'];
            $optional = $reservationDatum['optional'];
            $send_mail = $reservationDatum['send_mail'];


            foreach ($reservationDatum['hotels'] as $reservationHotelData) {
                // AGREGAREMOS EL ID DE LA REGION
                $regionId = $this->getHotelRegionId($reservationHotelData['hotel']['hotel_code']);

                //Excluir hoteles con precio dinámico
                $ratePlans = $reservationHotelData['rates_plans'] ?? [];

                $hasDynamic = false;
                foreach ($ratePlans as $plan) {
                    if (($plan['price_dynamic'] ?? 0) == 1) {
                        $hasDynamic = true;
                    }
                }

                if ($hasDynamic) {
                    continue; // Salta este hotel y sigue con el siguiente
                }

                //Excluir hoteles con precio dinámico
                $ratePlans = $reservationHotelData['rates_plans'] ?? [];

                $hasDynamic = false;
                foreach ($ratePlans as $plan) {
                    if (($plan['price_dynamic'] ?? 0) == 1) {
                        $hasDynamic = true;
                    }
                }

                if ($hasDynamic) {
                    continue; // Salta este hotel y sigue con el siguiente
                }

                $files_ms_parameters = isset($reservationHotelData['files_ms_parameters']) ? $reservationHotelData['files_ms_parameters'] : NULL;

                $RH = new ReservationsHotel();
                $RH->consecutive = $this->reservation->reservationsHotel->count();
                $RH->status = 3; //0: cancelada, 1: activa, 2: modificada, 3: por confirmar
                $RH->status_in_channel = 3;

                $RH->consecutive = $this->reservation->reservationsHotel->count();

                $RH->executive_id = $this->selection['selected_excecutive'];
                $RH->executive_name = $this->selection['selected_excecutive_name'];
                $RH->executive_code = $this->selection['executive_code'];
                $RH->reservator_type = $this->selection['reservator_type']; // 'client' , 'excecutive'
                $RH->chain_id = $reservationHotelData['hotel']['chain_id'];
                $RH->hotel_id = $reservationHotelData['hotel']['id'];
                $RH->hotel_name = $reservationHotelData['hotel']['name'];
                $RH->hotel_code = $reservationHotelData['hotel']['hotel_code'];
                $RH->notes = @$reservationHotelData['hotel']['notes'];
                $RH->stars = $reservationHotelData['hotel']['stars'];
                $RH->check_in = $check_in;
                $RH->check_out = $check_out;
                $RH->check_in_time = $reservationHotelData['hotel']['check_in_time'];
                $RH->check_out_time = $reservationHotelData['hotel']['check_out_time'];
                $RH->nights = $nights;
                $RH->optional = $optional;
                $RH->total_amount = 0;
                $RH->total_tax_and_services_amount = 0;
                $RH->total_amount_base = 0;
                $RH->package_id = $package_id;
                $RH->send_mail = $send_mail;
                $RH->files_ms_parameters = $files_ms_parameters;
                $RH->business_region_id = $regionId;

                $this->reservation->reservationsHotel->add($RH);
                $RH = $this->setReservationRoomData(
                    $RH,
                    $reservationHotelData,
                    $current_date,
                    $check_in,
                    $check_out,
                    $nights,
                    $channel_hyperguest_reservations
                );
            }
        }
        $this->reservation->setConsecutiveservicePrev();
        $reservationDataService = $reservationDataService ? $reservationDataService : array();
        $total_services_compensation = 0;

        //Todo Servicios: Guardo en la tabla reservations_services
        foreach ($reservationDataService as $reservationService) {

            $date = Carbon::parse($reservationService['date']);
            $reservation_time = Carbon::parse($reservationService['reservation_time']);
            $RS = $this->reservation->reservationsService->filter(function ($item) use ($reservationService, $date) {
                return $item->service_id == $reservationService['service']['id'] and $item->date_reserve == $date;
            })->first();

            //Todo se guarda en la tabla: reservations_services
            if (!$RS) {
                $regionId = $this->getServiceRegionId($reservationService['service']['code']);

                $markup_rate_client = isset($this->selection['selected_client'])
                    ? MarkupService::where('client_id', $this->selection['selected_client'])
                        ->where('service_id', $reservationService['service']['id'])
                        ->where('period', Carbon::parse($date)->year)
                        ->whereNull('deleted_at')
                        ->first()
                    : null;

                $markup_service = $reservationService['service']['rate']['markup'];
                $total_amount_service = $reservationService['service']['total_amount'];
                $total_amount_base_service = $reservationService['service']['total_base_amount'];


                if ($markup_rate_client) {
                    $price_adl_service = $reservationService["service"]["rate"]["rate_plans"][0]["price_adult"] ?? null;
                    $price_chd_service = $reservationService["service"]["rate"]["rate_plans"][0]["price_child"] ?? null;
                    $qty_pax = intval($reservationService["service"]["quantity_adult"] ?? 0);
                    $qty_child = intval($reservationService['service']['quantity_child'] ?? 0);

                    $total_amount_service_adl = 0;
                    $total_amount_service_chd = 0;
                    $total_base_amount_service_adl = 0;
                    $total_base_amount_service_chd = 0;

                    if (!empty($price_adl_service) && $qty_pax > 0) {
                        $markup_service = $markup_rate_client->markup;
                        $total_amount_service_adl = roundLito($price_adl_service + ($price_adl_service * ($markup_service / 100))) * $qty_pax;
                        $total_base_amount_service_adl = $price_adl_service * $qty_pax;
                    }

                    if (!empty($price_chd_service) && $qty_child > 0) {
                        $markup_service = $markup_rate_client->markup;
                        $total_amount_service_chd = roundLito($price_chd_service + ($price_chd_service * ($markup_service / 100))) * $qty_child;
                        $total_base_amount_service_chd = $price_chd_service * $qty_child;
                    }

                    if ($price_adl_service > 0 || $price_chd_service > 0) {
                        $total_amount_service = $total_amount_service_adl + $total_amount_service_chd;
                        $total_amount_base_service = $total_base_amount_service_adl + $total_base_amount_service_chd;
                    }
                }

                $RS = new ReservationsService();
                $RS->consecutive = $this->reservation->reservationsService->count();
                $RS->status = 3; //0: cancelada, 1: activa, 2: modificada, 3: por confirmar
                $RS->executive_id = $this->selection['selected_excecutive'];
                $RS->executive_name = $this->selection['selected_excecutive_name'];
                $RS->executive_code = $this->selection['executive_code'];
                $RS->reservator_type = $this->selection['reservator_type']; // 'client' , 'excecutive'
                $RS->service_id = $reservationService['service']['id'];
                $RS->service_inventory_id = $reservationService['service']['rate']['inventory_id'];
                $RS->service_rate_id = $reservationService['service']['rate']['id'];
                $RS->service_name = $reservationService['service']['name'];
                $RS->service_code = $reservationService['service']['code'];
                $RS->notes = @$reservationService['service']['notes'];
                $RS->date = $reservationService['service']['date_reserve'];
                //Si la categoria es 13 = Tren, la hora se guarda en null
                $isTrain = $reservationService['service']['category']['service_category_id'] == 13;
                $RS->time = ($isTrain) ? null : $reservation_time;
                $RS->compensation = $reservationService['service']['compensation'];
                $RS->total_amount = $total_amount_service;
                $RS->total_amount_base = $total_amount_base_service;
                $RS->total_amount_taxes = $reservationService['service']['total_taxes'];
                $RS->markup = $reservationService['service']['rate']['markup'];
                $RS->affected_markup = $reservationService['service']['affected_markup'];
                $RS->adult_num = $reservationService['service']['quantity_adult'];
                $RS->child_num = $reservationService['service']['quantity_child'];
                $RS->on_request = $reservationService['service']['on_request'];
                $RS->total_amount_supplements = 0;
                $RS->package_id = $reservationService['package_id'];
                $RS->infant_num = 0;
                $RS->optional = $reservationService['optional'];
                $RS->assigned_passengers = json_encode($reservationService['assigned_passengers']);
                $RS->file_associated_passenger = $reservationService['file_associated_passenger'];
                $RS->guest_note = '';
                $RS->type_service = 'service';
                $RS->parent_id = null;
                // REGION ID
                $RS->business_region_id = $regionId;

                if ($this->selection['entity'] === 'Package' and (boolean)$reservationService['service']['compensation']) {
                    $total_services_compensation += $reservationService['service']['total_amount'];
                }

                $this->reservation->reservationsService->add($RS);

                //Todo se guarda en la tabla: reservations_services_rates_plans
                foreach ($reservationService['service']['rate']['rate_plans'] as $rates_plans) {
                    $RSRP = new ReservationsServicesRatesPlans();
                    $RSRP->status = 1;//0: cancelada, 1: activa, 2: modificada, 3: por confirmar
                    $RSRP->executive_id = $this->selection['selected_excecutive'];
                    $RSRP->service_id = $reservationService['service']['id'];
                    $RSRP->service_name = $reservationService['service']['name'];
                    $RSRP->date = $reservationService['service']['date_reserve'];
                    $RSRP->service_rate_plan_id = $rates_plans['id'];
                    $RSRP->service_rate_id = $reservationService['service']['rate']['id'];
                    $RSRP->service_rate_name = $reservationService['service']['rate']['name'];
                    $RSRP->date_from = $rates_plans['date_from'];
                    $RSRP->date_to = $rates_plans['date_to'];
                    $RSRP->pax_from = $rates_plans['pax_from'];
                    $RSRP->pax_to = $rates_plans['pax_to'];
                    $RSRP->price_adult = $rates_plans['price_adult'];
                    $RSRP->price_child = $rates_plans['price_child'];
                    $RSRP->price_infant = $rates_plans['price_infant'];
                    $RSRP->price_guide = $rates_plans['price_guide'];
                    $RSRP->adult_num = $reservationService['service']['quantity_adult'];
                    $RSRP->child_num = $reservationService['service']['quantity_child'];
                    $RSRP->policies_cancellation = json_encode($rates_plans['political']['cancellation']['penalties']);
                    $RSRP->infant_num = 0;
                    $RS->reservationsServiceRatesPlans->add($RSRP);
                    //Todo se guarda en la tabla: reservations_services_rates_plans_cancellation_policies
                    foreach ($rates_plans['political']['cancellation']['parameters'] as $policy) {
                        $RSRPCP = new ReservationsServicesRatesPlansCancellationPolicies();
                        $RSRPCP->policy_cancelation_id = $rates_plans['political']['id'];
                        $RSRPCP->name = $rates_plans['political']['name'];
                        $RSRPCP->service_id = $reservationService['service']['id'];
                        $RSRPCP->policy_cancelations_parameter_id = $policy['id'];
                        $RSRPCP->min_pax = is_null($policy['min_num']) ? 0 : $policy['min_num'];
                        $RSRPCP->max_pax = is_null($policy['max_num']) ? 0 : $policy['max_num'];
                        $RSRPCP->min_hour = $policy['from'];
                        $RSRPCP->max_hour = $policy['to'];
                        $RSRPCP->amount = $policy['amount'];
                        $RSRPCP->tax = $policy['tax'];
                        $RSRPCP->service = $policy['service'];
                        $RSRPCP->penalty_id = $policy['penalty_id'];
                        $RSRPCP->unit_duration = $policy['unit_duration'];
                        $RSRPCP->penalty_name = $policy['penalty_name'];
                        $RSRP->reservationServiceCancelPolicies->add($RSRPCP);
                    }
                }

                $parent_id = $reservationService['service']['id'] . '-' . $reservationService['service']['date_reserve'];
                //Todo Servicios Suplmentos: Guardo en la tabla reservations_services con el campo type_service = 'supplement'
                foreach ($reservationService['supplements'] as $supplement) {
                    $date_reserve = Carbon::createFromFormat('d/m/Y', $supplement['date']);
                    $RSP = new ReservationsService();
                    $RSP->consecutive = $this->reservation->reservationsService->count();
                    $RSP->status = 1; //0: cancelada, 1: activa, 2: modificada, 3: por confirmar
                    $RSP->executive_id = $this->selection['selected_excecutive'];
                    $RSP->executive_name = $this->selection['selected_excecutive_name'];
                    $RSP->executive_code = $this->selection['executive_code'];
                    $RSP->reservator_type = $this->selection['reservator_type']; // 'client' , 'excecutive'
                    $RSP->service_id = $supplement['supplement']['id'];
                    $RSP->service_inventory_id = null;
                    $RSP->service_rate_id = $supplement['rate']['id'];
                    $RSP->service_name = $supplement['supplement']['name'];
                    $RSP->service_code = $supplement['supplement']['aurora_code'];
                    $RSP->date = $date_reserve->format('Y-m-d');
                    $RSP->time = $reservation_time;
                    $RSP->type_service = 'supplement';
                    $RSP->parent_id = $parent_id;
                    $RSP->total_amount = $supplement['total_prices']['total_amount'];
                    $RSP->total_amount_base = $supplement['total_prices']['total_amount'];
                    $RSP->total_amount_taxes = 0;
                    $RSP->markup = $reservationService['service']['rate']['markup'];
                    $RSP->affected_markup = $reservationService['service']['affected_markup'];
                    $RSP->adult_num = $supplement['adults'];
                    $RSP->child_num = $supplement['child'];
                    $RSP->on_request = 0;
                    $RSP->infant_num = 0;
                    $RSP->guest_note = '';
                    $RSP->business_region_id = $this->getServiceRegionId($supplement['supplement']['aurora_code']);
                    $this->reservation->reservationsService->add($RSP);

                    $name_rate = (count($supplement['rate']['translations']) > 0) ? $supplement['rate']['translations'][0]['value'] : '';

                    //Todo se guarda en la tabla: reservations_services_rates_plans
                    foreach ($supplement['rate']['service_rate_plans'] as $rates_plans) {
                        $RSRP_SP = new ReservationsServicesRatesPlans();
                        $RSRP_SP->status = 1;//0: cancelada, 1: activa, 2: modificada, 3: por confirmar
                        $RSRP_SP->executive_id = $this->selection['selected_excecutive'];
                        $RSRP_SP->service_id = $supplement['supplement']['id'];
                        $RSRP_SP->service_name = $supplement['supplement']['name'];
                        $RSRP_SP->date = $date_reserve;
                        $RSRP_SP->service_rate_plan_id = $rates_plans['id'];
                        $RSRP_SP->service_rate_id = $supplement['rate']['id'];
                        $RSRP_SP->service_rate_name = $name_rate;
                        $RSRP_SP->date_from = $rates_plans['date_from'];
                        $RSRP_SP->date_to = $rates_plans['date_to'];
                        $RSRP_SP->pax_from = $rates_plans['pax_from'];
                        $RSRP_SP->pax_to = $rates_plans['pax_to'];
                        $RSRP_SP->price_adult = $rates_plans['price_adult'];
                        $RSRP_SP->price_child = $rates_plans['price_child'];
                        $RSRP_SP->price_infant = $rates_plans['price_infant'];
                        $RSRP_SP->price_guide = $rates_plans['price_guide'];
                        $RSRP_SP->adult_num = $supplement['adults'];
                        $RSRP_SP->child_num = $supplement['child'];
                        $RSRP_SP->policies_cancellation = json_encode([]);
                        $RSRP_SP->infant_num = 0;
                        $RSP->reservationsServiceRatesPlans->add($RSRP_SP);

                        foreach ($rates_plans['policy']['parameters'] as $policy) {
                            $RSRPCP_SP = new ReservationsServicesRatesPlansCancellationPolicies();
                            $RSRPCP_SP->policy_cancelation_id = $rates_plans['policy']['id'];
                            $RSRPCP_SP->name = $rates_plans['policy']['name'];
                            $RSRPCP_SP->service_id = $supplement['supplement']['id'];
                            $RSRPCP_SP->policy_cancelations_parameter_id = $policy['id'];
                            $RSRPCP_SP->min_pax = is_null($rates_plans['policy']['min_num']) ? 0 : $rates_plans['policy']['min_num'];
                            $RSRPCP_SP->max_pax = is_null($rates_plans['policy']['max_num']) ? 0 : $rates_plans['policy']['max_num'];
                            $RSRPCP_SP->min_hour = $policy['min_hour'];
                            $RSRPCP_SP->max_hour = $policy['max_hour'];
                            $RSRPCP_SP->amount = $policy['amount'];
                            $RSRPCP_SP->tax = $policy['tax'];
                            $RSRPCP_SP->service = $policy['service'];
                            $RSRPCP_SP->penalty_id = $policy['penalty']['id'];
                            $RSRPCP_SP->unit_duration = $policy['unit_duration'];
                            $RSRPCP_SP->penalty_name = $policy['penalty']['name'];
                            $RSRP_SP->reservationServiceCancelPolicies->add($RSRPCP_SP);
                        }
                    }
                }

                //Todo Servicios Multiservicios: Guardo en la tabla reservations_services con el campo type_service = 'multiservice'
                foreach ($reservationService['multiservices'] as $multiservice) {
                    $date_ = Carbon::parse($multiservice['date_reserve']);
                    $reservation_time_ = Carbon::parse($multiservice['reservation_time']);
                    $RS_ = $this->reservation->reservationsService->filter(function ($item) use (
                        $multiservice,
                        $date_
                    ) {
                        return $item->service_id == $multiservice['id'] and $item->date_reserve == $date_;
                    })->first();
                    //Todo se guarda en la tabla: reservations_services
                    if (!$RS_) {
                        $RS_ = new ReservationsService();
                        $RS_->consecutive = $this->reservation->reservationsService->count();
                        $RS_->status = 3; //0: cancelada, 1: activa, 2: modificada, 3: por confirmar
                        $RS_->executive_id = $this->selection['selected_excecutive'];
                        $RS_->executive_name = $this->selection['selected_excecutive_name'];
                        $RS_->executive_code = $this->selection['executive_code'];
                        $RS_->reservator_type = $this->selection['reservator_type']; // 'client' , 'excecutive'
                        $RS_->service_id = $multiservice['id'];
                        $RS_->service_inventory_id = $multiservice['rate']['inventory_id'];
                        $RS_->service_rate_id = $multiservice['rate']['id'];
                        $name_ = $multiservice['descriptions']['name_commercial'];
                        if ($name_ == null) {
                            $name_ = $multiservice['name'];
                        }
                        $RS_->service_name = $name_;
                        $RS_->service_code = $multiservice['code'];
                        $RS_->date = $multiservice['date_reserve'];
                        $RS_->time = $reservation_time_;
                        $RS_->total_amount = $multiservice['total_amount'];
                        $RS_->total_amount_base = $multiservice['total_base_amount'];
                        $RS_->total_amount_taxes = $multiservice['total_taxes'];
                        $RS_->markup = $multiservice['rate']['markup'];
                        $RS_->affected_markup = $multiservice['affected_markup'];
                        $RS_->adult_num = $multiservice['quantity_adult'];
                        $RS_->child_num = $multiservice['quantity_child'];
                        $RS_->on_request = $multiservice['on_request'];
                        $RS_->total_amount_supplements = 0;
                        $RS_->package_id = $reservationService['package_id'];
                        $RS_->infant_num = 0;
                        $RS_->guest_note = '';
                        $RS_->type_service = 'multiservice';
                        $RS_->parent_id = $parent_id;
                        $RS_->business_region_id = $this->getServiceRegionId($multiservice['code']);
                        $this->reservation->reservationsService->add($RS_);

                        //Todo se guarda en la tabla: reservations_services_rates_plans
                        foreach ($multiservice['rate']['rate_plans'] as $rates_plans) {
                            $RS_RP = new ReservationsServicesRatesPlans();
                            $RS_RP->status = 1;//0: cancelada, 1: activa, 2: modificada, 3: por confirmar
                            $RS_RP->executive_id = $this->selection['selected_excecutive'];
                            $RS_RP->service_id = $multiservice['id'];
                            $RS_RP->service_name = $multiservice['name'];
                            $RS_RP->date = $multiservice['date_reserve'];
                            $RS_RP->service_rate_plan_id = $rates_plans['id'];
                            $RS_RP->service_rate_id = $multiservice['rate']['id'];
                            $RS_RP->service_rate_name = $multiservice['rate']['name'];
                            $RS_RP->date_from = $rates_plans['date_from'];
                            $RS_RP->date_to = $rates_plans['date_to'];
                            $RS_RP->pax_from = $rates_plans['pax_from'];
                            $RS_RP->pax_to = $rates_plans['pax_to'];
                            $RS_RP->price_adult = $rates_plans['price_adult'];
                            $RS_RP->price_child = $rates_plans['price_child'];
                            $RS_RP->price_infant = $rates_plans['price_infant'];
                            $RS_RP->price_guide = $rates_plans['price_guide'];
                            $RS_RP->adult_num = $multiservice['quantity_adult'];
                            $RS_RP->child_num = $multiservice['quantity_child'];
                            $RS_RP->policies_cancellation = json_encode($rates_plans['political']['cancellation']['penalties']);
                            $RS_RP->infant_num = 0;
                            $RS_->reservationsServiceRatesPlans->add($RS_RP);
                            //Todo se guarda en la tabla: reservations_services_rates_plans_cancellation_policies
                            foreach ($rates_plans['political']['cancellation']['parameters'] as $policy) {
                                $RS_RPCP = new ReservationsServicesRatesPlansCancellationPolicies();
                                $RS_RPCP->policy_cancelation_id = $rates_plans['political']['id'];
                                $RS_RPCP->name = $rates_plans['political']['name'];
                                $RS_RPCP->service_id = $multiservice['id'];
                                $RS_RPCP->policy_cancelations_parameter_id = $policy['id'];
                                $RS_RPCP->min_pax = is_null($policy['min_num']) ? 0 : $policy['min_num'];
                                $RS_RPCP->max_pax = is_null($policy['max_num']) ? 0 : $policy['max_num'];
                                $RS_RPCP->min_hour = $policy['from'];
                                $RS_RPCP->max_hour = $policy['to'];
                                $RS_RPCP->amount = $policy['amount'];
                                $RS_RPCP->tax = $policy['tax'];
                                $RS_RPCP->service = $policy['service'];
                                $RS_RPCP->penalty_id = $policy['penalty_id'];
                                $RS_RPCP->unit_duration = $policy['unit_duration'];
                                $RS_RPCP->penalty_name = $policy['penalty_name'];
                                $RS_RP->reservationServiceCancelPolicies->add($RS_RPCP);
                            }
                        }
                    }
                }

            }
        }

        $reservationDataFlight = $reservationDataFlight ? $reservationDataFlight : array();

        //Todo Si es una nueva reserva creamos los datos en vuelos y pasajeros
        if ($new_booking) {
            //Todo Vuelos: Guardo en la tabla reservations_flights
            foreach ($reservationDataFlight as $reservationFlight) {
                $date = Carbon::parse($reservationFlight['date']);
                $RS = $this->reservation->reservationsFlight->filter(function ($item) use ($reservationFlight, $date) {
                    return $item->date == $date; // $item->service_id == $reservationFlight['service_id'] and $item->date == $date;
                })->first();
                if (!$RS) {
                    $RS = new ReservationsFlight();
                    $RS->origin = $reservationFlight['origin'];
                    $RS->destiny = $reservationFlight['destiny'];
                    $RS->date = $reservationFlight['date'];
                    $RS->code_flight = $reservationFlight['code_flight'];
                    $RS->adult_num = $reservationFlight['quantity_persons']['adults'];
                    $RS->child_num = $reservationFlight['quantity_persons']['child'];
                    $RS->inf_num = 0;
                    $this->reservation->reservationsFlight->add($RS);
                }

            }
            //Todo Pasajeros: Guardo en la tabla reservation_passengers
            foreach ($reservationPassengers as $index => $passenger) {
                $pax = new ReservationPassenger();
                $pax->sequence_number = $passenger['sequence_number'];
                $pax->doctype_iso = $passenger['doctype_iso'];
                $pax->name = $passenger['name'];
                $pax->surnames = $passenger['surnames'];
                $pax->document_number = $passenger['document_number'];
                $pax->date_birth = $passenger['date_birth'];
                $pax->genre = $passenger['genre'];
                $pax->type = $passenger['type'];
                $pax->email = $passenger['email'];
                $pax->phone = $passenger['phone'];
                $pax->country_iso = $passenger['country_iso'];
                $pax->city_iso = $passenger['city_iso'];
                $pax->dietary_restrictions = $passenger['dietary_restrictions'];
                $pax->medical_restrictions = $passenger['medical_restrictions'];
                $pax->notes = $passenger['notes'];
                $pax->document_url = $passenger['document_url'];
                $this->reservation->reservationsPassenger->add($pax);
            }
        } else {
            //Todo Pasajeros: Modifico en la tabla reservation_passengers
            foreach ($reservationPassengers as $index => $passenger) {
                $this->reservation->reservationsPassenger->transform(function ($item, $key) use ($passenger, $index) {
                    if ($index == $key) {
                        $item->sequence_number = $passenger['sequence_number'];
                        $item->doctype_iso = $passenger['doctype_iso'];
                        $item->name = $passenger['name'];
                        $item->surnames = $passenger['surnames'];
                        $item->document_number = $passenger['document_number'];
                        $item->date_birth = $passenger['date_birth'];
                        $item->genre = $passenger['genre'];
                        $item->type = $passenger['type'];
                        $item->email = $passenger['email'];
                        $item->phone = $passenger['phone'];
                        $item->country_iso = $passenger['country_iso'];
                        $item->city_iso = $passenger['city_iso'];
                        $item->dietary_restrictions = $passenger['dietary_restrictions'];
                        $item->medical_restrictions = $passenger['medical_restrictions'];
                        $item->notes = $passenger['notes'];
                        $item->document_url = $passenger['document_url'];
                        $item->save();
                    }
                    return $item;
                });
            }
        }


        //Todo Paquetes: Guardo en la tabla reservations_packages si el entity es igual a Package
        if ($this->selection['entity'] === 'Package') {
            foreach ($reservationEntity as $entity_date) {
                $entity = new ReservationsPackage();
                $entity->package_id = $this->selection['object_id'];
                $entity->name = $entity_date['name'];
                $entity->type_class_id = $entity_date['type_class_id'];
                $entity->service_type_id = $entity_date['service_type_id'];
                $entity->quantity_adults = $entity_date['quantity_adults'];
                $entity->quantity_child_with_bed = $entity_date['quantity_child_with_bed'];
                $entity->quantity_child_without_bed = $entity_date['quantity_child_without_bed'];
                $entity->quantity_sgl = $entity_date['quantity_sgl'];
                $entity->quantity_dbl = $entity_date['quantity_dbl'];
                $entity->quantity_tpl = $entity_date['quantity_tpl'];
                $entity->price_per_adult_sgl = $entity_date['price_per_adult_sgl'];
                $entity->price_per_adult_dbl = $entity_date['price_per_adult_dbl'];
                $entity->price_per_adult_tpl = $entity_date['price_per_adult_tpl'];
                $entity->price_per_child_with_bed = $entity_date['price_per_child_with_bed'];
                $entity->price_per_child_without_bed = $entity_date['price_per_child_without_bed'];
                $entity->cancellation_policy = json_encode($entity_date['cancellation_policy']);
                $this->reservation->reservationsPackage->add($entity);
            }
        }


//        $this->setReservavationDiscounts();

        // Hoteles totales
        $this->reservation->total_hotels_discounts = number_format(
            $this->reservation->reservationsDiscount->sum('total_discount') + $totals_previous_reservation['hotels_discounts'],
            2, '.', '');
        $this->reservation->total_hotels_subs = number_format(
            $this->reservation->reservationsHotel->sum('total_amount_base') + $totals_previous_reservation['hotels_subs'],
            2, '.', '');
        $this->reservation->total_hotels = number_format(
            $this->reservation->reservationsHotel->sum('total_amount') + $totals_previous_reservation['hotels']
            , 2, '.', '');


        // Servicios totales
        $total_services = $this->reservation->reservationsService->sum('total_amount');

        $this->reservation->total_services = number_format(
            $total_services + $totals_previous_reservation['services']
            , 2, '.', '');

        $this->reservation->total_services_subs = number_format(
            $total_services + $totals_previous_reservation['services_subs']
            , 2, '.', '');

        $this->reservation->total_services_taxes = number_format(
            $this->reservation->reservationsService->sum('total_amount_taxes') + $totals_previous_reservation['services_taxes']
            , 2, '.', '');

        // Total descuento
        if ($this->selection['total_discounts'] == 0) {
            $this->reservation->total_discounts = $this->reservation->total_hotels_discounts;
        } else {
            $this->reservation->total_discounts = $this->selection['total_discounts'] + $totals_previous_reservation['total_discounts'];
        }

        // Sub-Total
        if ($this->selection['subtotal_amount'] == 0) {
            $this->reservation->subtotal_amount = $this->reservation->total_hotels_subs + $this->reservation->total_services_subs;
        } else {
            $this->reservation->subtotal_amount = $this->selection['subtotal_amount'] + $total_services_compensation + $totals_previous_reservation['subtotal_amount'];
        }

        //Total impuestos
        if ($this->selection['total_tax'] == 0) {
            $this->reservation->total_tax = $this->reservation->total_hotels + $this->reservation->total_services_taxes;
        } else {
            $this->reservation->total_tax = $this->selection['total_tax'] + $totals_previous_reservation['total_tax'];
        }

        //Total de servicios + hoteles
        if ($this->selection['total_amount'] == 0) {
            $this->reservation->total_amount = $this->reservation->total_hotels + $total_services;
        } else {
            $this->reservation->total_amount = $this->selection['total_amount'] + $total_services_compensation + $totals_previous_reservation['total_amount'];
        }

        return $this->reservation;
    }

    /**
     * @param $RH
     * @param $reservationHotelData
     * @param $current_date
     * @param $check_in
     * @param $check_out
     * @param $nights
     * @param null $channel_hyperguest_reservations
     * @return Reservation
     */
    public function setReservationRoomData(
        $RH,
        $reservationHotelData,
        $current_date,
        $check_in,
        $check_out,
        $nights,
        $channel_hyperguest_reservations = null
    )
    {
        $channel_hyperguest_reservation_code = null;
        if ($channel_hyperguest_reservations !== null) {

            foreach ($channel_hyperguest_reservations as $channel_hyperguest_reservation) {
                if ($channel_hyperguest_reservation["hotel_id"] == $reservationHotelData["hotel"]["id"] &&
                    $channel_hyperguest_reservation["date_from"] == $check_in->format('Y-m-d') &&
                    $channel_hyperguest_reservation["date_to"] == $check_out->format('Y-m-d')) {
                    $channel_hyperguest_reservation_code = $channel_hyperguest_reservation["booking_id"];
                }
            }
        }

        foreach ($reservationHotelData['rates_plans_rooms'] as $ratePlanRoomIdent => $rates_plans_rooms) {
            //Todo sacamos la cantidad de habitaciones seleccionadas
            $rooms_quantity = $reservationHotelData['rates_plans_rooms']->count();

            //Todo contamos la cantidad total de pasajeros seleccionados
            $guest_quantity = 0;
            foreach ($rates_plans_rooms as $rates_plans_room) {
                if (isset($rates_plans_room['adult_num'])) {
                    $guest_quantity += $rates_plans_room['adult_num'];
                    $guest_quantity += $rates_plans_room['child_num'];
                }
            }

            if ($guest_quantity == 0) {
                continue;
            }

            foreach ($rates_plans_rooms as $roomIdent => $rates_plans_room) {

                if (empty(@$rates_plans_room['adult_num']) || $rates_plans_room['adult_num'] == 0) {
                    continue;
                }

                $RHRPR = new ReservationsHotelsRatesPlansRooms();
                $status = 3;
                $status_in_channel = 3;
                $channel_reservation_code = "";

                $RHRPR->status = $status;//0: cancelada, 1: activa, 2: modificada, 3: por confirmar
                $RHRPR->status_in_channel = $status_in_channel;//0: cancelada, 1: activa, 2: modificada, 3: por confirmar

                $RHRPR->executive_id = $this->selection['selected_excecutive'];
                $RHRPR->executive_name = $this->selection['selected_excecutive_name'];

                $RHRPR->token_search_channel = $rates_plans_room['token_search_channel'] ?? null; // Token de busqueda en canal

                $RHRPR->chain_id = $rates_plans_room['chain_id'];
                $RHRPR->hotel_id = $rates_plans_room['hotel_id'];
                $RHRPR->hotel_name = $rates_plans_room['hotel_name'];
                $RHRPR->hotel_code = $rates_plans_room['hotel_code'];

                $RHRPR->check_in = $rates_plans_room['check_in'];
                $RHRPR->check_out = $rates_plans_room['check_out'];
                $RHRPR->check_in_time = $reservationHotelData['hotel']['check_in_time'];
                $RHRPR->check_out_time = $reservationHotelData['hotel']['check_out_time'];
                $RHRPR->nights = $nights;

                if (!isset($rates_plans_room['policies_cancelation'])) {
                    $rates_plans_room['policies_cancelation'] = array();
                }

                //Todo calcular las politicas de cancelacion

                $hayperguest_pull = isset($rates_plans_room['policies_cancelation']['hayperguest_pull']) ? true : false;

                if ($hayperguest_pull == false) {

                    //Todo calcular las politicas de cancelacion
                    if ($rates_plans_room['channel_code'] == 'AURORA') {
                        $selected_policies_cancelation = collect($rates_plans_room['calendarys'][0]['policies_rates']['policies_cancelation']);
                    } else {
                        if (count($rates_plans_room['policies_cancelation']) == 0) {
                            $selected_policies_cancelation = collect($rates_plans_room["calendarys"][0]["policies_cancelation"]);
                        } else {
                            $selected_policies_cancelation = collect($rates_plans_room['policies_cancelation']);
                        }
                    };

                    $policy_cancellation_calculated = $this->calculateCancellationlPolicies($current_date,
                        $check_in, $check_out, $rates_plans_room["total_amount"],
                        $selected_policies_cancelation, $guest_quantity, $rooms_quantity);

                    $RHRPR->policies_cancellation = json_encode($policy_cancellation_calculated['penalties']);
                    $RHRPR->taxes_and_services = json_encode($rates_plans_room['taxes_and_services']);
                    $RHRPR->first_penalty_date = Carbon::parse($policy_cancellation_calculated['next_penalty']['apply_date'])->format('Y-m-d');

                }else{

                    $RHRPR->policies_cancellation = json_encode($rates_plans_room['policies_cancelation']);
                    $RHRPR->taxes_and_services = json_encode([]);
                    $RHRPR->first_penalty_date = $rates_plans_room['first_penalty_date'];

                }



                $RHRPR->channel_id = $rates_plans_room['channel_id'];
                $RHRPR->channel_code = $rates_plans_room['channel_code'];
                $RHRPR->channel_hotel_code = $rates_plans_room['channel_hotel_code'];
                $RHRPR->channel_room_code = $rates_plans_room['channel_room_code'];
                $RHRPR->channel_reservation_code = $channel_reservation_code;

                $RHRPR->room_id = $rates_plans_room['room_id'];
                $RHRPR->room_name = $rates_plans_room['room_name'];
                $RHRPR->room_code = $rates_plans_room['room_code'];
                $RHRPR->room_description = $rates_plans_room['room_description'];
                $RHRPR->room_type_id = $rates_plans_room['room_type_id'];
                $RHRPR->max_capacity = $rates_plans_room['max_capacity'];
                $RHRPR->min_adults = $rates_plans_room['min_adults'];
                $RHRPR->max_adults = $rates_plans_room['max_adults'];
                $RHRPR->max_child = $rates_plans_room['max_child'];

                // dd($rates_plans_room);

                // if ($rates_plans_room['channel_id'] != 6) {
                $RHRPR->rates_plan_id = $rates_plans_room['rates_plan_id'];
                $RHRPR->rate_plan_code = $rates_plans_room['rate_plan_code'];
                $RHRPR->rate_plan_name = $rates_plans_room['rate_plan_name'];
                $RHRPR->rate_plan_name_commercial = $rates_plans_room['rate_plan_name_commercial'];
                // }

                $RHRPR->meal_id = $rates_plans_room['meal_id'];
                $RHRPR->meal_name = $rates_plans_room['meal_name'];
                $RHRPR->rates_plans_room_id = $rates_plans_room['rates_plans_room_id'];

                if ($rates_plans_room['channel_code'] == "6") {
                    $RHRPR->onRequest = 0;
                } else {
                    $RHRPR->onRequest = $rates_plans_room['onRequest'];
                }

                $RHRPR->adult_num = $rates_plans_room['adult_num'];
                $RHRPR->child_num = $rates_plans_room['child_num'];
                $RHRPR->guest_note = $rates_plans_room['guest_note'];
                $RHRPR->is_modification = (isset($rates_plans_room['is_modification'])) ? $rates_plans_room['is_modification'] : 0;
                $RHRPR->payment_status = 0;
                $RHRPR->markup = $rates_plans_room['markup'];
                $RHRPR->total_amount = number_format($rates_plans_room['total_amount'], 2, '.', '');
                $RHRPR->total_tax_and_services_amount = number_format($rates_plans_room['taxes_and_services']['amount_fees'],
                    2, '.', '');
                $RHRPR->total_amount_base = number_format($rates_plans_room['total_amount_base'], 2, '.', '');

                $RHRPR->observations = '';
                $RHRPR->associated_passenger = isset($rates_plans_room['passengers']) ? json_encode($rates_plans_room['passengers']) : NULL;

                $RH->total_amount += $rates_plans_room['total_amount'];
                $RH->total_tax_and_services_amount += $rates_plans_room['taxes_and_services']['amount_fees'];
                $RH->total_amount_base += $rates_plans_room['total_amount_base'];


                $RH->reservationsHotelRooms->put($ratePlanRoomIdent . '_' . $roomIdent, $RHRPR);

                if (isset($policy_cancellation_calculated['selected_policy_cancelation']['policy_cancellation_parameter'])) {
                    foreach ($policy_cancellation_calculated['selected_policy_cancelation']['policy_cancellation_parameter'] as $policy_cancellation_parameter) {
                        $policies_rate = Penalty::find($policy_cancellation_parameter['penalty_id'])->name;

                        $RHRPRCP = new ReservationsHotelsRatesPlansRoomsCancellationPollicies();
                        $RHRPRCP->policy_cancelation_id = $policy_cancellation_calculated['selected_policy_cancelation']['id'];
                        $RHRPRCP->name = $policy_cancellation_calculated['selected_policy_cancelation']['name'];
                        $RHRPRCP->hotel_id = $policy_cancellation_calculated['selected_policy_cancelation']['hotel_id'];
                        $RHRPRCP->policy_cancelations_parameter_id = $policy_cancellation_parameter['id'];
                        $RHRPRCP->min_day = $policy_cancellation_parameter['min_day'];
                        $RHRPRCP->max_day = $policy_cancellation_parameter['max_day'];
                        $RHRPRCP->amount = empty($policy_cancellation_parameter['amount']) ? 0 : $policy_cancellation_parameter['amount'];
                        $RHRPRCP->tax = $policy_cancellation_parameter['tax'];
                        $RHRPRCP->service = $policy_cancellation_parameter['service'];
                        $RHRPRCP->penalty_id = $policy_cancellation_parameter['penalty_id'];
                        $RHRPRCP->penalty_name = $policies_rate;
                        $RHRPR->reservationHotelCancelPolicies->add($RHRPRCP);
                    }
                }

                foreach ($rates_plans_room['calendarys'] as $date) {
                    $RHRPRC = new ReservationsHotelsRatesPlansRoomsCalendarys();
                    $RHRPRC->rates_plans_calendary_id = $date['id'];
                    $RHRPRC->rates_plans_room_id = $date['rates_plans_room_id'];
                    $RHRPRC->policies_rate_id = $date['policies_rate_id'];
                    $RHRPRC->policies_cancelation_id = $date['policies_cancelation_id'];
                    $RHRPRC->date = $date['date'];
                    $RHRPRC->status = $date['status'];
                    $RHRPRC->max_ab_offset = $date['max_ab_offset'];
                    $RHRPRC->min_ab_offset = $date['min_ab_offset'];
                    $RHRPRC->min_length_stay = $date['min_length_stay'];
                    $RHRPRC->max_length_stay = $date['max_length_stay'];
                    $RHRPRC->max_occupancy = $date['max_occupancy'];
                    $RHRPRC->total_amount = $date['total_amount'];
                    $RHRPRC->total_amount_base = $date['total_amount_base'];
                    $RHRPRC->policies_rates = $rates_plans_room['channel_code'] == 'AURORA' ? json_encode($date['policies_rates']) : '{}';

                    $RHRPR->reservationsHotelsCalendarys->add($RHRPRC);

                    $rate = $date['rate'][0];
                    $RHRPRCR = new ReservationsHotelsRatesPlansRoomsCalendarysRates();
                    $RHRPRCR->rate_id = isset($rate['old_rate']) ? 0 : $rate['id'];
                    $RHRPRCR->rates_plans_calendarys_id = isset($rate['old_rate']) ? 0 : $rate['rates_plans_calendarys_id'];
//                            $RHRPRCR->num_adult = $rate['num_adult'];
                    $RHRPRCR->num_adult = empty($rate['num_adult']) ? 0 : $rate['num_adult'];
                    $RHRPRCR->num_child = empty($rate['num_child']) ? 0 : $rate['num_child'];
                    $RHRPRCR->price_adult = $rate['price_adult'];
                    $RHRPRCR->price_child = $rate['price_child'];
                    $RHRPRCR->price_adult_base = $rate['price_adult_base'];
                    $RHRPRCR->price_child_base = $rate['price_child_base'];
                    $RHRPRCR->price_extra_base = $rate['price_extra_base'];
                    $RHRPRCR->old_rate = empty($rate['old_rate']) ? '{}' : json_encode($rate['old_rate']);

                    $RHRPRC->reservationHotelRoomDateRate->add($RHRPRCR);
                }

            }
        }

        $RH->total_amount = number_format($RH->total_amount, 2, '.', '');
        $RH->total_amount_base = number_format($RH->total_amount_base, 2, '.', '');


        return $RH;
    }

    /**
     *
     */
    public function setReservavationDiscounts()
    {
        $chainsHotels = $this->reservation->reservationsHotel->groupBy('chain_id');
//        foreach ($this->selection['discounts'] as $chain_id => $discount) {
        foreach ($chainsHotels as $chain_id => $resHotels) {
            $discountsChain = ChainsMultipleProperty::where('chain_id', '=', $chain_id)
                ->where('discount', '>', 0)
                ->where('quantity', '<=', count(array_unique($resHotels->pluck('hotel_id')->toArray())))
                ->orderBy('quantity', 'desc')
                ->first();

            if ($discountsChain) {
                if ($discountsChain->quantity > 0) {
                    $RHRPRCR = new ReservationsDiscounts();
                    $RHRPRCR->chains_multiple_propertie_id = $discountsChain->id;
                    $RHRPRCR->chain_id = $chain_id;
                    $RHRPRCR->hotel_num = empty($discountsChain->quantity) ? 0 : $discountsChain->quantity;
                    $RHRPRCR->percent_discount = empty($discountsChain->discount) ? 0 : $discountsChain->discount;
                    $RHRPRCR->total_hotels = $resHotels->sum('total_amount');
                    $RHRPRCR->total_discount = ($RHRPRCR->total_hotels / $RHRPRCR->percent_discount);

                    $this->reservation->reservationsDiscount->add($RHRPRCR);
                }
            }
        }
    }

    /**
     * @param Reservation $reservation
     * @return Collection
     */
    public function reservationToApiResponse(Reservation $reservation, $resumido = false)
    {
        $responseResumido = collect($reservation->only([
            'file_code',
        ]));

        $responseResumido->offsetSet('reservations_hotel', collect());
        $responseResumido->offsetSet('reservations_service', collect());
        $responseResumido->offsetSet('reservations_flight', collect());

        $response = collect($reservation->only([
            'id',
            'client_id',
            'file_code',
            'booking_code',
            'executive_id',
            'customer_name',
            'given_name',
            'surname',
            'date_init',
            'customer_country',
            'total_hotels_discounts',
            'total_hotels_subs',
            'total_hotels',
        ]));

        $response->offsetSet('total_tax_and_services_amount', 0);
        $response->offsetSet('total_service_supplement_selected', 0);
        $response->offsetSet('reservations_hotel', collect());
        $response->offsetSet('reservations_service', collect());
        $response->offsetSet('reservations_flight', collect());
        $response->offsetSet('min_date', '');
        $response->offsetSet('client', []);
        $response->offsetSet('reservation_errors', $this->reservation_errors);

        $min_date = '';

        $reservation->reservationsHotel->each(function (ReservationsHotel $reservationsHotel, $key) use (
            $response,
            $responseResumido,
            &$min_date
        ) {
            $reservations_hotel = collect($reservationsHotel->only([
                'status',
                'status_in_channel',
                'hotel_id',
                'hotel_name',
                'hotel_code',
                'check_in',
                'check_out',
                'check_in_time',
                'check_out_time',
                'total_amount',
            ]));
            $reservations_hotel->prepend($reservationsHotel->id, 'reservation_code');
            $reservations_hotel->offsetSet('total_tax_and_services_amount', 0);

            $logo = Galery::where('type', 'hotel')
                ->where('type', 'hotel')
                ->where('object_id', $reservationsHotel->hotel_id)
                ->get()->first();

            $reservations_hotel->offsetSet('hotel_logo', isset($logo['url']) ? $logo['url'] : '');

            $reservations_hotel->offsetSet('reservations_hotel_rooms', collect());

            $reservationsHotel->reservationsHotelRooms->each(function (
                ReservationsHotelsRatesPlansRooms $reservationsHotelRooms,
                                                  $roomInd
            ) use ($reservations_hotel, &$min_date) {
                $room = collect($reservationsHotelRooms->only([
                    'status',
                    'status_in_channel',
                    'first_penalty_date',
                    'room_name',
                    'room_code',
                    'room_description',
                    'room_type_id',
                    'rate_plan_code',
                    'rate_plan_name',
                    'meal_name',
                    'rates_plans_room_id',
                    'onRequest',
                    'adult_num',
                    'child_num',
                    'guest_note',
                    'observations',
                    'supplements',
                    'total_amount',
                ]));
                $room->prepend($reservationsHotelRooms->id, 'reservation_room_code');

                $room['policies_cancellation'] = collect($reservationsHotelRooms['policies_cancellation'] ? json_decode($reservationsHotelRooms['policies_cancellation'],
                    true) : []);
                $room['taxes_and_services'] = collect($reservationsHotelRooms['taxes_and_services'] ? json_decode($reservationsHotelRooms['taxes_and_services'],
                    true) : []);

                if ($room['first_penalty_date'] < $min_date or $min_date == '') {
                    $min_date = $room['first_penalty_date'];
                }

                if (isset($room['taxes_and_services']['apply_fees'])) {
                    foreach ($room['taxes_and_services']['apply_fees'] as $apply_fee) {
                        foreach ($apply_fee as $item) {
                            $reservations_hotel['total_tax_and_services_amount'] += $item['total_amount'];
                        }
                    }
                }

                $reservations_hotel['reservations_hotel_rooms']->put($roomInd, $room);
            });

            $reservations_hotel['total_tax_and_services_amount'] = (float)number_format($reservations_hotel['total_tax_and_services_amount'],
                2, '.', '');

            $response['reservations_hotel']->add($reservations_hotel);


            $responseResumidoRoom = [];
            foreach ($reservations_hotel['reservations_hotel_rooms'] as $room) {
                array_push($responseResumidoRoom, [
                    'reservation_room_code' => $room['reservation_room_code'],
                    'room_code' => $room['room_code'],
                    'room_name' => $room['room_name'],
                    'room_description' => $room['room_description'],
                    'onRequest' => $room['onRequest'],
                    'adult_num' => $room['adult_num'],
                    'child_num' => $room['child_num'],
                    'meal_name' => $room['meal_name'],
                    'rate_plan_name' => $room['rate_plan_name']
                    // 'policies_cancellation' => $room['policies_cancellation']
                ]);
            }

            $responseResumido['reservations_hotel']->add(collect([
                'reservation_code' => $reservationsHotel->id,
                'hotel_code' => $reservationsHotel->hotel_code,
                'hotel_name' => $reservationsHotel->hotel_name,
                'check_in' => $reservationsHotel->check_in,
                'check_out' => $reservationsHotel->check_out,
                'total_amount' => $reservationsHotel->total_amount,
                'reservations_hotel_rooms' => $responseResumidoRoom
            ]));


        });

        //Servicios
        $reservation->reservationsService->each(function (ReservationsService $reservationsService, $key) use (
            $response,
            $responseResumido
        ) {

            $reservations_service = collect($reservationsService->only([
                'status',
                'service_id',
                'service_name',
                'service_code',
                'date',
                'adult_num',
                'child_num',
                'guest_note',
                'total_amount',
                'total_amount_base',
                'total_amount_taxes',
                'total_amount_supplements',
                'type_service',
                'parent_id',
                'on_request',
            ]));
            $total_service_supplement_selected = 0;
            $reservations_service->prepend($total_service_supplement_selected, 'total_service_supplement_selected');

            $reservations_service->prepend($reservationsService->id, 'reservation_code');

            $logo = Galery::where('type', 'service')
                ->where('object_id', $reservationsService->service_id)
                ->get()->first();
            $reservations_service->offsetSet('service_logo', isset($logo['url']) ? $logo['url'] : '');
            $response['reservations_service']->add($reservations_service);

            $responseResumido['reservations_service']->add([
                'service_id' => $reservationsService->service_id,
                'service_code' => $reservationsService->service_code,
                'service_name' => $reservationsService->service_name,
                'date' => $reservationsService->date,
                'check_out' => $reservationsService->check_out,
                'total_amount' => $reservationsService->total_amount
            ]);

        });

        //Hotel
        $response['total_tax_and_services_amount'] = (float)number_format($response['reservations_hotel']->sum('total_tax_and_services_amount'),
            2, '.', '');
        $response['total_hotels_subs'] = (float)number_format($response['total_hotels'] - $response['total_tax_and_services_amount'],
            2, '.', '');
        $response['total_hotels'] = (float)$response['total_hotels'];
        //Servicios
        $total_services = $response['reservations_service']->sum('total_amount') + $response['reservations_service']->sum('total_service_supplement_selected');
        $response['total_services_tax_amount'] = (float)number_format($response['reservations_service']->sum('total_amount_taxes'),
            2, '.', '');
        $response['total_services_subs'] = (float)number_format($total_services,
            2, '.', '');
        $response['total_services'] = (float)number_format($total_services, 2,
            '.', '');
        //Total servicios + hoteles
        $total = $response['total_hotels'] + $response['total_tax_and_services_amount'] + $response['total_services'];
        $response['total'] = (float)number_format($total, 2, '.', '');
        $response['min_date'] = $min_date;
        $response['client'] = Client::where('id', '=', $response['client_id'])->first()->toArray();

        $reservation->reservationsFlight->each(function (ReservationsFlight $reservationsFlight, $key) use (
            $response,
            $responseResumido
        ) {

            $reservations_flight = collect($reservationsFlight->only([
                'date',
                'adult_num',
                'child_num',
                'code_flight',
                'origin',
                'destiny'
            ]));

            $response['reservations_flight']->add($reservations_flight);
            $responseResumido['reservations_flight']->add($reservations_flight);
        });

        $response['client'] = Client::where('id', '=', $reservation->client_id)->first();

        if ($resumido === true) {


            $responseResumido['total'] = $response['total'];
            return $responseResumido;

        } else {
            return $response;
        }

    }

    /**
     * @param Reservation $reservation
     * @return Collection
     */
    public function reservationSummarizedToApiResponse(Reservation $reservation)
    {
        $response = collect($reservation->only([
            'file_code',
        ]));

        return $response;
    }

    /**
     * @return Reservation
     */
    public function saveReservation()
    {
        DB::transaction(function () {
            $this->reservation->save();

            //Todo: Hotel
            $this->reservation->reservationsHotel()->saveMany($this->reservation->reservationsHotel);
            foreach ($this->reservation->reservationsHotel as $reservationsHotel) {
                $reservationsHotel->reservationsHotelRooms()->saveMany($reservationsHotel->reservationsHotelRooms);
                foreach ($reservationsHotel->reservationsHotelRooms as $reservationsHotelRoom) {
                    $reservationsHotelRoom->reservationHotelCancelPolicies()->saveMany($reservationsHotelRoom->reservationHotelCancelPolicies);

                    $reservationsHotelRoom->reservationsHotelsCalendarys()->saveMany($reservationsHotelRoom->reservationsHotelsCalendarys);
                    foreach ($reservationsHotelRoom->reservationsHotelsCalendarys as $reservationsHotelsCalendary) {
                        $reservationsHotelsCalendary->reservationHotelRoomDateRate()->saveMany($reservationsHotelsCalendary->reservationHotelRoomDateRate);
                    }

                    $reservationsHotelRoom->supplements()->saveMany($reservationsHotelRoom->supplements);
                }
            }

            //Todo: Servicio
            $this->reservation->reservationsService()->saveMany($this->reservation->reservationsService);

            foreach ($this->reservation->reservationsService as $reservationsService) {
                $reservationsService->reservationsServiceRatesPlans()->saveMany($reservationsService->reservationsServiceRatesPlans);
                foreach ($reservationsService->reservationsServiceRatesPlans as $reservationsServiceRatesPlans) {
                    $reservationsServiceRatesPlans->reservationServiceCancelPolicies()->saveMany($reservationsServiceRatesPlans->reservationServiceCancelPolicies);
                }
            }

            //Todo: Vuelos
            $this->reservation->reservationsFlight()->saveMany($this->reservation->reservationsFlight);

            //Todo: Pasajeros
            $this->reservation->reservationsPassenger()->saveMany($this->reservation->reservationsPassenger);

            //Todo: Entidad para paquetes
            $this->reservation->reservationsPackage()->saveMany($this->reservation->reservationsPackage);

        });
    }

    /**
     * @param array $filters
     * @return mixed
     */
    public function cancelReservationHotel(array $filters)
    {

        if (!isset($filters['channel_cancel_by_rooms_hyperguest'])) {
            $filters['channel_cancel_by_rooms_hyperguest'] = '';
        }

        $reservation = Reservation::getReservations($filters, true);
        $result = DB::transaction(function () use ($reservation, $filters) {
            $result = $this->cancelOnChannels($reservation, $filters['block_email_provider'],
                $filters['cancel_hotel_or_room']);
            return $result;
        });

        if ($result['success'] == false) {
            throw new Exception($result['error'][0]);
        }
        // Send cancellation Emails
        SendHotelReservationsEmails::dispatchNow($reservation, 'cancel', $filters['message_provider'],
            $filters['block_email_provider'], $filters['channel_cancel_by_rooms_hyperguest']);

        return ['reservation' => $reservation, 'result' => $result, $filters['message_provider']];
    }

    /**
     * @param array $filters
     * @return mixed
     */
    public function cancelReservationHotelRoom(array $filters)
    {
        $reservation = Reservation::getReservations([
            'file_code' => $filters['file_code'],
            'reservation_hotel_id' => $filters['reservation_hotel_id'],
        ], true);

        if (!is_array($filters['reservation_hotel_room_id'])) {
            $filters['reservation_hotel_room_id'] = [$filters['reservation_hotel_room_id']];
        }

        $res_id_resend = []; // que tengan channel_reservation_code
        $res_id_to_cancel = []; // ids para cancelar reservations_hotels_rates_plans_rooms
        // Obtener los id de loas reservas a cancelr y las reservas de los channels de queben se registradas nuevamente
        // esto por que las reservas de channels pueden tener mas de una habitacion y si se cancela solo una lo que se
        // debe hacer es cancelar ambas y hacer una nueva reserva con la o las habitaciones que quedan que tengan el mismo
        // codigo de channel_reservation_code de la habitacion se se cancelo/elimino
        foreach ($reservation->reservationsHotel as $reservationsHotel) {
            /** @var Collection $groupedByChannels */
            $groupedByChannels = $reservationsHotel->reservationsHotelRooms->groupBy('channel_code');
            foreach ($groupedByChannels as $channel_code => $resRooms) {
                if ($channel_code == 'AURORA') {
                    foreach ($resRooms as $resRoom) {
                        if (in_array($resRoom['id'], $filters['reservation_hotel_room_id'])) {
                            $res_id_to_cancel[] = $resRoom['id'];
                        }
                    }
                } else {
                    $groupByChannelResCode = $resRooms
                        ->where('channel_reservation_code', '!=', null)
                        ->where('channel_reservation_code', '!=', '')
                        ->whereIn('status_in_channel', ['1', '2'])
                        ->groupBy('channel_reservation_code');

                    foreach ($groupByChannelResCode as $channel_reservation_code => $resHotelRooms) {
                        $resToCancel = false;
                        foreach ($resHotelRooms as $resHotelRoom) {
                            if (in_array($resHotelRoom['id'], $filters['reservation_hotel_room_id'])) {
                                $resToCancel[] = true;
                                break;
                            }
                        }
                        if ($resToCancel) {
                            foreach ($resHotelRooms as $resHotelRoom) {
                                $res_id_to_cancel[] = $resHotelRoom['id'];
                                if (!in_array($resHotelRoom['id'], $filters['reservation_hotel_room_id'])) {
                                    $res_id_resend[] = $resHotelRoom['id'];
                                }
                            }
                        }
                    }
                }
            }
        }

//        return [ // file 307902 hotel URUHHV
//            "res_id_to_cancel" => $res_id_to_cancel, // [4206]
//            "res_id_resend" => $res_id_resend, // []
//        ];

        $reservation = Reservation::getReservations([
            'file_code' => $filters['file_code'],
            'reservation_hotel_id' => $filters['reservation_hotel_id'],
            'reservation_hotel_room_id' => $res_id_to_cancel,
        ], true);

//        return ["test"=> $reservation];

        $cancel_on_channels = $this->cancelOnChannels($reservation, $filters['block_email_provider'],
            $filters['cancel_hotel_or_room']);

        if ($cancel_on_channels['success']) {

            if (count($res_id_resend) > 0) {
                // confirmar que las reservas que se van a reenviar esten canceladas al 100%
                $reservation = Reservation::getReservations([
                    'file_code' => $filters['file_code'],
                    'reservation_hotel_id' => $filters['reservation_hotel_id'],
                    'reservation_hotel_room_id' => $res_id_resend,
                ], true);

                $res_channel_to_resend = collect();
                foreach ($reservation->reservationsHotel as $reservationsHotel) {
                    /** @var Collection $groupedByChannels */
                    $groupedByChannels = $reservationsHotel->reservationsHotelRooms->groupBy('channel_code');
                    foreach ($groupedByChannels as $channel_code => $resRooms) {
                        $groupByChannelResCode = $resRooms->groupBy('channel_reservation_code');
                        foreach ($groupByChannelResCode as $channel_reservation_code => $resHotelRooms) {
                            $allCancelled = false;
                            foreach ($resHotelRooms as $resHotelRoom) {
                                if ($resHotelRoom['status_in_channel'] == 0) {
                                    $allCancelled = true;
                                    break;
                                }
                            }
                            if ($allCancelled) {
                                foreach ($resHotelRooms as $resHotelRoom) {
                                    $resHotelRoom_new = $resHotelRoom->replicate();
                                    $resHotelRoom_new->status = 3;
                                    $resHotelRoom_new->status_in_channel = 3;
                                    $resHotelRoom_new->channel_reservation_code = null;

                                    $res_channel_to_resend[] = $resHotelRoom_new;
                                }
                            }
                        }
                    }
                }

                foreach ($res_channel_to_resend as $item) {
                    $item->save();
                }

                // confirmar que las reservas estan canceladas por completo
                $reservation = Reservation::getReservations([
                    'file_code' => $filters['file_code'],
                    'reservation_hotel_id' => $filters['reservation_hotel_id'],
                    'reservation_hotel_room_id' => $res_channel_to_resend->pluck('id'),
                ], true);

                // Save on Channels
                $this->saveOnChannels($reservation);
            };

            $reservation = Reservation::getReservations([
                'file_code' => $filters['file_code'],
                'reservation_hotel_id' => $filters['reservation_hotel_id'],
                'reservation_hotel_room_id' => $res_id_to_cancel
            ], true);

            $type_email = 'cancel_partial';
//            foreach ($reservation->reservationsHotel as $reservationsHotel) {
//                foreach ($reservationsHotel->reservationsHotelRooms as $reservationsHotelRoom) {
//                    if ($reservationsHotelRoom['channel_code'] == 'AURORA') {
//                        if (
//                            $reservationsHotelRoom['status'] == 1 or
//                            $reservationsHotelRoom['status'] == 2 or
//                            $reservationsHotelRoom['status'] == 3
//                        ) {
//                            $type_email = 'cancel_partial';
//                        }
//                    } else {
//                        if (
//                            $reservationsHotelRoom['status_in_channel'] == 1 or
//                            $reservationsHotelRoom['status_in_channel'] == 2
//                        ) {
//                            $type_email = 'cancel_partial';
//                        }
//                    }
//                }
//            }

            // Send cancellation Emails
            SendHotelReservationsEmails::dispatchNow($reservation, $type_email, $filters['message_provider'],
                $filters['block_email_provider']);
            // php artisan queue:work hotel_reservations_emails --queue=email

            return ["success" => true, "data" => $reservation];
        } else {
            return ["success" => false, "data" => $cancel_on_channels];
        }


    }

    /**
     * @param Reservation $reservation
     * @param bool $onlyExternals
     * @return array
     */
    public function saveOnChannels(Reservation $reservation)
    {
        $customer_name = $reservation['given_name'] . ' ' . $reservation['surname'];

        $errors = [];
        $resChannelProcessed = collect();
        // Guardar las reservas que van a los channels
        foreach ($reservation->reservationsHotel as $reservationsHotel) {
            /** @var Collection $groupedByChannels */
            $groupedByChannels = $reservationsHotel->reservationsHotelRooms->groupBy('channel_code');
            foreach ($groupedByChannels as $channel_code => $resRooms) {
                if ($channel_code == 'AURORA') {
                    continue;
                }

                /** @var Collection $resHotelRooms */
                $resHotelRooms = $resRooms->where('channel_reservation_code', '=', '')->whereIn('status_in_channel',
                    ['3']);
                if ($resHotelRooms->count() == 0) {
                    continue;
                }

                try {
                    $channelsRooms = [
                        'hotel_channel_code' => $resHotelRooms->first()->channel_hotel_code,
                        'customer_name' => $customer_name,
                        'created_at' => $reservationsHotel['created_at']->format('c'),
                        'reservation_code' => $reservationsHotel->id,
                        'check_in' => $reservationsHotel->check_in,
                        'check_out' => $reservationsHotel->check_out,
                        'rooms' => [],
                    ];

                    foreach ($resHotelRooms as $resHotelRoom) {
                        $channelsRooms['rooms'][$resHotelRoom->id] = [
                            'room_code' => $resHotelRoom->channel_room_code,
                            'rate_plan_code' => $resHotelRoom->rate_plan_code,
                            'adult_num' => $resHotelRoom->adult_num,
                            'child_num' => $resHotelRoom->child_num,
                            // 'extra_num' => $resHotelRoom->extra_num,
                            'total_amount_base' => $resHotelRoom->total_amount_base,
                            'guest_note' => $resHotelRoom->guest_note,
                            'rates' => $resHotelRoom->reservationsHotelsCalendarys->mapWithKeys(function ($date, $key) {
                                return [$date->date => $date];
                            })->toArray(),
                        ];
                    }

                    $model = null;
                    switch ($channel_code) {
                        case 'TRAVELCLICK':
                            $model = new \App\Http\Travelclick\TravelClick();
                            $channelResult = $model->OTA_HotelResNotifRQ($channelsRooms);
                            break;
                        case 'EREVMAX':
                            $model = new \App\Http\Erevmax\Erevmax();
                            $channelResult = $model->OTA_HotelResNotifRQ($channelsRooms);
                            break;
                        case 'SITEMINDER':
                            $model = new  SiteminderReservations($channelsRooms);
                            $channelResult = $model->commit();
                            break;
                        case 'HYPERGUEST':
                            $model = new  \App\Http\Hyperguest\Hyperguest();
                            $channelResult = $model->OTA_HotelResNotifRQ($channelsRooms);
                            break;
                        default:
                            throw new Exception('Channel ' . $channel_code . ' not implemented');
                    }

                    foreach ($resHotelRooms as $resHotelRoom) {
                        $resChannelProcessed->offsetSet($resHotelRoom->id, collect($channelResult));
                    }
                } catch (\Exception $e) {
                    foreach ($resHotelRooms as $resHotelRoom) {
                        $resChannelProcessed->offsetSet($resHotelRoom->id, collect([
                            'success' => false,
                            'error' => $e->getMessage(),
                        ]));
                    }
                }
            }
        }

        // verificar si las las reservas enviadas a los channels y colocar el estado 1 si fueron success
        foreach ($reservation->reservationsHotel as $reservationsHotel) {
            foreach ($reservationsHotel->reservationsHotelRooms as $resHotelRoom) {
                if ($resChannelProcessed->offsetExists($resHotelRoom->id)) {
                    if ($resChannelProcessed[$resHotelRoom->id]['success']) {
                        $resHotelRoom->status_in_channel = 1;// confirmada en el channel
                        $resHotelRoom->channel_reservation_code = $resChannelProcessed[$resHotelRoom->id]['channel_reservation_code'];
                        $resHotelRoom->observations = '';
                    } else {
                        $resHotelRoom->observations = $resChannelProcessed[$resHotelRoom->id]['error'];
                        if ($resChannelProcessed[$resHotelRoom->id]->offsetExists('channel_reservation_code')) {
                            $resHotelRoom->channel_reservation_code = $resChannelProcessed[$resHotelRoom->id]['channel_reservation_code'];
                        }
                    }
                }

                $resHotelRoom->save();
            }

            $reservationsHotel->process_aurora3 = 1;
            $reservationsHotel->save();
        }

        foreach ($reservation->reservationsFlight as $reservationsFlight) {
            $reservationsFlight->process_aurora3 = 1;
            $reservationsFlight->save();
        }

        foreach ($reservation->reservationsService as $reservationsService) {
            $reservationsService->process_aurora3 = 1;
            $reservationsService->save();
        }

        $result = [];

        try {
            // Guardar en stella
            $stella = new \App\Http\Stella\Reservation();
            $result = $stella->create($reservation);
            if (count($result['errors']) > 0) {
                foreach ($result['errors'] as $error) {
                    $errors[] = $error;
                }
                // verificar si las las reservas enviadas a los channels y colocar el estado 1 si fueron success
                foreach ($reservation->reservationsHotel as $reservationsHotel) {
                    foreach ($reservationsHotel->reservationsHotelRooms as $resHotelRoom) {
                        if ($resChannelProcessed->offsetExists($resHotelRoom->id)) {
                            if (!$resHotelRoom->observations) {
                                $resHotelRoom->observations = implode(' | ', $result['errors']);
                            } else {
                                $resHotelRoom->observations .= implode(' | ', $result['errors']);
                            }
                        }
                        $resHotelRoom->save();
                    }
                }
            }
        } catch (\Exception $e) {
            // verificar si las las reservas enviadas a los channels y colocar el estado 1 si fueron success
            foreach ($reservation->reservationsHotel as $reservationsHotel) {
                foreach ($reservationsHotel->reservationsHotelRooms as $resHotelRoom) {
                    if ($resChannelProcessed->offsetExists($resHotelRoom->id)) {
                        if (!$resHotelRoom->observations) {
                            $resHotelRoom->observations = $e->getMessage();
                        } else {
                            $resHotelRoom->observations .= $e->getMessage();
                        }
                    }
                    $resHotelRoom->save();
                }
            }

            $errors[] = $e->getMessage();
        }

        return [
            'result' => $result,
            'success' => count($errors) == 0 ? true : false,
            'reservation' => $reservation,
            'error' => $errors
        ];
    }

    /**
     * @param Reservation $reservation
     * @return array
     * @throws Exception
     */
    public function cancelOnChannels(Reservation $reservation, $block_email_provider, $cancel_hotel_or_room)
    {
        $customer_name = $reservation['given_name'] . ' ' . $reservation['surname'];
        $file_is_stella = is_numeric($reservation->file_code);
        $errors = [];
        $resChannelProcessed = collect();

        /**
         * Cancelar las reservas que van a los CHANNELS
         */
        foreach ($reservation->reservationsHotel as $reservationsHotel) {
            $groupedByChannels = $reservationsHotel->reservationsHotelRooms->groupBy('channel_code');
            $business_region_id = $reservationsHotel->business_region_id;

            foreach ($groupedByChannels as $channel_code => $resRooms) { // Valido solo para channels externos
                if ($channel_code == 'AURORA') {
                    continue;
                }

                $groupByChannelResCode = $resRooms
                    ->where('channel_reservation_code_master', '!=', null)
                    ->where('channel_reservation_code_master', '!=', '')
                    ->whereIn('status_in_channel', ['1', '2'])
                    ->groupBy('channel_reservation_code_master');

                foreach ($groupByChannelResCode as $channel_reservation_code => $resHotelRooms) {
                    try {
                        $channelsRooms = [
                            'hotel_channel_code' => $resHotelRooms->first()->channel_hotel_code,
                            'customer_name' => $customer_name,
                            'reservation_code' => $reservationsHotel->id,
                            'channel_reservation_code' => $channel_reservation_code,
                            'last_modify_date_time' => $reservationsHotel->created_at,
                            'check_in' => $reservationsHotel->check_in,
                            'check_out' => $reservationsHotel->check_out,
                            'rooms' => [],
                        ];
                        $procesarInChannel = false;
                        foreach ($resHotelRooms as $resHotelRoom) {
                            $channelsRooms['rooms'][$resHotelRoom->id] = [
                                'room_code' => $resHotelRoom->channel_room_code,
                                'rate_plan_code' => $resHotelRoom->rate_plan_code,
                                'adult_num' => $resHotelRoom->adult_num,
                                'child_num' => $resHotelRoom->child_num,
                                'total_amount_base' => $resHotelRoom->total_amount_base,
                                'guest_note' => $resHotelRoom->guest_note,
                                'rates' => $resHotelRoom->reservationsHotelsCalendarys->mapWithKeys(function (
                                    $date,
                                    $key
                                ) {
                                    return [$date->date => $date];
                                })->toArray(),
                            ];

                            if ($resHotelRoom->onRequest == '1') {
                                $procesarInChannel = true;
                            }
                        }

                        $model = null;
                        switch ($channel_code) {
                            case 'TRAVELCLICK':
                                $model = new \App\Http\Travelclick\TravelClick();
                                $channelResult = $model->OTA_CancelRQ($channelsRooms);
                                break;
                            case 'HYPERGUEST':
                                /* TODO: ALEX QUISPE */

                                $channelResult = ["success" => true, "onRequest" => 1];

                                if (is_null($business_region_id) || $business_region_id == 1) { // PERU
                                    if ($procesarInChannel) {
                                        $model = new \App\Http\Hyperguest\Hyperguest();
                                        $channelResult = $model->OTA_CancelRQ($channelsRooms);
                                    }
                                } else { // Otras regiones

                                    $cancelReservationHGService = new CancelReservationHyperguestGatewayService();
                                    $paramsCancel = [
                                        "file" => $reservation->file_code,
                                        "channelReservationId" => $channelsRooms["channel_reservation_code"],
                                    ];
                                    $responseChannel = $cancelReservationHGService->cancelReservationLegacy($paramsCancel);
                                    if (!$responseChannel['success']) {
                                        $channelResult = ["success" => false, "error" => $responseChannel['error']];
                                    }
                                }

                                /* TODO: ALEX QUISPE */
                                break;
                            case 'EREVMAX':
                                $model = new \App\Http\Erevmax\Erevmax();
                                $channelResult = $model->OTA_HotelResNotifRQ($channelsRooms, true);
                                break;
                            case 'SITEMINDER':
                                $model = new  SiteminderReservations($channelsRooms);
                                $channelResult = $model->cancel();
                                break;
                            default:
                                throw new Exception('Channel ' . $channel_code . ' not implemented');
                        }

                        foreach ($resHotelRooms as $resHotelRoom) {
                            $resChannelProcessed->offsetSet($resHotelRoom->id, collect($channelResult));
                        }

                    } catch (\Exception $e) {
                        foreach ($resHotelRooms as $resHotelRoom) {
                            $resChannelProcessed->offsetSet($resHotelRoom->id, collect([
                                'success' => false,
                                'error' => $e->getMessage(),
                            ]));
                        }
                    }
                }
            }
        }

        /**
         * verificar si las las reservas se cancelaron en los channels y colocar el estado 0 si fueron success
         */
        foreach ($reservation->reservationsHotel as $reservationsHotel) {
            $res_hotel_active = false;
            foreach ($reservationsHotel->reservationsHotelRooms as $resHotelRoom) {
                if ($resHotelRoom->channel_code != 'AURORA' and $resChannelProcessed->offsetExists($resHotelRoom->id)) {
                    if (isset($resChannelProcessed[$resHotelRoom->id]['success']) and $resChannelProcessed[$resHotelRoom->id]['success']) {
                        $resHotelRoom->status_in_channel = 0;// eliminada en el channel
                        if ($resHotelRoom->status != 1 and $resHotelRoom->status != 2) {
                            $resHotelRoom->status = 0;// eliminada
                            $resHotelRoom->cancel_block_email_provider = $block_email_provider;
                            $resHotelRoom->cancel_hotel_or_room = $cancel_hotel_or_room;
                            $resHotelRoom->cancel_user_id = Auth::user()->id;
                            $resHotelRoom->cancel_updated_at = date('Y-m-d H:i:s');
                        }
                    } else {
                        $resHotelRoom->observations = $resChannelProcessed[$resHotelRoom->id]['error'];
                    }
                } elseif ($resHotelRoom->channel_code == 'HYPERGUEST') {

                    if ($resHotelRoom->channel_reservation_code_master == "") {
                        $resHotelRoom->status_in_channel = 0;// eliminada
                        $resHotelRoom->status = 0;// eliminada
                        $resHotelRoom->cancel_block_email_provider = $block_email_provider;
                        $resHotelRoom->cancel_hotel_or_room = $cancel_hotel_or_room;
                        $resHotelRoom->cancel_user_id = Auth::user()->id;
                        $resHotelRoom->cancel_updated_at = date('Y-m-d H:i:s');
                    }

                } elseif ($resHotelRoom->channel_code == 'AURORA' and !$file_is_stella) {
                    $resHotelRoom->status_in_channel = 0;// eliminada
                    $resHotelRoom->status = 0;// eliminada
                    $resHotelRoom->cancel_block_email_provider = $block_email_provider;
                    $resHotelRoom->cancel_hotel_or_room = $cancel_hotel_or_room;
                    $resHotelRoom->cancel_user_id = Auth::user()->id;
                    $resHotelRoom->cancel_updated_at = date('Y-m-d H:i:s');
                }
                $resHotelRoom->save();

                if ($resHotelRoom->status_in_channel != 0 or $resHotelRoom->status != 0) {
                    $res_hotel_active = true;
                }
            }

            if (!$res_hotel_active) {
                $reservationsHotel->status = 0;
                $reservationsHotel->save();
            }
        }


        $data_ws = [];
        $procesa_ws = 0;

        // Cancelar en stella
        try {
            $stella = new \App\Http\Stella\Reservation();
            $result = $stella->cancel($reservation, '', $block_email_provider, $cancel_hotel_or_room);
            $data_ws = $result['data_ws'];
            $procesa_ws = 1;

            if (count($result['errors']) > 0) {
                if (stripos($result['errors'][0], "not Found") !== false) {
                    $result['errors'][0] = 'File ' . $reservation->file_code . ' not found';
                }
            }

            if (count($result['errors']) > 0) {
                foreach ($result['errors'] as $error) {
                    $errors[] = $error;
                }
                // verificar si las las reservas enviadas a los channels y colocar el estado 1 si fueron success
                foreach ($reservation->reservationsHotel as $reservationsHotel) {
                    foreach ($reservationsHotel->reservationsHotelRooms as $resHotelRoom) {
                        if ($resChannelProcessed->offsetExists($resHotelRoom->id)) {
                            if (!$resHotelRoom->observations) {
                                $resHotelRoom->observations = implode(' | ', $result['errors']);
                            } else {
                                $resHotelRoom->observations .= implode(' | ', $result['errors']);
                            }
                        }
                        $resHotelRoom->save();
                    }
                }
            }
        } catch (\Exception $e) {
            // verificar si las las reservas enviadas a los channels y colocar el estado 1 si fueron success
            foreach ($reservation->reservationsHotel as $reservationsHotel) {
                foreach ($reservationsHotel->reservationsHotelRooms as $resHotelRoom) {
                    if ($resChannelProcessed->offsetExists($resHotelRoom->id)) {
                        if (!$resHotelRoom->observations) {
                            $resHotelRoom->observations = $e->getMessage();
                        } else {
                            $resHotelRoom->observations .= $e->getMessage();
                        }
                    }
                    $resHotelRoom->save();
                }
            }
        }

        return [
            'success' => count($errors) == 0,
            'error' => $errors,
            'data_ws' => $data_ws,
            'procesa_ws' => $procesa_ws
        ];
    }

    /**
     * @param $token_search
     * @param $serviceIdent
     * @param $service_id
     * @param $date
     * @param null $quantity_adults
     * @param null $quantity_child
     * @param array $ages_child
     * @return array
     * @throws Exception
     */
    public function setServiceRateData(
        $token_search,
        $service_id,
        $date,
        $quantity_adults = null,
        $quantity_child = null,
        $ages_child = []
    )
    {
        $serviceSearch = $this->getTokenSearchData($token_search);
        $selectedService = null;

        $selectedService = collect($serviceSearch)->first(function ($service) use ($service_id, $date) {
            return $service['id'] == $service_id and $service['date_reserve'] == $date;
        });

        if (!$selectedService) {
            throw new \Exception(trans('validations.reservations.token_search_service_not_found',
                ['service' => $service_id]));
        }

        if (!$quantity_adults) {
            throw new Exception(trans('validations.reservations.quantity_adults_required'));
        }
        return $selectedService;
    }

    public function setServiceDataAdditional($supplement_optionals)
    {
        $supplementsData = [];
        foreach ($supplement_optionals as $optional) {
            $serviceSearch = $this->getTokenSearchData($optional['token_search']);
            foreach ($serviceSearch as $search) {
                $supplementsData[] = $search;
            }

        }

        return $supplementsData;

    }

    /**
     * @param array $filters
     * @return mixed
     */
//    public function addNumberConfirmationRoomHotel(array $filters)
//    {
//
//        $reservation = Reservation::getReservations([
//            'file_code' => $filters['file_code'],
//            'reservation_hotel_id' => $filters['reservation_hotel_id'],
//        ], true);
//
//        $codrooms_implode = implode(",", $filters['room_codes']);
//        $response = false;
//
//        $aurora1 = new \App\Http\Stella\Reservation();
//        $params = [
//            'nroref' => $filters['file_code'],
//            'codhab' => $codrooms_implode,
//            'nroconfirm' => $filters['nro_confirmation'],
//        ];
//        $result = $aurora1->updateNumberConfirmationFile($params);
//        // if ($result->success === 1) {
//        $this->updateNumberConfirmation($reservation, $filters['nro_confirmation']);
//        $response = true;
//        // }
//
//        return $reservation;
//    }

    public function addNumberConfirmationRoomHotel(array $filters)
    {
        $reservation = Reservation::getReservations([
            'file_code' => $filters['file_code'],
            'reservation_hotel_id' => $filters['reservation_hotel_id'],
        ], true);

        $codrooms_implode = implode(",", $filters['room_codes']);
        $response = false;

        $file_code = $filters['file_code'];
        $baseUrl = config('services.files_onedb.domain');

        // Construimos la ruta dinámica: files/{file_number}/sync/room-confirmation
        $endpoint = $baseUrl . "files/{$file_code}/sync/room-confirmation";

        try {
            $client = new \GuzzleHttp\Client(['verify'=>false]);
            $apiResponse = $client->post($endpoint, [
                'json' => [
                    'codhab' => $codrooms_implode,
                    'nroconfirm' => $filters['nro_confirmation']
                ],
                'timeout' => 60
            ]);

            $result = json_decode($apiResponse->getBody()->getContents(), true);
            $this->updateNumberConfirmation($reservation, $filters['nro_confirmation']);
            $response = true;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error al conectar con Express (syncAddNroConfirmationByRoom): " . $e->getMessage());
        }

        return $response;
    }

    public function updateNumberConfirmation(Reservation $reservation, $num_confirmation)
    {
        foreach ($reservation->reservationsHotel as $reservationsHotel) {
            foreach ($reservationsHotel->reservationsHotelRooms as $resRooms) {

                if ($resRooms->channel_id == ChannelHyperguestConfig::CHANNEL_ID) {

                    if ($resRooms->onRequest == "1" and $resRooms->channel_reservation_code) {
                        // no actualizamos porque este codigo ya vino del channel de hyperguest
                    } else {
                        $resRooms->channel_reservation_code = $num_confirmation;
                        $resRooms->onRequest = 1;
                        $resRooms->bocking_updated_at = date('Y-m-d H:i:s');
                        $resRooms->save();
                    }
                } else {
                    $resRooms->channel_reservation_code = $num_confirmation;
                    $resRooms->onRequest = 1;
                    $resRooms->bocking_updated_at = date('Y-m-d H:i:s');
                    $resRooms->save();
                }

            }
        }
    }

    /**
     * @param array $filters
     * @return mixed
     * @throws Exception
     */
    public function cancelReservationService(array $filters)
    {
        $reservation = Reservation::getReservations($filters, true);

        $result = DB::transaction(function () use ($reservation) {
            $result = $this->cancelService($reservation);
            return $result;
        });

        if (!$result['success']) {
            throw new Exception($result['error'][0]);
        }

        // Send cancellation Emails
        // SendHotelReservationsEmails::dispatch($reservation, 'cancel');
        SendHotelReservationsEmails::dispatchNow($reservation, 'cancel'); // usar para ejecutar de inmediato (test)

        return $reservation;
    }

    /**
     * @param Reservation $reservation
     * @return array
     * @throws Exception
     */
    public function cancelService(Reservation $reservation): array
    {
        $customer_name = $reservation['given_name'] . ' ' . $reservation['surname'];

        $file_is_stella = is_numeric($reservation->file_code);

        $errors = [];
        $resChannelProcessed = collect();

        /**
         * verificar si las las reservas se cancelaron en los channels y colocar el estado 0 si fueron success
         */
        foreach ($reservation->reservationsService as $reservationsService) {
            $res_hotel_active = false;
            foreach ($reservationsService->reservationsHotelRooms as $resHotelRoom) {
                if ($resHotelRoom->channel_code != 'AURORA' and $resChannelProcessed->offsetExists($resHotelRoom->id)) {
                    if ($resChannelProcessed[$resHotelRoom->id]['success']) {
                        $resHotelRoom->status_in_channel = 0;// eliminada en el channel
                        if ($resHotelRoom->status != 1 and $resHotelRoom->status != 2) {
                            $resHotelRoom->status = 0;// eliminada
                        }
                    } else {
                        $resHotelRoom->observations = $resChannelProcessed[$resHotelRoom->id]['error'];
                    }
                } elseif ($resHotelRoom->channel_code == 'AURORA' and !$file_is_stella) {
                    $resHotelRoom->status_in_channel = 0;// eliminada
                    $resHotelRoom->status = 0;// eliminada
                }

                $resHotelRoom->save();

                if ($resHotelRoom->status_in_channel != 0 or $resHotelRoom->status != 0) {
                    $res_hotel_active = true;
                }
            }

            // Comentado porque esto al aparecer no se usa
            // if (!$res_hotel_active) {
            //     $reservationsHotel->status = 0;
            //     $reservationsHotel->save();
            // }
        }

        // Cancelar en stella
        try {
            $stella = new \App\Http\Stella\Reservation();
            $result = $stella->cancel($reservation);
            if (count($result['errors']) > 0) {

                if ($result['errors'][0]) {

                    if (stripos($result['errors'][0], "no existe numero de linea de la trama") !== false) {
                        $result = $stella->cancel($reservation, '123456');
                    }
                }
            }

            if (count($result['errors']) > 0) {
                foreach ($result['errors'] as $error) {
                    $errors[] = $error;
                }
                // verificar si las las reservas enviadas a los channels y colocar el estado 1 si fueron success
                foreach ($reservation->reservationsHotel as $reservationsHotel) {
                    foreach ($reservationsHotel->reservationsHotelRooms as $resHotelRoom) {
                        if ($resChannelProcessed->offsetExists($resHotelRoom->id)) {
                            if (!$resHotelRoom->observations) {
                                $resHotelRoom->observations = implode(' | ', $result['errors']);
                            } else {
                                $resHotelRoom->observations .= implode(' | ', $result['errors']);
                            }
                        }
                        $resHotelRoom->save();
                    }
                }
            }
        } catch (\Exception $e) {
            // verificar si las las reservas enviadas a los channels y colocar el estado 1 si fueron success
            foreach ($reservation->reservationsHotel as $reservationsHotel) {
                foreach ($reservationsHotel->reservationsHotelRooms as $resHotelRoom) {
                    if ($resChannelProcessed->offsetExists($resHotelRoom->id)) {
                        if (!$resHotelRoom->observations) {
                            $resHotelRoom->observations = $e->getMessage();
                        } else {
                            $resHotelRoom->observations .= $e->getMessage();
                        }
                    }
                    $resHotelRoom->save();
                }
            }
        }


        return [
            'success' => count($errors) == 0,
            'error' => $errors
        ];
    }

    public function createBilling($reservationBilling)
    {
        if ($reservationBilling->count() > 0) {
            $checkClient = ReservationBilling::where('document_type_id', $reservationBilling[0]['document_type_id'])
                ->where('document_number', $reservationBilling[0]['document_number'])
                ->first();
            if ($checkClient) {
                $billing = ReservationBilling::find($checkClient->id);
                $billing->name = $reservationBilling[0]['name'];
                $billing->surnames = $reservationBilling[0]['surnames'];
                $billing->phone = $reservationBilling[0]['phone'];
                $billing->email = $reservationBilling[0]['email'];
                $billing->document_type_id = $reservationBilling[0]['document_type_id'];
                $billing->document_number = $reservationBilling[0]['document_number'];
                $billing->address = $reservationBilling[0]['address'];
                $billing->country_id = $reservationBilling[0]['country_id'];
                $billing->state_id = $reservationBilling[0]['state_id'];
                $billing->state_ifx_iso = $reservationBilling[0]['state_ifx_iso'];
                $billing->save();
            } else {
                $billing = new ReservationBilling();
                $billing->name = $reservationBilling[0]['name'];
                $billing->surnames = $reservationBilling[0]['surnames'];
                $billing->phone = $reservationBilling[0]['phone'];
                $billing->email = $reservationBilling[0]['email'];
                $billing->document_type_id = $reservationBilling[0]['document_type_id'];
                $billing->document_number = $reservationBilling[0]['document_number'];
                $billing->address = $reservationBilling[0]['address'];
                $billing->country_id = $reservationBilling[0]['country_id'];
                $billing->state_id = $reservationBilling[0]['state_id'];
                $billing->state_ifx_iso = $reservationBilling[0]['state_ifx_iso'];
                $billing->save();
            }
            return $billing->id;
        } else {
            return null;
        }

    }

    private function reservationPushExternalClient(Request $request)
    {

        if (!is_array($request->get('guests')) || count($request->get('guests')) == 0) {
            throw new \Exception('At least 1 guest is requiered');
        }

        $guest = current($request->get('guests'));

        if (empty($guest['given_name']) or empty($guest['surname'])) {
            throw new \Exception('Mandatory given_name and surname cannot be null or empty');
        }


        $file_code = $request->get('file_code');// TODO: refactorizar a file_code
        $payment_code = $request->get('payment_code');
        $given_name = $guest['given_name'];
        $surname = $guest['surname'];
        $reference = $request->get('reference');
        $date_init = $request->get('date_init');
        $reference_new = $request->get('reference_new');
        $this->selection['reference'] = empty($reference) ? '' : $reference;
        $this->selection['given_name'] = $given_name;
        $this->selection['surname'] = $surname;
        $this->selection['file_code'] = empty($file_code) ? '' : $file_code;
        $this->selection['payment_code'] = empty($payment_code) ? null : $payment_code;
        $this->selection['date_init'] = empty($date_init) ? null : $date_init;
        $this->selection['reference_new'] = empty($reference_new) ? null : $reference_new;
        $this->selectionConstructor($request);
        $this->saveReservation();
        $this->reservation = Reservation::getReservations(
            [
                'file_code' => $this->reservation->file_code,
            ],
            true
        );
        return [
            'reservation_id' => $this->reservation->id,
            'file_code' => $this->reservation->file_code
        ];
    }

    private function getDateInitServices($file_code, $reservationDataService, $reservationDataHotel, $update = false)
    {
        //Todo Variable donde tendra las fechas minimas de la reserva y de los servicios nuevos agregados a un file
        $dates_min = collect();

        //Todo Si la reserva es una actualizacion (agregan nuevos servicios al file)
        if ($update) {
            //Todo Obtengo las fechas de todos los servicios y hoteles y obtengo la fechas minimas
            $reservation = Reservation::where('file_code', $file_code)
                ->with([
                    'reservationsHotel' => function ($query) {
                        $query->select(['reservation_id', 'check_in']);
                        $query->where('status', '!=', 0);
                    }
                ])->with([
                    'reservationsService' => function ($query) {
                        $query->select(['reservation_id', 'date']);
                    }
                ])->first(['id', 'file_code', 'date_init']);

            if ($reservation) {
                $dates_min->add($reservation['date_init']);
                if ($reservation->reservationsHotel->count() > 0) {
                    foreach ($reservation->reservationsHotel as $key => $reservationData) {
                        $dates_min->add($reservationData['check_in']);
                    }
                }

                if ($reservation->reservationsService->count() > 0) {
                    foreach ($reservation->reservationsService as $key => $reservationData) {
                        $dates_min->add($reservationData['date']);
                    }
                }
            }
        } else {
            foreach ($reservationDataHotel as $key => $reservationData) {
                $dates_min->add($reservationData['check_in']);
            }
            foreach ($reservationDataService as $key => $reservationData) {
                $dates_min->add($reservationData['date']);
            }
        }


        return $dates_min->min();
    }

    public function getNumberFile()
    {
        if (!isset($this->selection['no_generate_nro_file'])) {
            $getData = new StellaService();
            $getData = $getData->generateFileNumber();
            return $getData;
        } else {
            $stela = new \stdClass();
            $stela->success = true;
            $stela->nroref = createFileCode($this->selection['selected_client_country_iso']);
            return $stela;
        }
    }

    public function blockServicesQuote($quote_id)
    {
        try {
            //Todo Bloquemos servicios y hoteles de la cotizacion original
            $quote_categories = DB::table('quote_categories')
                ->where('quote_id', $quote_id)
                ->get(['id', 'quote_id']);
            foreach ($quote_categories as $quote_category) {
                DB::table('quote_services')
                    ->where('quote_category_id', $quote_category->id)
                    ->update(['locked' => 1]);
            }
            //Todo Bloquemos servicios y hoteles de la cotizacion borrador
            $quote_draft = DB::table('quotes')
                ->where('user_id', Auth::user()->id)
                ->where('status', 2)
                ->first();
            $quote_categories = DB::table('quote_categories')
                ->where('quote_id', $quote_draft->id)
                ->get(['id', 'quote_id']);
            foreach ($quote_categories as $quote_category) {
                DB::table('quote_services')
                    ->where('quote_category_id', $quote_category->id)
                    ->update(['locked' => 1]);
            }

        } catch (\Exception $e) {
        }
    }

    public function validateBillingData(Request $request)
    {
        $count_error = 0;
        $billing_data = $request->has('billing') ? $request->input('billing') : [];
        if (!empty($billing_data)) {

            if (!isset($billing_data['document_type_id'])) {
                $count_error++;
            } elseif (empty($billing_data['document_type_id'])) {
                $count_error++;
            }

            if (!isset($billing_data['document_number'])) {
                $count_error++;
            } elseif (empty($billing_data['document_number'])) {
                $count_error++;
            }

            if (!isset($billing_data['name'])) {
                $count_error++;
            } elseif (empty($billing_data['name'])) {
                $count_error++;
            }

            if (!isset($billing_data['surnames'])) {
                $count_error++;
            } elseif (empty($billing_data['surnames'])) {
                $count_error++;
            }
            if (!isset($billing_data['country_id'])) {
                $count_error++;
            } elseif (empty($billing_data['country_id'])) {
                $count_error++;
            }

            if (!isset($billing_data['state_iso'])) {
                $count_error++;
            } elseif (empty($billing_data['state_iso'])) {
                $count_error++;
            }

            if (!isset($billing_data['address'])) {
                $count_error++;
            } elseif (empty($billing_data['address'])) {
                $count_error++;
            }

        }

        if ($count_error > 0) {
            throw new \Exception(trans('validations.reservations.billing_information_required'), 1001);
        }
    }

    private function putLogAurora(
        $request,
        $response,
        $requestHeaders,
        $responseHeaders,
        $methodName,
        $echoToken = null,
        $success = true,
        $type_doc = 'xml'
    )
    {
        try {
            $tokenLog = md5(uniqid(rand(), true));

            $logDir = strtolower('AURORA') . '/' . $methodName . '/' . date('Y-m-d') . '/' . $tokenLog . '/';

            $log = new ChannelsLogs();
            if (is_integer($echoToken) and ($methodName == 'CreandoFile' or $methodName == 'CreandoCliente' or $methodName == 'CancelaFile' or $methodName == 'RequestCreateFile')) {
                $log->reservation_id = $echoToken;
            }
            $log->request_ip = '127.0.0.1';
            $log->request_headers = !$requestHeaders ? '{}' : $requestHeaders;
            $log->response_headers = !$responseHeaders ? '{}' : $responseHeaders;
            $log->log_directory = $logDir;
            $log->method_name = $methodName;
            $log->echo_token = $echoToken ?? $tokenLog;
            $log->token = $tokenLog;
            $log->date = date('c');
            $log->status = $success;
            $log->channel_id = 1;
            $log->type_data = $type_doc;
            $log->log_request = $request;
            $log->log_response = $response;
            $log->created_at = date('Y-m-d H:i:s');
            $log->save();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function validateQuote(Request $request)
    {

        if ($request->get('entity') === 'Quote' and $request->get('file_code') === null) {
            $reservation_quote = Reservation::where('entity', 'Quote')
                ->where('object_id', $request->get('object_id'))
                ->first(['id', 'entity', 'object_id', 'file_code']);

            if ($reservation_quote) {
                throw new \Exception(trans('validations.reservations.quote_number_already_has_a_file_assigned', [
                    'quote_id' => $request->get('object_id'),
                    'file_code' => $reservation_quote->file_code,
                ]), 1006);

            }
        }
    }

    public function validationMinimumHotelNights($hotel, $rates_plan_rooms, $rate_plan_room_id)
    {
        //Todo Variable para agregar las noches minimas
        $collection_minimum_nights = collect();
        //Todo recorremos noche por noche y buscar el minimo de noches mayor
        foreach ($rates_plan_rooms as $rates_plan_room) {
            if ($rates_plan_room['id'] === $rate_plan_room_id) {
                $calendaries_select = collect();
                foreach ($rates_plan_room['calendarys'] as $calendary) {
                    $min_nights = $calendary['policies_rates']['min_length_stay'];
                    $collection_minimum_nights->add($min_nights);
                    $calendaries_select->add($calendary);
                }
            }
        }


        //Todo Obtenemos el minimo de noches mayor
        $min_nights_reservation = $collection_minimum_nights->max();
        //Todo si la noches que se esta reservando es menor a la cantidad minima
        if ((integer)$hotel['nights'] < $min_nights_reservation) {
            $_hotel = $hotel['hotel']['name'];
            foreach ($hotel['hotel']['channels'] as $channel) {
                if ($channel['id'] === 1) {
                    $_hotel .= ' [' . $channel['pivot']['code'] . ']';
                }
            }

            throw new \Exception(trans('validations.reservations.hotel_minimum_number_nights_required',
                ['hotel_name' => $_hotel, 'nights' => $min_nights_reservation]));
        }
    }

    public function getReferenceFile($reference, $passengers)
    {
        if (empty($reference) and $passengers->count() > 0) {
            $passenger = $passengers->first();
            $reference_file = $passenger['surnames'] . ' ' . $passenger['name'] . ' - X' . $passengers->count();
        } else {
            $reference_file = $reference;
        }
        return $reference_file;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    private function validationHyperguestHotels(Request $request, $reference, $reservation)
    {
        $this->setChannel('HYPERGUEST');

        $this->validateReservationAuth($request);

        $this->selectionInit(
            session()->get('user_type'),
            session()->get('selected_executive')['id'],
            session()->get('selected_executive')['name'],
            session()->get('selected_executive')['code'],
            session()->get('selected_client')['id'],
            session()->get('selected_client')["code"],
            session()->get('selected_client_country')['iso'],
            session()->get('selected_client_user_id'),
            session()->get('selected_client')['ecommerce']
        );

        //Todo: Guardando pasajeros
        $reservationPassengers = $this->getPassengersData($request->input('guests'));
        $reservationPassengers = $reservationPassengers->toArray();

        $firstGuestData = $this->setHyperguestRoomGuest(collect($reservationPassengers[0]));

        //Todo: Guardando hoteles
        $reservationData = $this->getHyperguestHotelsReservationData($request->input('reservations'));

        $createReservationHGService = new CreateReservationHyperguestGatewayService();
        $reservationDataHyperguestPull = $createReservationHGService->getHyperguestHotelsReservationData($request->input('reservations'));


        if (empty($reservationData) and empty($reservationDataHyperguestPull)) {
            return [
                "success" => false,
                "hotels_reservation" => []
            ];
        } else {

            $file_code = $reservation->file_code;
            $executive_id = $reservation->executive_id;
            $executive = User::select('name', 'email')->find($executive_id);

            $hotels_reservation = [];
            foreach ($reservationData as $reservationDatum) {
                foreach ($reservationDatum['hotels'] as $reservationHotelData) {


                    try {
                        $reservations = $this->reservationHyperguestPush($file_code, $firstGuestData, $executive, $reservationDatum, $reservationHotelData, $reservationPassengers);
                        array_push($hotels_reservation, $reservations);
                    } catch (\Throwable $e) {
                        $reservations['success'] = false;
                        $reservations['error'] = $e->getMessage();
                        array_push($hotels_reservation, $reservations);

                    }
                }
            }

            foreach ($reservationDataHyperguestPull as $reservationDatum) {
                foreach ($reservationDatum['hotels'] as $reservationHotelData) {


                    try {
                        $reservations = $this->reservationHyperguestPull($file_code, $firstGuestData, $executive, $reservationDatum, $reservationHotelData, $reservationPassengers);
                        array_push($hotels_reservation, $reservations);
                    } catch (\Throwable $e) {
                        $reservations['success'] = false;
                        $reservations['error'] = $e->getMessage();
                        array_push($hotels_reservation, $reservations);

                    }
                }
            }

            return [
                "success" => true,
                "hotels_reservation" => $hotels_reservation
            ];
        }


    }

    private function reservationHyperguestPull($file_code, $firstGuestData, $executive, $reservationDatum, $reservationHotelData, $reservationPassengers): array
    {
        $data = $reservationHotelData;

        $error_msg = "";
        $channel_reservation_code = "";
        $success = true;
        $error = "";
        $hotel = [
            "dates" => [
                "from" => $reservationDatum["check_in"],
                "to" => $reservationDatum["check_out"]
            ],
            "hotelId" => $reservationHotelData["hotel"]["hotel_code"],
            "leadGuest" => $firstGuestData,
            "reference" => [
                "agency" => $file_code
            ],
            "rooms" => []
        ];

        $reservations = [
            "file" => $file_code,
            "hotel_id" => $reservationHotelData["hotel"]["id"],
            "hotel_name" => $reservationHotelData["hotel"]["name"],
            "booking_id" => $channel_reservation_code,
            "date_from" => $reservationDatum["check_in"],
            "date_to" => $reservationDatum["check_out"],
            "success" => true,
            "error" => $error
        ];

        $newReference = empty($reference) ? $firstGuestData['name']["first"] . " " . $firstGuestData['name']["last"] : $reference;
        $specialRequests = [
            "File: $file_code",
            "Cliente: $newReference",
            "Usuario nombre: $executive->name",
            "Usuario correo: $executive->email",
        ];

        foreach ($reservationHotelData['rates_plans_rooms'] as $rates_plans_rooms) {
            foreach ($rates_plans_rooms as $rates_plans_room) {
                $rates_plans_room['total_amount_adult_base'] = $rates_plans_room['total_amount_base'];
                $hotel["hotelId"] = $rates_plans_room["channel_hotel_code"]; // channel_hotel_code
                $hotel["rooms"][] = $this->setHyperguestRoom($rates_plans_room, $reservationPassengers, $specialRequests);
            }
        }

        try {
            $integration = IntegrationHyperguest::first();

            if (!$integration) {
                throw new Exception("No se encontró la integración de Hyperguest.");
            }

            $isProduction = app()->environment('production');
            if ($isProduction) {
                $data_emails = $integration->email_contact . ',' . $integration->email; // Si estamos en producción los emails de la integración son los de contacto
            } else {
                $data_emails = $integration->email; // Sino los de la integración son los de QA, DEV, etc
            }

            $emails = explode(",", $data_emails);

            $createReservationHGService = new CreateReservationHyperguestGatewayService();

            $response = $createReservationHGService->createReservationLegacy([
                'hotel' => $hotel
            ]);

            $reservation = $response['reservation'] ?? null;
            $reservationId = $response['reservationId'] ?? null;
            $data = $reservation ?? [];

            if (isset($reservationId)) {
                $channel_reservation_code = $reservationId;
            }

            $this->putXmlLogHyperguest(
                json_encode($hotel),
                json_encode($data),
                $this->getChannel(),
                'OTA_HotelResNotifRQ',
                $channel_reservation_code,
                $success
            );

            $reservations['success'] = $success;
            $reservations['booking_id'] = $channel_reservation_code;
            $reservations['error'] = $error ?? "";

            if (!$success) {
                SendNotificationHyperguest::dispatchNow(
                    $emails,
                    $reservations,
                    json_encode($hotel),
                    json_encode($data)
                );
            }

        } catch (\Throwable $e) {
            $reservations['success'] = false;
            $reservations['error'] = $e->getMessage();
        }

        return $reservations;
    }

    private function reservationHyperguestPush($file_code, $firstGuestData, $executive, $reservationDatum, $reservationHotelData, $reservationPassengers): array
    {
        $error_msg = "";
        $channel_reservation_code = '';
        $success = true;
        $error = "";
        $hotel = [
            "dates" => [
                "from" => $reservationDatum["check_in"],
                "to" => $reservationDatum["check_out"]
            ],
            "hotelId" => $reservationHotelData["hotel"]["hotel_code"],
            "leadGuest" => $firstGuestData,
            "reference" => [
                "agency" => $file_code
            ],
            "rooms" => []
        ];

        $reservations = [
            "file" => $file_code,
            "hotel_id" => $reservationHotelData["hotel"]["id"],
            "hotel_name" => $reservationHotelData["hotel"]["name"],
            "booking_id" => $channel_reservation_code,
            "date_from" => $reservationDatum["check_in"],
            "date_to" => $reservationDatum["check_out"],
            "success" => $success,
            "error" => $error
        ];

        $newReference = empty($reference) ? $firstGuestData['name']["first"] . " " . $firstGuestData['name']["last"] : $reference;
        $specialRequests = [
            "File: $file_code",
            "Cliente: $newReference",
            "Usuario nombre: $executive->name",
            "Usuario correo: $executive->email",
        ];


        foreach ($reservationHotelData['rates_plans_rooms'] as $rates_plans_rooms) {
            foreach ($rates_plans_rooms as $rates_plans_room) {
                $hotel["hotelId"] = $rates_plans_room["channel_hotel_code"];
                $hotel["rooms"][] = $this->setHyperguestRoom($rates_plans_room, $reservationPassengers, $specialRequests);
            }
        }

        try {

            $integration = IntegrationHyperguest::first();

            $emails = app()->environment('production')
                ? $integration->email_contact . ',' . $integration->email // Si estamos en producción los emails de la integración son los de contacto
                : $integration->email; // Sino los de la integración son los de QA, DEV, etc

            $emails = explode(",", $emails);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, config('services.hyperguest.domain'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($hotel));
            $headers = array(
                "Content-Type: application/json",
                "Authorization: Bearer {$integration->token}",
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $data = curl_exec($ch);

            if (curl_errno($ch)) {
                $success = false;
                $error_msg = curl_error($ch);
            }
            curl_close($ch);

            $data = json_decode($data, true);

            if (isset($data['error'])) {
                $success = false;
                $error = json_encode($data["error"]) . ' ' . json_encode($data["errorDetails"]) . ' ' . $error_msg;
            }

            if (isset($data["content"]["bookingId"])) {
                $channel_reservation_code = $data["content"]["bookingId"];
            }

            $this->putXmlLogHyperguest(json_encode($hotel), json_encode($data), $this->getChannel(),
                'OTA_HotelResNotifRQ', $channel_reservation_code, $success);

            $reservations['success'] = $success;
            $reservations['booking_id'] = $channel_reservation_code;
            $reservations['error'] = $error;

            if ($success == false) {
                //    Mail::to($emails)->send(new \App\Mail\NotificationHyperguestError($reservations,json_encode($hotel), json_encode($data)));
                SendNotificationHyperguest::dispatchNow($emails, $reservations, json_encode($hotel),
                    json_encode($data));
            }

        } catch (\Throwable $e) {
            $reservations['success'] = false;
            $reservations['error'] = $e->getMessage();
        }

        return $reservations;
    }


    /**
     * @throws Exception
     */
    private function setHyperguestRoom(array $rates_plans_room, array &$reservationPassengers, array $specialRequests): array
    {
        $guests = [];
        $quantity_adults = $rates_plans_room["adult_num"];
        $quantity_child = $rates_plans_room["child_num"];

        for ($i = 0; $i < $rates_plans_room["adult_num"]; $i++) {
            $adultAdded = false;
            foreach ($reservationPassengers as $index_passenger => $reservationPassenger) {
                if ($reservationPassenger["type"] == "ADL" && !$reservationPassenger["used"] && $quantity_adults > 0) {
                    $guests[] = $this->setHyperguestRoomGuest(collect($reservationPassenger));
                    $reservationPassengers[$index_passenger]["used"] = true;
                    $quantity_adults--;
                    $adultAdded = true;
                }
            }

            if (!$adultAdded) {
                foreach ($reservationPassengers as $index_passenger => $reservationPassenger) {
                    if ($reservationPassenger["type"] == "ADL" && $reservationPassenger["used"] && $quantity_adults > 0) {
                        $guests[] = $this->setHyperguestRoomGuest(collect($reservationPassenger));
                        $reservationPassengers[$index_passenger]["used"] = true;
                        $quantity_adults--;
                        break;
                    }
                }
            }
        }

        for ($j = 0; $j < $rates_plans_room["child_num"]; $j++) {
            foreach ($reservationPassengers as $index_passenger => $reservationPassenger) {
                if ($reservationPassenger["type"] == "CHD" && !$reservationPassenger["used"] && $quantity_child > 0) {
                    $guestsData = collect($reservationPassenger);

                    $guests[] = $this->setHyperguestRoomGuest($guestsData);
                    $reservationPassengers[$index_passenger]["used"] = true;
                    $quantity_child--;
                }
            }
        }

        if (count($guests) == 0) {
            for ($i = 0; $i < $rates_plans_room["adult_num"]; $i++) {
                foreach ($reservationPassengers as $index_passenger => $reservationPassenger) {
                    if ($reservationPassenger["type"] == "ADL" && $reservationPassenger["used"] && $quantity_adults > 0) {
                        $guests[] = $this->setHyperguestRoomGuest(collect($reservationPassenger));
                        $reservationPassengers[$index_passenger]["used"] = true;
                        $quantity_adults--;
                    }
                }
            }

            for ($j = 0; $j < $rates_plans_room["child_num"]; $j++) {
                foreach ($reservationPassengers as $index_passenger => $reservationPassenger) {
                    if ($reservationPassenger["type"] == "CHD" && $reservationPassenger["used"] && $quantity_child > 0) {
                        $guestsData = collect($reservationPassenger);

                        $guests[] = $this->setHyperguestRoomGuest($guestsData);
                        $reservationPassengers[$index_passenger]["used"] = true;
                        $quantity_child--;
                    }
                }
            }
        }

        $expectedPrice = $rates_plans_room["has_channel_child_rate"] ? $rates_plans_room["total_amount_base"] : $rates_plans_room["total_amount_adult_base"];
        return [
            "roomCode" => $rates_plans_room["channel_room_code"],
            "rateCode" => $rates_plans_room["rate_plan_code"],
            "expectedPrice" => [
                "amount" => (string)$expectedPrice,
                "currency" => "USD"
            ],
            "guests" => $guests,
            "specialRequests" => $specialRequests
        ];

    }

    /**
     * @throws Exception
     */
    private function setHyperguestRoomGuest(Collection $guestsData): array
    {
        if ($guestsData["type"] == "ADL") {
            $birthdate = !$guestsData->get('date_birth') ? '1992-01-01' : $guestsData->get('date_birth');
        } else {
            if ($guestsData["type"] == "CHD") {
                if (!$guestsData->get('date_birth')) {
                    $birthdate = date('Y-m-d', strtotime('-2 year', strtotime(date("Y-m-d"))));
                } else {
                    $birthdate = $guestsData->get('date_birth');
                }
            } else {
                throw new Exception('Hyperguest: guest type: "' . $guestsData["type"] . '" not valid');
            }
        }

        $email = !$guestsData->get('email') ? 'not_data@email.com' : $guestsData->get('email');
        $phone = !$guestsData->get('phone') ? 'N/A' : $guestsData->get('phone');
        $first_name = !$guestsData->get('name') ? 'N/A' : $guestsData->get('name');
        $last_name = !$guestsData->get('surnames') ? 'N/A' : $guestsData->get('surnames');
        $country = !$guestsData->get('country_iso') ? 'US' : $guestsData->get('country_iso');

        return [
            "birthDate" => $birthdate,
            "contact" => [
                "address" => "N/A",
                "city" => "N/A",
                "email" => $email,
                "phone" => $phone,
                "state" => "N/A",
                "zip" => "N/A",
                "country" => $country
            ],
            "name" => [
                "first" => $first_name,
                "last" => $last_name
            ],
            "title" => "Mr"
        ];
    }

    private function getHyperguestHotelsReservationData($reservations)
    {
        $reservationData = collect();
        $exists_hotels_hyperguest = false;
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
                    ['hotel' => $hotel_id]));
            }

            $rate_plan_room_id = $selectedRoomRate['rate_plan_room_id'];
            $check_in = $selectedRoomRate['date_from'];
            $check_out = $selectedRoomRate['date_to'];

            $ratePlanRoomKey = $rate_plan_room_id . '_' . $key;

            //Todo extraes la data del cache seleccionado (token_search)
            if ($best_option) {
                $selectedRoomData = $this->setRoomRateData($token_search, $roomIdent, $hotel_id, $rate_plan_room_id,
                    $check_in, $check_out, $best_option, null, null, [], null, null);
            } else {
                $selectedRoomData = $this->setRoomRateData($token_search, $roomIdent, $hotel_id, $rate_plan_room_id,
                    $check_in, $check_out, $best_option, $selectedRoomRate['quantity_adults'],
                    $selectedRoomRate['quantity_child'], $selectedRoomRate['child_ages'],null, (isset($selectedRoomRate['set_markup']) ? $selectedRoomRate['set_markup'] : null));
            }

            $rateProviderMethod = NULL;

            if (isset($selectedRoomData['selectedRatePlanRoom']['rate']['rateProviderMethod'])) {
                $rateProviderMethod = $selectedRoomData['selectedRatePlanRoom']['rate']['rateProviderMethod'];
            }

            if ($selectedRoomData['selectedRatePlanRoom']['rate']['channel_id'] == 6 and $rateProviderMethod === NULL) {

                $exists_hotels_hyperguest = true;
                $selectedRatePlanRoom = $selectedRoomData['selectedRatePlanRoom'];
                $selectedRate = $selectedRatePlanRoom['rate'];
                $firstPenaltyDate = !empty($selectedRatePlanRoom['policy_cancellation']['apply_date']) ? $selectedRatePlanRoom['policy_cancellation']['apply_date'] : Carbon::now('America/Lima')->startOfDay()->format('Y-m-d');
                $room_id = $selectedRoomData['selectedRoom']['id'];
                $rates_plan_id = $selectedRate['rate_plan']['id'];
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

                /** @var Collection $hotels_token */
                $hotels_token = $reservationData->offsetGet($token_dates)['hotels'];

                if (!$hotels_token->offsetExists($hotel_id)) {
                    $hotel = $selectedRoomData['selectedHotel']['hotel'];
                    $hotel['hotel_code'] = empty($hotelChannelByCode['AURORA']['code']) ? '' : $hotelChannelByCode['AURORA']['code'];
                    $hotel['notes'] = (isset($selectedRoomRate['notes']) and !empty($selectedRoomRate['notes'])) ? $selectedRoomRate['notes'] : null;

                    $hotels_token->offsetSet($hotel_id, collect([
                        'hotel' => collect($hotel),
                        'rooms' => collect(),
                        'rates_plans' => collect(),
                        'rates_plans_rooms' => collect(),
                    ]));
                }

                /** @var Collection $hotel */
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
                        'room_code' => empty($roomChannelsByCode['AURORA']['code']) ? '' : $roomChannelsByCode['AURORA']['code'],
                        'name' => empty($roomTranslations['room_name']) ? '' : $roomTranslations['room_name'],
                        'description' => empty($roomTranslations['room_description']) ? '' : $roomTranslations['room_description'],
                    ]));
                }

                if (!$hotel['rates_plans']->offsetExists($ratesPlanKey)) {
                    $hotel['rates_plans']->offsetSet($ratesPlanKey, collect($selectedRate['rate_plan']));
                }

                if (!$hotel['rates_plans_rooms']->offsetExists($ratePlanRoomKey)) {
                    $hotel['rates_plans_rooms']->offsetSet($ratePlanRoomKey, collect());
                }

                /** @var Collection $hotel */
                $rates_plans_rooms = $hotel['rates_plans_rooms']->offsetGet($selectedRate['id'] . '_' . $key);


                $meal = [
                    'id' => $selectedRate['rate_plan']['meal']['id'],
                    'name' => collect($selectedRate['rate_plan']['meal']['translations'])->first(function ($item) {
                        return $item['slug'] == 'meal_name' and $item['language_id'] == 1;
                    })['value']
                ];
                // die("eeeeee");
                $rates_plan_room_new = $this->getChannelsAvailableRates(
                    $selectedRate,
                    $selectedRate['quantity_adults'],
                    empty($selectedRate['quantity_child']) ? 0 : $selectedRate['quantity_child'],
                    empty($selectedRate['ages_child']) ? [] : $selectedRate['ages_child'],
                    $hotel['hotel'],
                    $selectedRoomData['selectedRoom'],
                    true
                );

                // if(!isset($rates_plan_room_new['policies_cancelation'])){
                //     dd("no debe entrar aqui",$rates_plan_room_new);
                // }

                $rate_plan_name_commercial = $hotel['rates_plans'][$ratesPlanKey]['name'];

                if (isset($hotel['rates_plans'][$ratesPlanKey]['translations'])) {
                    $rate_plan_name_commercial = (count($hotel['rates_plans'][$ratesPlanKey]['translations']) > 0) ? $hotel['rates_plans'][$ratesPlanKey]['translations'][0]['value'] : $hotel['rates_plans'][$ratesPlanKey]['name'];
                }

                $rates_plans_rooms[$roomIdent] = [
                    'chain_id' => $hotel['hotel']['chain_id'],
                    'hotel_id' => $hotel['hotel']['id'],
                    'hotel_name' => $hotel['hotel']['name'],
                    'hotel_code' => $hotel['hotel']['hotel_code'],
                    'check_in' => $check_in,
                    'check_out' => $check_out,
                    'first_penalty_date' => $firstPenaltyDate,
                    'policies_cancelation' => $rates_plan_room_new['policies_cancelation'] ?? null,
                    'channel_id' => $rates_plan_room_new['channel_id'],
                    'channel_code' => $rates_plan_room_new['channel']['code'],
                    'channel_hotel_code' => empty($hotelChannelById[$channel_id]) ? '' : $hotelChannelById[$channel_id]['code'],
                    'channel_room_code' => empty($roomChannelsByById[$channel_id]) ? '' : $roomChannelsByById[$channel_id]['code'],
                    'room_id' => $hotel['rooms'][$roomKey]['id'],
                    'room_key' => $roomKey,
                    'room_name' => $hotel['rooms'][$roomKey]['name'],
                    'room_code' => $hotel['rooms'][$roomKey]['room_code'],
                    'room_description' => $hotel['rooms'][$roomKey]['description'],
                    'room_type_id' => $hotel['rooms'][$roomKey]['room_type_id'],
                    'max_capacity' => $hotel['rooms'][$roomKey]['max_capacity'],
                    'min_adults' => $hotel['rooms'][$roomKey]['min_adults'],
                    'max_adults' => $hotel['rooms'][$roomKey]['max_adults'],
                    'max_child' => $hotel['rooms'][$roomKey]['max_child'],
                    'rates_plan_id' => $hotel['rates_plans'][$ratesPlanKey]['id'],
                    'rate_plan_code' => $hotel['rates_plans'][$ratesPlanKey]['code'],
                    'rate_plan_name' => $hotel['rates_plans'][$ratesPlanKey]['name'],
                    'rate_plan_name_commercial' => $rate_plan_name_commercial,
                    'markup' => (float)$rates_plan_room_new['markup']['markup'],
                    'meal_id' => $meal['id'],
                    'meal_name' => $meal['name'],
                    'rates_plans_room_id' => $rates_plan_room_new['id'],
                    'onRequest' => (isset($selectedRoomRate['only_on_request']) and $selectedRoomRate['only_on_request'] == true) ? 0 : $rates_plan_room_new['status'],
                    'adult_num' => $rates_plan_room_new['quantity_adults'],
                    'child_num' => empty($rates_plan_room_new['quantity_child']) ? 0 : $rates_plan_room_new['quantity_child'],
                    'has_channel_child_rate' => $rates_plan_room_new['has_channel_child_rate'],
                    'guest_note' => empty($rates_plan_room_new['guest_note']) ? '' : $selectedRoomRate['guest_note'],
                    'is_modification' => empty($rates_plan_room_new['is_modification']) ? 0 : $selectedRoomRate['is_modification'],
                    'total_amount' => $rates_plan_room_new['total_amount'],


                    'total_amount_base' => $selectedRate['total_amount_base'],
                    // Se necesita los totales sin markup
                    'total_amount_adult_base' => $selectedRate['total_amount_adult_base'],
                    //  Se necesita los totales sin markup


                    'total_amount_child_base' => $rates_plan_room_new['total_amount_child_base'],
                    'calendarys' => $rates_plan_room_new['calendarys'],
                    'taxes_and_services' => $selectedRatePlanRoom['taxes_and_services']
                ];
            }
        }

        if ($exists_hotels_hyperguest == false) {
            $reservationData = [];
        }

        return $reservationData;
    }

    /**
     * @throws Exception
     */
    public function validateInputs($request)
    {
        if (!is_array($request->input('guests')) || count($request->input('guests')) == 0) {
            throw new \Exception(trans('validations.reservations.passenger_required'), 1001);
        }

        $passengers = $request->input('guests');

        foreach ($passengers as $index => $passenger) {

            $date_birth = null;
            $ageValidator = new AgeValidator();

            if (isset($passenger['date_birth'])) {

                $date_birth = $passenger['date_birth'];
                $dateIsInvalid = !$ageValidator->isValidDate($date_birth);
                if ($dateIsInvalid) {
                    throw new \Exception("The passenger's date of birth is incorrect: " . $date_birth, 1001);
                }
            }

            if ($date_birth !== null) {
                $isUnderdage = $ageValidator->isChild($date_birth);

                if ($passenger['type'] == 'ADL' && $isUnderdage) {
                    throw new \Exception("The adult passenger is the wrong age: " . $date_birth, 1001);
                }

                if ($passenger['type'] == 'CHD' && !$isUnderdage) {
                    throw new \Exception("The child passenger is the wrong age: " . $date_birth, 1001);
                }

            } else {
                if ($passenger['type'] == 'CHD') {
                    throw new \Exception("The child's date of birth is mandatory.", 1001);
                }
            }

        }


        if ((!is_array($request->input('reservations')) or count($request->input('reservations')) == 0) and
            (!is_array($request->input('reservations_services')) or count($request->input('reservations_services')) == 0) and
            (!is_array($request->input('reservations_flights')) or count($request->input('reservations_flights')) == 0)) {
            throw new \Exception(trans('validations.reservations.service_or_hotel_required'), 1001);
        }

        if ($request->has('entity') and $request->input('entity') !== 'Cart' and (empty($request->input('entity')) or empty($request->input('object_id')))) {
            throw new \Exception(trans('validations.reservations.required_entity_and_object_id'), 1001);
        }

        $this->validateBillingData($request);
        $this->validateQuote($request);
    }

    public function checkTokenSearchService($token_search)
    {
        return $this->getHotelsByTokenSearch($token_search);
    }

    public function tokenRemainingTime($token_search)
    {
        return $this->getTokenRemainingTime($token_search);
    }

    public function getServiceRegionId($code)
    {

        if ($code == null || $code == '') {
            return null;
        }

        $regionId = null;

        $service = Service::with([
            'serviceOrigin.country.businessRegions' => function ($query) {
                $query->whereNull('business_region_country.deleted_at')
                    ->whereNull('business_regions.deleted_at');
            }
        ])
            ->where('aurora_code', $code)
            ->whereNull('deleted_at')
            ->first();

        if (
            $service &&
            $service->serviceOrigin &&
            $service->serviceOrigin->first() &&
            $service->serviceOrigin->first()->country &&
            $service->serviceOrigin->first()->country->businessRegions &&
            $service->serviceOrigin->first()->country->businessRegions->first()
        ) {
            $regionId = $service->serviceOrigin->first()->country->businessRegions->first()->id;
        }

        return $regionId;
    }

    public function getHotelRegionId($code)
    {

        if ($code == null || $code == '') {
            return null;
        }

        $regionId = null;

        $hotel = Hotel::whereHas('channel', function ($query) use ($code) {
            $query->where('code', $code);
        })
            ->with(['country.businessRegions'])
            ->first();

        if ($hotel && $hotel->country && $hotel->country->businessRegions) {
            $regionId = $hotel->country->businessRegions->first()->id;
        }

        return $regionId;
    }
}

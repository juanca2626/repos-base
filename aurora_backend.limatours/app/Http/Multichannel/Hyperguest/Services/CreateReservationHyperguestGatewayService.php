<?php

namespace App\Http\Multichannel\Hyperguest\Services;

use App\Http\Aurora\Hotels\Traits\SetReservations;
use App\IntegrationHyperguest;
use App\Jobs\SendNotificationHyperguest;
use App\User;
use Exception;
use Illuminate\Http\Request;

class CreateReservationHyperguestGatewayService
{
    use SetReservations;

    public function validationHyperguestHotels(Request $request, $reference, $reservation): array
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

        // TODO: Guardando pasajeros
        $guests = $request->input('guests');
        $reservationPassengers = $this->getPassengersData($guests);
        $reservationPassengers = $reservationPassengers->toArray();

        $passengers = collect($reservationPassengers[0]);
        $firstGuestData = $this->setHyperguestRoomGuest($passengers);

        // TODO: Guardando hoteles
        $reservations = $request->input('reservations');
        $reservationData = $this->getHyperguestHotelsReservationData($reservations);

        if (empty($reservationData)) {
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
                foreach ($reservationDatum['hotels'] as $index => $reservationHotelData) {

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

                    //foreach ($reservationHotelData['rates_plans_rooms'] as $rates_plans_rooms) {
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

                        $response = $this->createReservationLegacy([
                            'hotel' => $hotel
                        ]);

                        $reservation = $response['reservation'] ?? null;
                        $data = json_decode($reservation, true);

                        if (isset($data['error'])) {
                            $success = false;
                            $error = json_encode($data["error"]) . ' ' . json_encode($data["errorDetails"]) . ' ' . $error_msg;
                        }

                        // if (isset($data["content"]["bookingId"])) {
                        //     $channel_reservation_code = $data["content"]["bookingId"];
                        // }

                        if (isset($data["bookingId"])) {
                            $channel_reservation_code = $data["bookingId"];
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
                        $reservations['error'] = $error;
                        $hotels_reservation[] = $reservations;

                        if (!$success) {
                            SendNotificationHyperguest::dispatchNow(
                                $emails,
                                $reservations,
                                json_encode($hotel),
                                json_encode($data)
                            );
                        }
                    } catch (Exception $e) {
                        $reservations['success'] = false;
                        $reservations['error'] = $e->getMessage();
                        $hotels_reservation[] = $reservations;
                    }
                }
            }

            return [
                "success" => true,
                "hotels_reservation" => $hotels_reservation
            ];
        }
    }

    // Desde canal Aurora
    public function createReservationLegacy(array $params): ?array
    {
        [
            "hotel" => $hotel,
        ] = $params;

        $paramsCreateReservation = [
            "guests" => $hotel['leadGuest'],
            "file" => $hotel['reference']['agency'],
            "hotel_id" => $hotel['hotelId'],
            "date_from" => $hotel['dates']['from'],
            "date_to" => $hotel['dates']['to'],
            "reference" => $hotel['reference'],
            "rooms" => $hotel['rooms'],
            "leadGuest" => $hotel['leadGuest'],
        ];
        $responseChannelReservation = $this->createReservationGateway($paramsCreateReservation);

        return $responseChannelReservation;
    }

    // Crear reserva en canal Hyperguest
    private function createReservationGateway(array $params): ?array
    {
        [
            "file" => $fileCode,
            "hotel_id" => $hotelId,
            "date_from" => $dateFrom,
            "date_to" => $dateTo,
            "reference" => $reference,
            "rooms" => $rooms,
            "leadGuest" => $leadGuest,
        ] = $params;

        $channelIntegration = [
            'channelIntegration' => [
                'channel' => 'hyperguest',
                'type' => 'PULL',
                'version' => 'v1',
                'isActive' => true
            ]
        ];
        $paramsCreateReservation = [
            "legacyReservationId" => $fileCode,
            "hotel_id" => $hotelId,
            "date_from" => $dateFrom,
            "date_to" => $dateTo,
            "reference" => $reference,
            "rooms" => $rooms,
            "leadGuest" => $leadGuest,
            "isTest" => true
        ];

        $payload = array_merge($channelIntegration, $paramsCreateReservation);

        try {
            $gatewayService = new HyperguestGatewayService();
            $response = $gatewayService->createReservation($payload);

            if (!$response['success']) {
                throw new Exception($response['error']);
            }

            $result = $response['result'];

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $result;
    }
}

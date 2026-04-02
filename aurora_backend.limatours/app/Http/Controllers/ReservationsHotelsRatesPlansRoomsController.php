<?php

namespace App\Http\Controllers;

use App\ReservationsHotelsRatesPlansRooms;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ReservationsHotelsRatesPlansRoomsController extends Controller
{

    public function update_status_room(Request $request)
    {
        try {

            $file_code = $request->input('file_code');
            $origin = $request->input('origin');
            $data_rooms = $request->input('data_rooms');

            $error_ = 0;
            $error_message = "";

            if( !isset( $file_code ) ){
                $error_++;
                $error_message = "Código del File inexistente";
            } else{

                foreach ( $data_rooms as $data_room ){

                    $room_id = $data_room['room_id'];
                    $reservation_code = $data_room['reservation_code'];
                    $onRequest = $data_room['status'];
                    if( $onRequest === 'RQ' ){
                        $onRequest = 0;
                    }
                    if( $onRequest === 'OK' ){
                        $onRequest = 1;
                    }

                    $reservations_hotels_rates_plans_room = ReservationsHotelsRatesPlansRooms::where('id', $room_id)
                        ->with('reservation_hotel.reservation')
                        ->first();

                    if( $reservations_hotels_rates_plans_room ){

                        if( $reservations_hotels_rates_plans_room->reservation_hotel->reservation->file_code != $file_code ){
                            $error_++;
                            $error_message = "El codigo del file ". $file_code ." no coincide con el room_id " . $room_id;
                            break;
                        }

                        if( $origin === 'stella' ){
                            $reservations_hotels_rates_plans_room->stella_updated_at = Carbon::now();
                        }
                        $reservations_hotels_rates_plans_room->channel_reservation_code = $reservation_code;
                        $reservations_hotels_rates_plans_room->onRequest = $onRequest;
                        $reservations_hotels_rates_plans_room->save();

                    } else {
                        $error_++;
                        $error_message = "room_id " . $room_id . " inexistente";
                        break;
                    }
                }

            }

            if( $error_ === 0 ){
                $response = ['success' => true];
            } else {
                $response = ['success' => false, "message"=>$error_message];
            }

        } catch (\Exception $e) {
            $response = ['success' => false, 'error' => $e->getMessage()];
        }


        return Response::json($response);
    }
}

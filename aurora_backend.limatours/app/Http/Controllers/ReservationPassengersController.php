<?php

namespace App\Http\Controllers;

use App\File;
use App\Reservation;
use App\ReservationPassenger;
use App\Http\Traits\Files;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReservationPassengersController extends Controller
{
    use Files;

    public function search_by_file_number($nroref, Request $request){

        $file = File::where('file_number', $nroref)->first();
        $import_file_success = true;
        $import_file_message = "";
        if( !$file ) {
            $import_file_ = $this->import_file($nroref);
            $import_file_success = $import_file_['success'];
            $import_file_message = $import_file_['message'];
        }
        if( $import_file_success ) {
            $reservation = Reservation::where('file_code', $nroref)->first();

            $passengers = ReservationPassenger::where('reservation_id', $reservation->id)
                ->with('reservation')
                ->get();

            if(count($passengers)===0){
                $passengers_import_ifx = $this->import_passengers($nroref, $reservation->id);

    //            return array(
    //                "datasss" => $passengers_import_ifx,
    //            );

                if($passengers_import_ifx['success']) {
                    $passengers = ReservationPassenger::where('reservation_id', $reservation->id)
                        ->with('reservation')
                        ->get();
                    $data = array(
                        "data" => $passengers,
                        "success" => true,
                        "message" => "Importado correctamente"
                    );
                } else {
                    $data = array(
                        "data" => [],
                        "success" => false,
                        "message" => "Error import ". $passengers_import_ifx['message']
                    );
                }
            } else {
                $data = array(
                    "data" => $passengers,
                    "success" => true,
                    "message" => "Ya en mysql!"
                );
            }
        } else {
            $data = array(
                "data" => [],
                "success" => false,
                "message" => $import_file_message
            );
        }

        return Response::json($data);

    }
}

<?php

namespace App\Http\Controllers;

use App\File;
use App\FileAccommodation;
use App\FileService;
use App\Http\Stella\StellaService;
use App\ReservationPassenger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class FileAccommodationsController extends Controller
{
    protected $stellaService;

    public function __construct(StellaService $stellaService)
    {
        $this->stellaService = $stellaService;
    }

    public function store($nroref, Request $request){

        $file = File::where('file_number', $nroref)->first();

        $array = [
            "codsvs" => $request->input('codsvs'),
            "rooms" => $request->input('rooms'),
            "variations" => $request->input('variations')
        ];

        $file_service_ids = [];
        $nroites_service_id = [];
        foreach ( $array['variations'] as $variation ){
            array_push($file_service_ids, $variation['id']);
            $nroites_service_id[$variation['nroite']] = $variation['id'];
        }
//        "DELETE from t41 where nroemp = 5 and ideref = 'R' and nroref = " + nroref + " and tipsvs = 'H' and codsvs = '" + codsvs + "'";
        FileAccommodation::whereIn('file_service_id', $file_service_ids)->delete();

        $types = ['', 'S', 'D', 'T']; // Simple - Double - Triple

        foreach ( $array['rooms'] as $room_k => $room ){
            foreach( $room as $k => $r ){
                try{
                    $types[count($r)];
                } catch (\Exception $e){
                    return array(
                        "success" => false,
                        "message" => "Acomodación no valida para el tipo de hab.",
                        "error" => $e
                    );
                    break;
                    break;
                }
//                var_export($k);
                foreach( $r as $r_ ){
                    $reservation_passenger = ReservationPassenger::where('reservation_id', $file->reservation_id)
                        ->where('sequence_number', $r_['nrosec'])->first();

                    $new_file_accommodation = new FileAccommodation();
                    $new_file_accommodation->file_service_id = $nroites_service_id[$room_k];
                    $new_file_accommodation->reservation_passenger_id = $reservation_passenger->id;
                    $new_file_accommodation->room_key = $types[count($r)] . '' . $k;
                    $new_file_accommodation->save();
                }
            }
        }

        $this->stellaService->save_room_paxs_by_nroite($nroref, $array);

        $data = array(
            "success" => true
        );

        return Response::json($data);
    }

    public function store_general($nroref, Request $request){

        $file = File::where('file_number', $nroref)->first();

        $array = [
            "simple" => $request->input('simple'),
            "doble" => $request->input('doble'),
            "triple" => $request->input('triple'),
            "hotels" => $request->input('hotels')
        ];

        $types = ['', 'S', 'D', 'T']; // Simple - Double - Triple

        foreach ( $array['hotels'] as $h => $hotel ){

            $check = $hotel['check'];

            if($check === true || $check === 'true')
            {
                $rooms = $hotel['variations'];
                $nroite_ = $hotel['nroite'];
                $used = [];

                // "DELETE from t41 where nroemp = 5 and ideref = 'R' and nroref = " + nroref + " and tipsvs = 'H' and codsvs = '" + codsvs + "'";
                $file_service_id_ = FileService::where('file_id', $file->id )->where('item_number', $nroite_)->first()->id;
                FileAccommodation::where('file_service_id', $file_service_id_)->delete();

                foreach ( $rooms as $room_k => $room ){
                    $nroite = $room['nroite'];
                    $groups = [];

                    $file_service = FileService::where('file_id', $file->id)->where('item_number', $nroite)->first();

                    if( $room['anulado'] == 0 ){
                        $limite = intval($room['canpax'] / $room['cantid']); // 1
                        $type = $types[$limite];

                        if($type == 'S')
                        {
                            $groups = $array['simple'];
                        }

                        if($type == 'D')
                        {
                            $groups = $array['doble'];
                        }

                        if($type == 'T')
                        {
                            $groups = $array['triple'];
                        }

                        for($i=1; $i<=$room['cantid']; $i++){
                            $passengers = 1;

                            foreach( $groups as $p=>$passenger) {

                                if(!in_array($passenger['nrosec'], $used) && $passengers <= $limite)
                                {
                                    $passenger_ = ReservationPassenger::where('reservation_id', $file->reservation_id)
                                        ->where('sequence_number', $passenger['nrosec'])->first();

                                    $new_accommodation = new FileAccommodation();
                                    $new_accommodation->file_service_id = $file_service->id;
                                    $new_accommodation->reservation_passenger_id = $passenger_->id;
                                    $new_accommodation->room_key = $type . '' . $i;
                                    $new_accommodation->save();

                                    array_push($used, $passenger['nrosec']);
                                    $passengers += 1;
                                }
                            }
                        }

                    }
                }

            }

        }

        $this->stellaService->save_room_paxs_general($nroref, $array);

        $data = array(
            "success" => true
        );

        return Response::json($data);
    }

    public function search_room($file_service_id, $number_room){

        $data_ = FileAccommodation::where('file_service_id', $file_service_id)
                    ->where('room_key', 'like', '%'.$number_room)
                    ->with(['passenger'])
                    ->get();

        $data = array(
            "data" => $data_,
            "success" => true,
        );
        return Response::json($data);
    }

    public function search_by_service($file_number, Request $request){

        $item_number = $request->input('item_number');

        $file = File::where('file_number', $file_number)->first();

        $file_service_ids = FileService::where('file_id', $file->id)
            ->where('item_number', $item_number)->pluck('id');

        $data_ = FileAccommodation::whereIn('file_service_id', $file_service_ids)
                    ->with(['passenger', 'service'])
                    ->get();

        $data = array(
            "data" => ( count($data_)>0) ? $this->translate_data_ifx($file_number, $data_) : [],
            "success" => true,
        );
        return Response::json($data);
    }

    public function translate_data_ifx($nrofile, $data){

        $response = [];

        foreach ( $data as $d ){
            array_push( $response, [
                "id" => $d->id,
                "nroemp" => 5,
                "ideref" => "R",
                "nroref" => (int)$nrofile,
                "nroite" => $d->service->item_number,
                "tipsvs" => ( TRIM( $d->service->type_code_ifx ) === 'HTL' ) ? "H" : TRIM( $d->service->type_code_ifx ),
                "codsvs" => $d->service->code,
                "nrosec" => $d->passenger->sequence_number,
                "nombre" => $d->passenger->name . ' ' . $d->passenger->surnames,
                "nrohab" => $d->room_key
            ] );
        }

        return $response;
    }

}

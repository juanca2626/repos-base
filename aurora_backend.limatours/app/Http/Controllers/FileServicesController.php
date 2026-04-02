<?php

namespace App\Http\Controllers;

use App\ChannelHotel;
use App\File;
use App\FileAccommodation;
use App\FileImportLog;
use App\FileService;
use App\Hotel;
use App\Language;
use App\Mail\ReservationHotel;
use App\Reservation;
use App\ReservationsHotel;
use App\ReservationsHotelsRatesPlansRooms;
use App\Http\Traits\Files;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;

class FileServicesController extends Controller
{
    use Files;

    public function search_by_file_number($nroref, Request $request){

        $file = File::where('file_number', $nroref)->first();

        if( !$file ) {
            $this->import_file($nroref);
            $file = File::where('file_number', $nroref)->first();
        }

        $services = FileService::where('file_id', $file->id)
            ->where('status_ifx', 'OK')
            ->whereIn('classification', [1, 5, 6])
            ->get();
        foreach ( $services as $service ){
            $services_ids = FileService::where('code', $service->code)->pluck('id');
            $service->flag_accommodation = FileAccommodation::whereIn('file_service_id', $services_ids)->count();
        }

        if(count($services)===0){
            $services_import_ifx = $this->import_services($nroref, $file->id, '');

//            return $services_import_ifx;

            if($services_import_ifx['success']) {
                $services = FileService::where('file_id', $file->id)
                    ->where('status_ifx', 'OK')
                    ->whereIn('classification', [1, 5, 6])
                    ->get();
                foreach ( $services as $service ){
                    $services_ids = FileService::where('code', $service->code)->pluck('id');
                    $service->flag_accommodation = FileAccommodation::whereIn('file_service_id', $services_ids)->count();
                }
                $data = array(
                    "data" => $services,
                    "file" => $file,
                    "success" => true,
                    "message" => "Importado correctamente"
                );
            } else {
                $data = array(
                    "data" => [],
                    "file" => $file,
                    "success" => false,
                    "message" => "Error import ". $services_import_ifx['message']
                );
            }
        } else {
            $data = array(
                "data" => $services,
                "file" => $file,
                "success" => true,
                "message" => "Ya en mysql!"
            );
        }

        return Response::json($data);

    }

    public function search_by_service_code($nroref, $codsvs){

        $file = File::where('file_number', $nroref)
            ->with(['reservation.executive'])
            ->with(['reservation.client.countries.translations'=>function($query){
                $query->where('language_id',1);
            }])
            ->first();

        $hotel_ = ChannelHotel::where('code', $codsvs)->first();
        $hotel = Hotel::where('id', $hotel_->hotel_id)
            ->first();
        if(!$hotel)
        {
            $hotel = '';
        }

        if( $file )
        {
            $services = FileService::where('file_id', $file->id)
                ->where('status_ifx', '!=', 'XL')
                ->where('code', $codsvs)
                ->get();

            $reimport = false;

            if(count($services) == 0) // REIMPORT..
            {
                $services_current_nroites = FileService::where('file_id', $file->id)->pluck('item_number');

                $nroites_not_in = [];
                foreach ( $services_current_nroites as $current_nroite ){
                    array_push( $nroites_not_in, $current_nroite );
                }
                $nroites_not_in = implode(',', $nroites_not_in);

                $this->import_services($file->file_number, $file->id, $nroites_not_in);

                $services = FileService::where('file_id', $file->id)
                    ->where('status_ifx', '!=', 'XL')
                    ->where('code', $codsvs)
                    ->get();

                $reimport = true;
            }

            $data = array(
                "data" => $services,
                "hotel" => $hotel,
                "file" => $file,
                "success" => true,
                "reimport" => $reimport,
                "message" => (count($services)===0) ? "Sin servicios" : "Con servicios"
            );

        }
        else
        {
            $max = File::max('file_number');

            if($nroref < $max)
            {
                $log = FileImportLog::where('file', '=', $nroref)->first();

                if($log == null OR $log == '')
                {
                    $log = new FileImportLog;
                }

                $log->file = $nroref;
                $log->date = date("Y-m-d H:i:s");
                $log->status = 0;
                $log->message = 'Files - File not found';
                $log->save();
            }

            $data = array(
                "data" => [],
                "hotel" => $hotel,
                "file" => [],
                "success" => false,
                "message" => "File not found"
            );
        }

        return Response::json($data);

    }

    public function search_by_service_code_backup($nroref, $codsvs){

        $file = File::where('file_number', $nroref)
            ->with(['reservation.executive'])
            ->with(['reservation.client.countries.translations'=>function($query){
                $query->where('language_id',1);
            }])
            ->first();

        $hotel_ = ChannelHotel::where('code', $codsvs)->first();
        $hotel = Hotel::where('id', $hotel_->hotel_id)
            ->first();
        if(!$hotel){
            $hotel = '';
        }

        $import_file_success = true;
        $import_file_message = "";
        if( !$file ) {
            $import_file_ = $this->import_file($nroref);
            $import_file_success = $import_file_['success'];
            $import_file_message = $import_file_['message'];
            $file = File::where('file_number', $nroref)
                ->with(['reservation.executive'])
                ->with(['reservation.client.countries.translations'=>function($query){
                    $query->where('language_id',1);
                }])
                ->first();
        }

        if( $import_file_success ) {
            $services = FileService::where('file_id', $file->id)
                ->where('status_ifx', '!=', 'XL')
                ->where('code', $codsvs)
                ->get();

            if(count($services)===0){
                $services_import_ifx = $this->import_services($nroref, $file->id, '');

    //            return $services_import_ifx;

                if($services_import_ifx['success']) {
                    $services = FileService::where('file_id', $file->id)
                        ->where('status_ifx', '!=', 'XL')
                        ->where('code', $codsvs)
                        ->get();
                    $data = array(
                        "data" => $services,
                        "hotel" => $hotel,
                        "file" => $file,
                        "success" => true,
                        "message" => "Importado correctamente"
                    );
                } else {
                    $data = array(
                        "data" => [],
                        "hotel" => $hotel,
                        "file" => $file,
                        "success" => false,
                        "message" => "Error import ". $services_import_ifx['message']
                    );
                }
            } else {
                $data = array(
                    "data" => $services,
                    "hotel" => $hotel,
                    "file" => $file,
                    "success" => true,
                    "message" => "Ya en mysql!"
                );
            }
        } else{
            $data = array(
                "data" => [],
                "hotel" => $hotel,
                "file" => [],
                "success" => false,
                "message" => $import_file_message
            );
        }

        return Response::json($data);

    }

    public function search_components($nroref, $nroite){

        $file = File::where('file_number', $nroref)->first();

        $components = FileService::where('file_id', $file->id)
            ->where('status_ifx', '!=', 'XL')
            ->where('item_number_parent', $nroite)
            ->get();
        foreach ( $components as $service ){
            $services_ids = FileService::where('code', $service->code)->pluck('id');
            $service->flag_accommodation = FileAccommodation::whereIn('file_service_id', $services_ids)->count();
        }

        $data = array(
            "success" => true,
            "data" => $components,
            "file" => $file
        );

        return Response::json($data);

    }

    public function save_confirmation_codes($nrofile, Request $request){
        try {
            $services = (array)$request->input('services');
            if(config('app.env') == 'production') {
                $client = new \GuzzleHttp\Client();
            } else {
                $client = new \GuzzleHttp\Client(["verify" => false]);
            }
            $response = $client->request('POST',
                config('services.stella.domain') . 'api/v1/services/'. $nrofile .'/codcfm', [
                    "json" => [
                        'services' => $services
                    ]
                ]);
            $response = json_decode($response->getBody()->getContents());

            if( $response->success ){

                $file_ = File::where('file_number', $nrofile)->first();

                /**/
                $reservations_hotels_array = [];
                foreach ( $services as $service ){
                    $file_service = FileService::find($service['id']);
                    $file_service->status_hotel = "OK";
                    $file_service->confirmation_code = $service['confirmation_code'];
                    $file_service->reservation_for_send = "N";
                    $file_service->reservation_sent = "NO";

                    if( $file_service->save() ){
                        // Save in Reservations Module
                        if( !isset($reservations_hotels_array[$file_service->code.'_'.$file_service->date_in.'_'.$file_service->date_out]) ) {
                            $reservations_hotels_array[$file_service->code.'_'.$file_service->date_in.'_'.$file_service->date_out] = true;
                            $reservations_hotels = ReservationsHotel::where('reservation_id', $file_->reservation_id)
                                ->where('hotel_code', $file_service->code)
                                ->where('check_in', $file_service->date_in)
                                ->where('check_out', $file_service->date_out)
                                ->get();
                            foreach ($reservations_hotels as $reservations_hotel){
                                $rate_plan_rooms =
                                    ReservationsHotelsRatesPlansRooms::where('reservations_hotel_id', $reservations_hotel->id)->get();
                                foreach ($rate_plan_rooms as $rate_plan_room){
                                    $rate_plan_room->channel_reservation_code = $service['confirmation_code'];
                                    $rate_plan_room->onRequest = 1;
                                    $rate_plan_room->external_updated_at = date('Y-m-d H:i:s');
                                    $rate_plan_room->save();
                                }
                            }
                        }
                    }

                }
                /**/
                $response = [
                    'success' => true,
                    'response' => $response,
                    'message' => 'Código de confirmación Guardado correctamente'
                ];
            } else {
                $response = [
                    'data' => $response, // $call
                    'detail' => 'Error al guardar en ifx',
                    'success' => false
                ];
            }

        } catch (\Exception $ex) {
            $response = [
                'success' => false,
                'message' => $ex->getMessage()
            ];
        }

        return response()->json($response);
    }

    public function send_notification_confirmation_codes(Request $request){
        try {
            $to = $request->input('to');
            $cc = $request->input('cc');
            $link = $request->input('link');
            $provider_name = $request->input('provider_name');
            $executive_name = $request->input('executive_name');
            $executive_email = $request->input('executive_email');
            $provider_language_id = $request->input('provider_language_id');

            $hotel_code = $request->input('hotel_code');
            $file_number = $request->input('file_number');

            $lang_ = Language::find($provider_language_id);

            $reservation = Reservation::where('file_code', $file_number)->first();
            $file = File::where('file_number', $file_number)->first();

            if( $reservation ) {

                $data_ =
                    [
                        "id" => $reservation->id,
                        "hotel_name" => $provider_name,
                        "created_at" => $reservation->created_at,
                        "executive_name" => $executive_name, // --
                        'link' => $link,
                        'lang' => $lang_->iso,
                        'mail_type' => "HOTEL",
                        'file_code' => $file_number,
                        'customer_name' => $reservation->customer_name,
                        'executive_email' => $executive_email
                    ];

                $data_['hotels'] = [];

                $hotel_data = $this->find_hotel($hotel_code);
                $file_hotels = FileService::where('file_id', $file->id)
                    ->where('status_ifx', 'OK')
                    ->where('code', $hotel_code)
                    ->whereIn('classification', [1, 5, 6])
                    ->orderBy('date_in')
                    ->get();

                foreach ( $file_hotels as $file_hotel ){

                    if( $hotel_data !== '' ){
                        $check_in_time = $hotel_data->check_in_time;
                        $check_out_time = $hotel_data->check_out_time;
                    } else {
                        $check_in_time = $file_hotel->start_time;
                        $check_out_time = $file_hotel->departure_time;
                    }

                    array_push($data_['hotels'], [

                        "check_in" => $file_hotel->date_in,
                        "check_in_time" => $check_in_time,
                        "check_out" => $file_hotel->date_out,
                        "check_out_time" => $check_out_time,
                        "total_amount_base" => $file_hotel->total_amount,
                        'reservations_hotel_rooms' => [[
                            "room_name" =>
                                ($file_hotel->base_name_original === null)
                                    ? $file_hotel->base_name_initial
                                    : $file_hotel->base_name_original,
                            "room_code" => $file_hotel->base_code,
                            "rate_plan_name" => "",
                            "rate_plan_code" => "",
                            "adult_num" => $file->adults,
                            "child_num" => $file->children,
                            "extra_num" => $file->infants,
                            "onRequest" => ($file_hotel->status_hotel==='OK')?1:0,
                            "confirmation_code" => $file_hotel->confirmation_code,
                            "channel_code" =>"AURORA",
                            "guest_note" => ""
                        ]]
                    ]);
                }

                if (App::environment('production')) {
                    $mail = Mail::to($to);
                    $mail->cc($cc);
                }

                $mail->send(new ReservationHotel( $data_['lang'], $data_));

                $response = [
                    'success' => true,
                    'message' => 'Código de confirmación Guardado correctamente'
                ];

            } else {
                $response = [
                    'success' => false,
                    'message' => "Reserva Inexistente en Aurora"
                ];
            }

        } catch (\Exception $ex) {
            $response = [
                'success' => false,
                'message' => $ex->getMessage()
            ];
        }

        return response()->json($response);
    }


}

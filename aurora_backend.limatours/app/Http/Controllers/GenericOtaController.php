<?php

namespace App\Http\Controllers;

use App\CentralBooking;
use App\ExtensionGenericOtaHeader;
use App\ExtensionGenericOtaReserves;
use App\ExtensionGenericOtaService;
use App\Ota;
use App\ReserveFile;
use App\Service;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use App\Http\Traits\Aurora3;

class GenericOtaController extends Controller
{
    use Aurora3;
    
    public function booking(Request $request)
    {
        try
        {
            $fechoy = date("d/m/Y");
            $timnow = date("H:i");
            $codeXML = trim(str_replace("/", "", $fechoy)) . trim(str_replace(":", "", $timnow)) .
                $request->post('token');

            $findCodeXML = ExtensionGenericOtaHeader::where('code', $codeXML);

            if ($findCodeXML->count() == 0) {
                $services = $request->post("services");

                $new_header = new ExtensionGenericOtaHeader();
                $new_header->code = $codeXML;
                $new_header->email = $request->post('user');
                $new_header->date_created = $request->post('date_created');
                $new_header->status = 1;
                $new_header->ota_id = $request->post('ota_id');
                $new_header->save();


                $errors = 0;

                $ota = Ota::findorfail($request->post('ota_id'));

                foreach ($services as $service) {
                    $new_service = new ExtensionGenericOtaService();
                    $new_service->generic_ota_header_id = $new_header->id;
                    $new_service->code = $service["code"];
                    $new_service->date_start = $service["date_start"];
                    $new_service->date_end = $service["date_end"];
                    $new_service->paxs = $service["paxs"];
                    $new_service->passenger = $service["passenger"];
                    $new_service->passenger_email = $service["passenger_email"];
                    $new_service->status = $service["status"];
                    $new_service->booking_state = $service["booking_state"];
                    $new_service->type = $service["type"];
                    $new_service->name = $service["name"];
                    $new_service->detail = $service["detail"];
                    $new_service->multi_days = $service["multi_days"];
                    $new_service->package_id = $service["package_id"];
                    $new_service->ticket_type = $service["ticket_type"];
                    $new_service->aurora_code = $service["aurora_code"];

                    if (!($new_service->save())) {
                        $errors++;
                    }

                    $tags_generic_ota =
                        json_encode([
                            "passenger_email" => $service["passenger_email"],
                            "booking_state" => $service["booking_state"],
                            "name" => $service["name"],
                            "booking_code" => null,
                            "header_code" => $new_header->code,
                            "header_email" => $new_header->email,
                        ]);
                    DB::table('central_bookings')->insert([
                        "ota_id"=>$ota->id,
                        "channel_name" => $ota->name,
                        "model" => 'App\ExtensionGenericOtaService',
                        "object_id" => $new_service->id,
                        "code" => $new_service->code,
                        "tags" => $tags_generic_ota,
                        "status" => 1,
                        "made_date_time" => $new_header->date_created,
                        "start_date" => $new_service->date_start,
                        "end_date" => $new_service->date_end,
                        "description" => $new_service->detail,
                        "passenger" => $new_service->passenger,
                        "agent" => null,
                        "file_number" => null,
                        "aurora_code" => $service["aurora_code"],
                        "type_service" => $new_service->type,
                        "quantity_pax" => $new_service->paxs,
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);
                    // -------- GENERAL TABLE UNIFIED ----------------
                }

                if ($errors == 0) {

                    $users = [];
                    $permission =
                        DB::table("permissions")->where('slug', "reservationcentertourcms.create")->first();
                    if ($permission) {
                        $permission_role_ids =
                            DB::table("permission_role")->where('permission_id', $permission->id)->pluck('role_id');
                        $role_user_ids = DB::table('role_user')->whereIn('role_id', $permission_role_ids)->pluck('user_id');
                        $users = User::whereIn('id', $role_user_ids)->get();
                    }

                    foreach ($users as $user) {
                        $pushNoti = (object)[
                            'user' => $user->code,
                            'title' => strtoupper($ota->name) . " - Nueva reserva",
                            'body' => 'Ha llegado una nueva reserva a la Aurora 2 desde el correo de ' . strtoupper($ota->name) . '. Toque este mensaje para revisarlo.',
                            'click_action' => 'https://aurora.limatours.com.pe/central_bookings/en_espera'];
    //                    $this->sendPushNotification($pushNoti);
                    }

                    $response = array('response' => "OK", 'detail' => 'Guardado correctamente');
                } else {
                    $response = array('response' => "ERR", 'detail' => 'Error de Sistema al guardar servicios');
                }

            } else {
                $response = array('response' => "ERR",
                    'detail' => 'Ya fué registrado anteriormente.');
            }
            return json_encode([$response]);
        }
        catch(\Exception $ex)
        {
            return json_encode($this->throwError($ex));
        }
    }

    public function index(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('queryCustom');
        $filterBy = $request->input('filterBy');

        $filter = $request->input('filter');
        $order = $request->input('order');
        $ota_id = $request->input('ota');
        $reserve_passed = $request->input('reserve_passed');

        $multi_days = $request->input('multi_days');



        $list = ExtensionGenericOtaService::with('header.ota','reserves.file','package');

        if ($querySearch) {
            $list->where(function ($query) use ($querySearch) {
                $query->orWhere('code', 'like', '%' . $querySearch . '%');
                $query->orWhere('passenger', 'like', '%' . $querySearch . '%');
                $query->orWhere('passenger_email', 'like', '%' . $querySearch . '%');
                $query->orWhere('date_start', 'like', '%' . $querySearch . '%');
            });
        }
        if ($ota_id != ""){
            $header_ids = ExtensionGenericOtaHeader::select('id')->where('ota_id',$ota_id)->get();

            $list->whereIn('generic_ota_header_id',$header_ids);
        }

        if ($reserve_passed == "false"){
            $list->where('date_start','>=',Carbon::now());
        }

        if ($multi_days =="true"){

            $list->where('package_id','!=',null)->where('package_id','!=',0);
        }

        if ($filterBy == 0) {
            $list->where('status', 1);
        }

        $count = $list->count();

        if( $filter != "undefined" && $filter != null ){
            $_order = ( $order == 'true' ) ? 'desc' : 'asc';
            $list = $list
                ->orderBy($filter, $_order);

        } else {
            $list = $list->orderBy('date_start','asc');
        }

        if ($paging === 1) {
            $list = $list->take($limit)->get();
        } else {
            $list = $list->skip($limit * ($paging - 1))->take($limit)->get();
        }

        $list = $list->transform(function ($l) {
            $l['service'] = Service::where('aurora_code', $l->aurora_code)->first();
            return $l;
        });

        $data = [
            'data' => $list,
            'count' => $count,
            'success' => true,
        ];

        return Response::json($data);
    }

    public function updateStatus($id, Request $request)
    {
        $package = ExtensionGenericOtaService::find($id);
        $update_central = CentralBooking::where('model', 'App\ExtensionGenericOtaService')
            ->where('object_id', $id)
            ->first();
        if ($request->input("status")) {
            $package->status = 0;
            $update_central->status = 0;
        } else {
            $package->status = 1;
            $update_central->status = 1;
        }
        $package->save();
        $update_central->save();

        return Response::json(['success' => true]);
    }

    public function updateStatusExternal($id, Request $request)
    {
        $package = ExtensionGenericOtaService::find($id);

        /*
        $update_central = CentralBooking::where('model', 'App\ExtensionGenericOtaService')
            ->where('object_id', $id)
            ->first();
        if ($request->input("status")) {
            $package->status = 0;
            $update_central->status = 0;
        } else {
            $package->status = 1;
            $update_central->status = 1;
        }
        */

        $package->booking_state = $request->__get('code');
        $package->save();
        // $update_central->save();

        return Response::json(['success' => true]);
    }

    public function reserve(Request $request){

        $servs = $request->input('services');
        $nrofile = $request->input('nrofile');
        $codcli = $request->input('code');

        $json_params = $this->do_params( $servs, $nrofile, $codcli );
//        return json_encode( $json_params['response'] );

        if( $json_params['status'] == 'ok' ){
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST',
                config('services.stella.domain') . 'api/v1/stelaFiles/savemeridiam', [
                    "json" => [
                        'datapla' => $json_params['response']['datapla'],
                        'datasvs' => $json_params['response']['datasvs'],
                        'datahtl' => $json_params['response']['datahtl'],
                        'datapax' => $json_params['response']['datapax'],
                    ]
                ]);
            $response = json_decode($response->getBody()->getContents());

            if( $response->success && $response->process ){
                // Create File A3..
                $this->createFileOTSA3($json_params['response'], $json_params['response']['datapla']['operad']);
                
                $_nrofile = $response->data->nroref;

                $reserve_file_id = DB::table('reserve_files')->insertGetId([
                    "file_number" => $_nrofile,
                    "type" => '',
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);
                foreach ( $servs as $serv ){
                    DB::table('extension_generic_ota_services')->where('id', $serv['id'])
                        ->update([
                            'status' => 0
                        ]);
                    DB::table('extension_generic_ota_reserves')->insert([
                        "reserve_file_id" => $reserve_file_id,
                        "generic_ota_service_id" => $serv['id'],
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);

                    $update_central = CentralBooking::where('model', 'App\ExtensionGenericOtaService')
                        ->where('object_id', $serv['id'])
                        ->first();
                    $update_central->status = 0;
                    $update_central->file_number = $_nrofile;
                    $update_central->save();
                }
                $response = [
                    'data' => $response, // $call
                    'detail' => $_nrofile,
                    'success' => true
                ];
            } else {
                $response = [
                    'data' => $response, // $call
                    'detail' => 'Error en el web service de creación',
                    'success' => false
                ];
            }

        } else{
            $response = [
                'detail' => $json_params['response'], // $call(int)$call['files'][0]
                'success' => false
            ];
        }

        return Response::json($response);

    }

    private function do_params($servs, $nrofile, $codcli){

        $r = 'ok';

        if( $nrofile == '' ){
            $nrofile = 0;
        }
        $paxdes = $servs[0]['paxdes'];
        $fecini = $servs[0]['start_date'];

        $canadu = 0;
        $canchd = 0;
        $caninf = 0;

        $import = 0;

        foreach ( $servs as $serv ){
            if( $this->parseDateReserve( $serv['start_date'], $fecini ) ){
                $fecini = $serv['start_date'];
            }
            if( (int)$serv['canadu'] > $canadu ){
                $canadu = (int)$serv['canadu'];
            }
            if( (int)$serv['canchd'] > $canchd ){
                $canchd = (int)$serv['canchd'];
            }
            if( (int)$serv['caninf'] > $caninf ){
                $caninf = (int)$serv['caninf'];
            }
        }

        $buffer = [
            'datapla' => [
                'fecini' => $fecini,
                'codcli' => $codcli,
                'descri' => $paxdes,
                'observ' => (string)$import,
                'canadu' => $canadu,
                'canchd' => $canchd,
                'caninf' => $caninf,
                'import' => $import,
                'nroref' => $nrofile,
                'operad' => Auth::user()->code,
                'refext' => "",
                'viene' => $servs[0]["quote"],
            ],
            'datasvs' => [],
            'datahtl' => [],
            'datapax' => [],
        ];

        $nrolin = 0;

        if( $r == 'ok' ) {

            for( $s=0; $s < count( $servs ); $s++ ){
                $nrolin++;

                $paxServ = (int)$servs[$s]['canadu'];
                array_push($buffer["datasvs"], [
                    "nrolin" => $nrolin,
                    "fecha" => $servs[$s]['start_date'],
                    "codigo" => $servs[$s]['code'],
                    "canpax" => $paxServ,
                    "infoad" => "",
                ]);

            }
        }

        if( $r == 'ok' ){

            for( $p=0; $p < (int)$canadu; $p++ ){
                array_push($buffer["datapax"], [
                    "nombre" => $paxdes,
                    "genero" => "",
                    "edad" => "",
                    "nac" => "",
                    "tipdoc" => "",
                    "nrodoc" => ""
                ]);
            }

        }

        if( $r == 'ok' ){
            return array( 'status' => $r, 'response' => $buffer );
        } else {
            return array( 'status' => 'error', 'response' => $r );
        }
    }

    public function parseDateReserve($date){
        $explode = explode("-", $date);
        $response = $explode[2] . '/' . $explode[1] . '/' . $explode[0];
        return $response;
    }


    public function report(Request $request)
    {
        try
        {
            $date_from = Carbon::createFromFormat('d/m/Y', $request->post('date_from'))->format('Y-m-d');
            $date_to = Carbon::createFromFormat('d/m/Y', $request->post('date_to'))->format('Y-m-d');


            $total_reserves = CentralBooking::whereBetween('start_date', [$date_from, $date_to])
                ->where('type_service', '<>', 'Canceled')->get()->count();

            if ($total_reserves > 0){

                $total_reserve_with_files =  CentralBooking::with(['file' => function ($query) use ($date_to) {
                    $query->where('created_at', '<=', $date_to);
                }])
                ->whereHas('file', function ($query) use ($date_to) {
                    $query->where('created_at', '<=', $date_to);
                })
                    ->whereBetween('start_date', [$date_from, $date_to])
                    ->where('type_service', '<>', 'Canceled')
                    ->where('file_number','!=',null)->get()->count();

                $reserve_no_files =  CentralBooking::with('file')->whereBetween('start_date', [$date_from, $date_to])
                    ->where('type_service', '<>', 'Canceled')
                    ->where(function ($query) use ($date_to) {
                        $query->orWhere('file_number', '=', NULL);
                        $query->orWhereHas('file', function ($q) use ($date_to) {
                            $q->select('file_number', 'created_at');
                            $q->where('created_at', '>=', $date_to);
                        });
                    })
                    //->where('file_number','=',null)
                    ->get();

                $total_reserve_no_files =  $reserve_no_files->count();

                $percentage_total_reserve_with_files = $total_reserve_with_files * 100 / $total_reserves;

                $percentage_total_reserve_no_files = $total_reserve_no_files * 100 / $total_reserves;
            }else{
                $total_reserve_with_files = 0;
                $total_reserve_no_files = 0;
                $percentage_total_reserve_with_files = 0;
                $percentage_total_reserve_no_files = 0;
                $reserve_no_files = [];
            }

            $data = [
                'type' => 'success',
                "total_files"=>$total_reserves,
                "total_reserve_with_files"=>$total_reserve_with_files,
                "percentage_total_reserve_with_files"=>$percentage_total_reserve_with_files,
                "total_reserve_no_files"=>$total_reserve_no_files,
                "percentage_total_reserve_no_files"=>$percentage_total_reserve_no_files,
                "reserve_no_files"=>$reserve_no_files,
            ];

            return \response()->json($data);
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function saveFileReservationFromPackage(Request $request){

        $file_code = $request->post('file_code');
        $generic_service_id = $request->post('generic_service_id');


        $generic_ota_service = ExtensionGenericOtaService::find($generic_service_id);
        $generic_ota_service->booking_code = $file_code;
        $generic_ota_service->status = 0;
        $generic_ota_service->save();

        $central_booking = CentralBooking::where('model','App\ExtensionGenericOtaService')->where('object_id',$generic_service_id)->first();
        $central_booking->file_number = $file_code;
        $central_booking->save();

        $reserve_file =  new ReserveFile();
        $reserve_file->file_number = $file_code;
        $reserve_file->type = $central_booking->channel_name;
        $reserve_file->save();

        $generic_ota_reserve = new ExtensionGenericOtaReserves();
        $generic_ota_reserve->reserve_file_id= $reserve_file->id;
        $generic_ota_reserve->generic_ota_service_id= $generic_service_id;
        $generic_ota_reserve->save();

        return \response()->json("file registrado");
    }
}

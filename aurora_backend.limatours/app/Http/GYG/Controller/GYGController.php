<?php

namespace App\Http\GYG\Controller;

use App\CentralBooking;
use App\ExtensionGYGHeader;
use App\ExtensionGYGService;
use App\Http\Controllers\Controller;
use App\Ota;
use App\Service;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use App\Http\Traits\Aurora3;
use DateTime;

class GYGController extends Controller
{
    use Aurora3;

    public function booking(Request $request)
    {
        try
        {
            $data_feccre = $request->input('feccre');
            $data_horcre = $request->input('horcre');
            $data_user = $request->input('user');
            $data_state = $request->input('state') ?? 'N';

            $ota = Ota::select('id','name')->where('name', '=','Get your guide')->first();

            $data = $request->input('data');

            $codeXML = trim( str_replace( "/", "", $data_feccre ) ) . trim( str_replace( ":", "", $data_horcre ) ) . trim($data['reference']);

            $findCodeXML = ExtensionGYGHeader::where( 'code', $codeXML );
            $arrayServs = [];

            if( $findCodeXML->count() === 0)
            {
                $dateTime = new DateTime($data['date']);
                $date = $dateTime->format("Y-m-d"); $time = $dateTime->format('H:i:s');
                $_date =  $date;
                $dateServ = convertDate( $_date, '-', '/', 1 );

                $arrayServs[] = array(
                    "dateHead" => $dateServ,
                    "ticketType" => '',
                    "tipser" => 'P',
                    "pax" => $data['paxs'],
                    "aurora_code" => '',
                    "boonum" => $data['reference'],
                    "primaryRedeemer" => array(
                        "name" => $data['customer_name'],
                        "number" => $data['customer_phone'],
                        "mail" => $data['customer_email']
                    ),
                    "item" => $data['tour_title'],
                    "detail" => $data['option_title'],
                );

                $new_header = new ExtensionGYGHeader();
                $new_header->code = $codeXML;
                $new_header->email = $data_user;
                $new_header->date_created = convertDate( $data_feccre, '/', '-', 1 ) . ' ' . $data_horcre;
                $new_header->status = 1;
                $new_header->save();

                $errS = 0;

                for ( $s=0; $s<count( $arrayServs ); $s++ ) {

                    $_desnom = $arrayServs[$s]["primaryRedeemer"]["name"];

                    $aurora_codes = explode('/', $arrayServs[$s]['aurora_code']);
                    foreach ( $aurora_codes as $aurora_code ){

                        $count_svs = ExtensionGYGService::where('code', '=', $arrayServs[$s]["boonum"])->count();

                        if($count_svs == 0)
                        {
                            $new_service = new ExtensionGYGService();
                            $new_service->extension_gyg_header_id = $new_header->id;
                            $new_service->code = $arrayServs[$s]["boonum"];
                            $new_service->date_start = convertDate( $arrayServs[$s]["dateHead"], '/', '-', 1 );
                            $new_service->paxs = $arrayServs[$s]["pax"];
                            $new_service->passenger = $_desnom;
                            $new_service->passenger_email = $arrayServs[$s]["primaryRedeemer"]["mail"];
                            $new_service->status = 1;
                            $new_service->booking_state = $data_state;
                            $new_service->type = $arrayServs[$s]["tipser"];
                            $new_service->name = $arrayServs[$s]["item"];
                            $new_service->detail = $arrayServs[$s]["detail"];
                            $new_service->ticket_type = $arrayServs[$s]["ticketType"];
                            $new_service->aurora_code = strtoupper($aurora_code);

                            if( !($new_service->save()) ){
                                $errS++;
                            }

                            // -------- GENERAL TABLE UNIFIED ----------------
                            $tags_gyg =
                                json_encode([
                                    "passenger_email" => $arrayServs[$s]["primaryRedeemer"]["mail"],
                                    "booking_state" => $data_state,
                                    "name" => $arrayServs[$s]["item"],
                                    "booking_code" => null,
                                    "header_code" => $codeXML,
                                    "header_email" => $data_user,
                                ]);
                            DB::table('central_bookings')->insert([
                                "ota_id"=>$ota->id,
                                "channel_name" => $ota->name,
                                "model" => 'App\ExtensionGYGService',
                                "object_id" => $new_service->id,
                                "code" => $new_service->code,
                                "tags" => $tags_gyg,
                                "status" => 1,
                                "made_date_time" => $new_header->date_created,
                                "start_date" => $new_service->date_start,
                                "end_date" => null,
                                "description" => $new_service->name,
                                "passenger" => $new_service->passenger,
                                "agent" => null,
                                "file_number" => null,
                                "aurora_code" => $new_service->aurora_code,
                                "type_service" => $new_service->type,
                                "quantity_pax" => $new_service->paxs,
                                "created_at" => Carbon::now(),
                                "updated_at" => Carbon::now()
                            ]);
                        }
                        // -------- GENERAL TABLE UNIFIED ----------------
                    }
                }

                if ($errS == 0) {
                    $users = [];
                    $permission =
                        DB::table("permissions")->where('slug',"reservationcentertourcms.create")->first();
                    if( $permission ){
                        $permission_role_ids =
                            DB::table("permission_role")->where('permission_id',$permission->id)->pluck('role_id');
                        $role_user_ids = DB::table('role_user')->whereIn('role_id',$permission_role_ids)->pluck('user_id');
                        $users = User::whereIn('id',$role_user_ids)->get();
                    }
                    foreach ( $users as $user ){
                        $pushNoti = (object)[
                            'user' => $user->code,
                            'title' => "Get Your Guide - Nueva reserva",
                            'body' => 'Ha llegado una nueva reserva a la Aurora 2 desde el correo de Get Your Guide. Toque este mensaje para revisarlo.',
                            'click_action' => 'https://aurora.limatours.com.pe/central_bookings/get-your-guide'];
                        $this->sendPushNotification($pushNoti);
                    }

                    $response = array('response' => "OK", 'detail' => 'Guardado correctamente');
                } else {
                    $response = array('response' => "ERR", 'detail' => 'Error de Sistema al guardar servicios');
                }

            } else {
                $response = array( 'response' => "ERR",
                    'detail' => 'Ya fue registrado anteriormente.' );
            }

            return json_encode([$response]);
        }
        catch(\Exception $ex)
        {
            return json_encode([$this->throwError($ex)]);
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

        $reserve_passed = $request->input('reserve_passed');

        $list = ExtensionGYGService::with('header','reserves.file');

        if ($querySearch) {
            $list->where(function ($query) use ($querySearch) {
                $query->orWhere('code', 'like', '%' . $querySearch . '%');
                $query->orWhere('passenger', 'like', '%' . $querySearch . '%');
                $query->orWhere('passenger_email', 'like', '%' . $querySearch . '%');
                $query->orWhere('date_start', 'like', '%' . $querySearch . '%');
            });
        }

        if ($filterBy == 0) {
            $list->where('status', 1);
        }

        if ($reserve_passed == 0){
            $list->where('date_start','>=',Carbon::now());
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

        $list = $list->filter(function ($item, $i) {
            $item['code'] = strip_tags($item['code']);
            $item['passenger'] = strip_tags($item['passenger']);
            $item['email'] = strip_tags($item['email']);
            $item['passenger_email'] = strip_tags($item['passenger_email']);
            return $item;
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
        $package = ExtensionGYGService::find($id);
        $update_central = CentralBooking::where('model', 'App\ExtensionGYGService')
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
        $package = ExtensionGYGService::find($id);
        /*
        $update_central = CentralBooking::where('model', 'App\ExtensionExpediaService')
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

    public function updateService($id, Request $request)
    {
        $package = ExtensionGYGService::find($id);
        /*
        $update_central = CentralBooking::where('model', 'App\ExtensionExpediaService')
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

        $package->aurora_code = strtoupper($request->__get('code'));
        $package->save();
        // $update_central->save();

        return Response::json(['success' => true]);
    }

    public function reserve(Request $request){

        $servs = $request->input('services');
        $nrofile = $request->input('nrofile');

        $json_params = $this->do_params( $servs, $nrofile );
//        return json_encode( $json_params['response'] );

        if( $json_params['status'] == 'ok' ){
            $client = new \GuzzleHttp\Client();
            $params = [
                'datapla' => $json_params['response']['datapla'],
                'datasvs' => $json_params['response']['datasvs'],
                'datahtl' => $json_params['response']['datahtl'],
                'datapax' => $json_params['response']['datapax'],
            ];

            $response = $client->request('POST',
                config('services.stella.domain'). 'api/v1/stelaFiles/savemeridiam', [
                    "json" => $params
                ]);

            $response = json_decode($response->getBody()->getContents());

            if( $response->success && $response->process ){
                // Create File A3..
                $this->createFileOTSA3($json_params['response'], $json_params['response']['datapla']['operad']);

                $_nrofile = $response->data->nroref;

                $reserve_file_id = DB::table('reserve_files')->insertGetId([
                    "file_number" => $_nrofile,
                    "type" => 'gyg',
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);
                foreach ( $servs as $serv ){
                    DB::table('extension_gyg_services')->where('id', $serv['id'])
                        ->update([
                            'status' => 0
                        ]);
                    DB::table('extension_gyg_reserves')->insert([
                        "reserve_file_id" => $reserve_file_id,
                        "gyg_service_id" => $serv['id'],
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);

                    $update_central = CentralBooking::where('model', 'App\ExtensionGYGService')
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

    private function do_params($servs, $nrofile){

        $r = 'ok';

        if( $nrofile == '' ){
            $nrofile = 0;
        }
        $paxdes = $servs[0]['passenger'];
        $fecini = $servs[0]['date_start'];

        $canadu = 0;
        $canchd = 0;
        $caninf = 0;

        $import = 0;

        foreach ( $servs as $serv ){
            $fecini = $this->parseDateReserve( $fecini );
            $canadu = (int)$serv['paxs'];
        }

        $buffer = [
            'datapla' => [
                'fecini' => $fecini,
                'codcli' => "9GETYG",
                'descri' => $paxdes,
                'observ' => (string)$import,
                'canadu' => $canadu,
                'canchd' => $canchd,
                'caninf' => $caninf,
                'import' => $import,
                'nroref' => $nrofile,
                'operad' => Auth::user()->code,
                'refext' => "",
                'viene' => "BREDOW",
            ],
            'datasvs' => [],
            'datahtl' => [],
            'datapax' => [],
        ];

        $nrolin = 0;

        if( $r == 'ok' ) {

            for( $s=0; $s < count( $servs ); $s++ ){
                $nrolin++;

                $paxServ = (int)$servs[$s]['paxs'];
                array_push($buffer["datasvs"], [
                    "nrolin" => $nrolin,
                    "fecha" => $this->parseDateReserve($servs[$s]['date_start']),
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
        $explode = explode("/", $date);

        if(count($explode) === 3)
        {
            $date = $explode[2] . '-' . $explode[1] . '-' . $explode[0];
        }

        return $date;
    }
}

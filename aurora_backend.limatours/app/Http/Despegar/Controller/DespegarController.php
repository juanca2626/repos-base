<?php

namespace App\Http\Despegar\Controller;

use App\CentralBooking;
use App\ExtensionDespegarHeader;
use App\ExtensionDespegarHomologation;
use App\ExtensionDespegarService;
use App\Http\Controllers\Controller;
use App\Ota;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use App\Http\Traits\Aurora3;

class DespegarController extends Controller
{
    use Aurora3;
    // https://backend.limatours.com.pe/api/channel/despegar/booking

    public function booking(Request $request)
    {
        try
        {
            $data = $request->input('data');

    //        $data = [
    //            [ "title"=>"Titular de la reserva",
    //              "text"=>"Rosario Jhoselin Rojas Tuiro, Contactar pasajero *"],
    //            [ "title"=>null,
    //              "text"=>null],
    //            [ "title"=>"Teléfono",
    //                "text"=>"51(01)913089796"],
    //            ["title"=>"Pasajeros",
    //                "text"=>"Adultos: 2 | Menores: 0 | Infantes: 0"],
    //            ["title"=>null,
    //                "text"=>null],
    //            ["title"=>"Service id",
    //                "text"=>"2565"],
    //            ["title"=>"Fecha de reserva",
    //                "text"=>"03/03/2021 - 00:38"],
    //            ["title"=>"Código de reserva PAM",
    //                "text"=>"3321498"],
    //            ["title"=>"ID Compra Despegar",
    //                "text"=>"5007558308"],
    //            ["title"=>"Lugar de compra",
    //                "text"=>"Perú"],
    //            ["title"=>"Canal","text"=>"Despegar"],
    //            ["title"=>null,"text"=>null],
    //            ["title"=>"Producto comprado","text"=>"Almuerzo buffet en el restaurante Mangos en Larcomar"],
    //            ["title"=>"Temporada","text"=>"LIM8MG - Almuerzo buffet en Mangos 2021/2023"],
    //            ["title"=>null,"text"=>null],
    //            ["title"=>"Fecha de la excursión","text"=>"05/03/2021"],["title"=>null]];

            $data = json_encode( $data );

            $dataExtract = json_decode( $data );
    //        return $dataExtract;
            $pamcode = ''; $boonum = ''; $response = [];

            foreach( $dataExtract as $dato ){
                if( $dato->title == 'Código de reserva PAM' ){
                    $pamcode = $this->cleanHTML($dato->text);
                }
                if( $dato->title == 'ID Compra Despegar' ){
                    $boonum = $this->cleanHTML($dato->text);
                }
            }

            if( $pamcode != '' && $boonum != '' ){

                $findPam = ExtensionDespegarHeader::where( 'code', $pamcode );
                if( $findPam->count() == 0 ) {

                    $bootyp = ''; $fechaR = ''; $horaR = ''; $dias = ''; $fromDate = ''; $toDate = ''; $fromHour = '';
                    $adl = ''; $chd = ''; $inf = ''; $correo = ''; $nombre = ''; $servki = ''; $servde = ''; $rosety = '';
                    $aurora_code = '';
                    $modcod = ''; $moddes = ''; $modalidad = '';
                    $hotel = ''; $vuelo = '';

                    foreach( $dataExtract as $dato ){

                        if( isset( $dato->text ) ){
                            $dato->text = $this->cleanHTML($dato->text);
                            $dato->text = str_replace( '<span class="il">', "", $dato->text );
                            $dato->text = str_replace( '</span>', "", $dato->text );
                            $dato->text = str_replace( '<SPAN CLASS="IL">', "", $dato->text );
                            $dato->text = str_replace( '</SPAN>', "", $dato->text );
                        }

                        if( $dato->title == 'Lugar de compra' ){
                            $bootyp = $this->cleanHTML($dato->text);
                        }
                        if( $dato->title == 'Fecha de reserva' ){
                            $dato->text = $this->cleanHTML($dato->text);
                            $extract = explode( "-", $dato->text );
                            $fechaR = trim( $extract[0] );
                            $horaR = trim( $extract[1] );
                        }
                        if( $dato->title == 'Producto comprado' ){
                            $servde = $dato->text;
                            $dias = explode( "-", $dato->text );
                            if( count( $dias ) >= 2 ){
                                $dias = explode( "Días", $dias[1] );
                                $dias = (int)(trim( $dias[0] ));
                                if( $dias <= 1 ){
                                    $dias = 1;
                                }
                            } else {
                                $dias = 1;
                            }
                        }
                        if( $dato->title == 'Modalidad' ){
                            $modalidad = $this->cleanHTML($dato->text);
                        }
                        if( $dato->title == 'Fecha de la excursión' || $dato->title == 'Fecha y hora del vuelo'
                            || $dato->title == 'Fecha y hora del traslado' ){
                                if( $dato->title == 'Fecha de la excursión' ){
                                    $servki = 'X';
                                    $modcod = '';
                                    $fromDate = $dato->text;
                                    $fromHour = '';
                                } else {
                                        $servki = 'T';
                                        $modcod = 'PRIVATE';
                                        $extract = explode( "-", $dato->text );
                                        $fromDate = $extract[0];
                                        $fromHour = $extract[1];
                                }
                                if( $dias > 1 ){ // Si en "Producto comprado vienen Días"
                                    $dayToSum = $dias - 1;
                                    $dateToSum = convertDate( $fromDate, '/', '/', 1 );
                                    $toDate = date("d/m/Y", strtotime("$dateToSum $dayToSum days") );
                                } else {
                                    $toDate = $fromDate;
                                }
                        }
                        if( $dato->title == 'Pasajeros' ){ //"Adultos: 2 | Menores: 0 | Infantes: 0"
                            $extract = explode( "|", $dato->text );
                            $adl = explode( ":", $extract[0] );
                            $adl = trim( $adl[1] );

                            if(isset($extract[1]))
                            {
                                $chd = explode( ":", $extract[1] );
                                $chd = trim( $chd[1] );
                            }

                            if(isset($extract[2]))
                            {
                                $inf = explode( ":", $extract[2] );
                                $inf = trim( $inf[1] );
                            }
                        }
                        if( $dato->title == 'Titular de la reserva' ){
                            $dato->text = $this->cleanHTML($dato->text);
                            $extract = explode( ",", $dato->text );
                            $nombre = strtoupper( trim( $extract[0] ) );
                            $nombre = str_replace( '<span class="il">', '', $nombre );
                            $nombre = str_replace( '</span>', '', $nombre );
                            $correo = $this->cleanHTML( $extract[1] );
                        }
                        if( $dato->title == 'Temporada' || $dato->title == 'Tipo de servicio' ){
                            $rosety = $this->cleanHTML($dato->text);
                            $aurora_code = explode( "-", $rosety );
                            $aurora_code = trim( $aurora_code[0] );
                        }
                        if( $dato->title == 'Tipo de servicio' ){
                            $moddes = $dato->text;
                        }
                        if( $dato->title == 'Hotel' ){
                            $hotel = $dato->text;
                        }
                        if( $dato->title == 'Vuelo' ){
                            $vuelo = $dato->text;
                        }
                    }

                    $new_header = new ExtensionDespegarHeader();
                    $new_header->code = $pamcode;
                    $new_header->email = $request->input('user');
                    $new_header->date_created = convertDate( $request->input('feccre'), '/', '-', 1 ) . ' ' . $request->input('horcre');
                    $new_header->status = 1;
                    $new_header->save();

                    $count_svs = ExtensionDespegarService::where('code', '=', $boonum)->count();

                    if($count_svs == 0)
                    {
                        $new_service = new ExtensionDespegarService();
                        $new_service->extension_despegar_header_id = $new_header->id;
                        $new_service->code = $boonum;
                        $new_service->detail = $bootyp;
                        $new_service->date_from = convertDate( $fromDate, '/', '-', 1 );
                        $new_service->date_to = convertDate( $toDate, '/', '-', 1 );
                        $new_service->adults = $adl;
                        $new_service->children = $chd;
                        $new_service->infants = $inf;
                        $new_service->passenger = $nombre;
                        $new_service->status = 1;
                        $new_service->type = $servki;
                        $new_service->name = $servde;
                        $new_service->description = $rosety;
                        $new_service->aurora_code = $aurora_code;
                        $new_service->modality = $modalidad;

                        if( $new_service->save() ){

                            // -------- GENERAL TABLE UNIFIED ----------------
                            $tags_despegar =
                                json_encode([
                                    "name" => $new_service->name,
                                    "header_code" => $new_header->code,
                                    "header_email" => $new_header->email,
                                ]);
                            $ota = Ota::select('id','name')->where('name','despegar')->first();
                            DB::table('central_bookings')->insert([
                                "ota_id"=>$ota->id,
                                "channel_name" => $ota->name,
                                "model" => 'App\ExtensionDespegarService',
                                "object_id" => $new_service->id,
                                "code" => $new_service->code,
                                "tags" => $tags_despegar,
                                "status" => $new_service->status,
                                "made_date_time" => $new_header->date_created,
                                "start_date" => $new_service->date_from,
                                "end_date" => $new_service->date_to,
                                "description" => $new_service->description,
                                "passenger" => $new_service->passenger,
                                "agent" => $new_service->detail,
                                "file_number" => null,
                                "aurora_code" => $new_service->aurora_code,
                                "type_service" => $new_service->type,
                                "quantity_pax" => $new_service->adults,
                                "created_at" => Carbon::now(),
                                "updated_at" => Carbon::now()
                            ]);
                            // -------- GENERAL TABLE UNIFIED ----------------

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
                                    'title' => "Despegar - Nueva reserva",
                                    'body' => 'Ha llegado una nueva reserva a la Aurora 2 desde el correo de Despegar. Toque este mensaje para revisarlo.',
                                    'click_action' => 'https://aurora.limatours.com.pe/central_bookings/despegar'];
                                $this->sendPushNotification($pushNoti);
                            }

                            $response = array( 'response' => "OK", 'detail' => 'Guardado correctamente' );

                        } else {
                            $response = array( 'response' => "ERR", 'detail' => 'Error de Sistema al guardar el Servicio' );
                        }
                    } else {
                        $findPam = $findPam->first();
                        $response = array( 'response' => "ERR",
                                    'detail' => 'El codigo PAM ( ' . $findPam->code . '), ya fue registrado por: ' .
                                        $findPam->email );
                    }

                } else {
                    $findPam = $findPam->first();
                    $response = array( 'response' => "ERR",
                                    'detail' => 'El codigo PAM ( ' . $findPam->code . '), ya fue registrado por: ' .
                                        $findPam->email );
                }
            } else {
                $response = array( 'response' => "ERR",
                                'detail' => 'Error al extraer información, contactese con el administrador.' );
            }

            return json_encode([$response]);
        }
        catch(\Exception $ex)
        {
            return json_encode([$this->throwError($ex)]);
        }
    }

    public function cleanHTML($html) {
        $clean = strip_tags($html); // Quita las etiquetas HTML
        $clean = html_entity_decode($clean); // Decodifica entidades HTML
        // Opcional: limpiar espacios innecesarios o caracteres no deseados
        $clean = trim($clean); // Elimina espacios al inicio y fin
        return $clean;
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

        $list = ExtensionDespegarService::with('header','reserves.file');

        if ($querySearch) {
            $list->where(function ($query) use ($querySearch) {
                $query->orWhere('code', 'like', '%' . $querySearch . '%');
                $query->orWhere('passenger', 'like', '%' . $querySearch . '%');
                $query->orWhere('detail', 'like', '%' . $querySearch . '%');
                $query->orWhere('name', 'like', '%' . $querySearch . '%');
                $query->orWhere('description', 'like', '%' . $querySearch . '%');
                $query->orWhere('date_from', 'like', '%' . $querySearch . '%');

            });
        }

        if ($filterBy == 0) {
            $list->where('status', 1);
        }

        if ($reserve_passed == 0){
            $list->where('date_from','>=',Carbon::now());
        }

        $count = $list->count();

        if( $filter != "undefined" && $filter != null ){
            $_order = ( $order == 'true' ) ? 'desc' : 'asc';
            $list = $list
                ->orderBy($filter, $_order);
        } else {
            $list = $list->orderBy('date_from','asc');
        }

        if ($paging === 1) {
            $list = $list->take($limit)->get();
        } else {
            $list = $list->skip($limit * ($paging - 1))->take($limit)->get();
        }

        $list = $list->filter(function ($item, $i) {
            $item['code'] = $this->cleanHTML($item['code']);
            $item['passenger'] = $this->cleanHTML($item['passenger']);
            $item['email'] = $this->cleanHTML($item['email']);
            $item['passenger_email'] = $this->cleanHTML($item['passenger_email']);
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
        $package = ExtensionDespegarService::find($id);
        $update_central = CentralBooking::where('model', 'App\ExtensionDespegarService')
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
        $package = ExtensionDespegarService::find($id);

        /*
        $update_central = CentralBooking::where('model', 'App\ExtensionDespegarService')
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
        $json_params = $this->do_params( $servs, $nrofile );

//        return json_encode( $json_params['response'] );

        if( $json_params['status'] == 'ok' ){

            $client = new \GuzzleHttp\Client(["verify"=>false]);
            $response = $client->request('POST',
                config('services.stella.domain'). 'api/v1/stelaFiles/savemeridiam', [
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
                    "type" => 'despegar',
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);
                foreach ( $servs as $serv ){
                    DB::table('extension_despegar_services')->where('id', $serv['id'])
                        ->update([
                            'status' => 0
                        ]);
                    DB::table('extension_despegar_reserves')->insert([
                        "reserve_file_id" => $reserve_file_id,
                        "despegar_service_id" => $serv['id'],
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);
                    $update_central = CentralBooking::where('model', 'App\ExtensionDespegarService')
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
                'codcli' => "5DESPE",
                'descri' => $paxdes,
                'observ' => (string)$import,
                'canadu' => $canadu,
                'canchd' => $canchd,
                'caninf' => $caninf,
                'import' => $import,
                'nroref' => $nrofile,
                'operad' => Auth::user()->code,
                'refext' => "",
                'viene' => ($servs[0]['code']) ? 'BREDOW' : "DESPEG",
            ],
            'datasvs' => [],
            'datahtl' => [],
            'datapax' => [],
        ];

        $nrolin = 0;

        if( $r == 'ok' ) {

            for( $s=0; $s < count( $servs ); $s++ ){
                $nrolin++;
                $internal_code = '';

                if( $servs[$s]['code'] ){
                    $internal_code = $servs[$s]['code'];
                } else {
                    $service = ExtensionDespegarService::find($servs[$s]['id']);
                    $find_homologation = ExtensionDespegarHomologation::where('description',$service->name)
                                            ->orWhere('description',$service->modality)->first();
                    if( isset($find_homologation->id) ){
                        $internal_code = $find_homologation->internal_code;
                    } else {
                        $r = 'Equivalencia del servicio: "' . $service->name . '" no encontrada.';
                        break;
                    }
                }

                $paxServ = (int)$servs[$s]['canadu'];
                array_push($buffer["datasvs"], [
                    "nrolin" => $nrolin,
                    "fecha" => $servs[$s]['start_date'],
                    "codigo" => $internal_code,
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
//    public function reserve_backup(Request $request){
//
//        $servs = $request->input('services');
//        $nrofile = $request->input('nrofile');
//        $xml = $this->doXML( $servs, $nrofile );
//
//        if( $xml['status'] == 'ok' ){
//
////            url del webservice
////            $wsdl = "http://genero.limatours.com.pe:8099/AllotWebMeridi?wsdl";
//            $wsdl = "http://genero.limatours.com.pe:8206/WS_AllotWebMeridi?wsdl";
//            //instanciando un nuevo objeto cliente para consumir el webservice
//            // new SoapClient("some.wsdl", array('soap_version'   => SOAP_1_2));
//            $client=new \SoapClient($wsdl,[
//                'encoding'=>'UTF-8',
//                'trace'=>true,
//                'soap_version'=>SOAP_1_1,
//                'exceptions'=>true
//            ]);
//
//            //pasando los parámetros a un array
//            $filter=array(
//                'dato'=>$xml['response'],
//                'file'=>"LITO00".date('Ymdhis').".xml",
//                'subdirs'=>false
//            );
//
////            var_export( $filter ); die;
//
//            //llamando al método y pasándole el array con los parámetros
//            $result = $client->__call('FileSearch', $filter);
//
//            if( $result['estado'] == "1" ){
//                $_nrofile = (int)$result['files'][0];
//
//                $reserve_file_id = DB::table('reserve_files')->insertGetId([
//                    "file_number" => $_nrofile,
//                    "type" => 'despegar',
//                    "created_at" => Carbon::now(),
//                    "updated_at" => Carbon::now()
//                ]);
//                foreach ( $servs as $serv ){
//                    DB::table('extension_despegar_services')->where('id', $serv['id'])
//                        ->update([
//                            'status' => 0
//                        ]);
//                    DB::table('extension_despegar_reserves')->insert([
//                        "reserve_file_id" => $reserve_file_id,
//                        "despegar_service_id" => $serv['id'],
//                        "created_at" => Carbon::now(),
//                        "updated_at" => Carbon::now()
//                    ]);
//                }
//                $response = [
//                    'data' => $result, // $call
//                    'detail' => $_nrofile,
//                    'success' => true
//                ];
//            } else {
//                $response = [
//                    'data' => $result, // $call
//                    'detail' => 'Error en el web service de creación',
//                    'success' => false
//                ];
//            }
//
//        } else{
//            $response = [
//                'detail' => $xml['response'], // $call(int)$call['files'][0]
//                'success' => false
//            ];
//        }
//
//        return Response::json($response);
//    }

    private function doXML($servs, $nrofile){

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

        $buffer = "<?xml version=\"1.0\" encoding=\"ANSI_X3.4-1968\"?>
<!-- Comentario -->
<!-- tiphab=\"1\" Simple -->
<!-- tiphab=\"2\" Doble  -->
<!-- tiphab=\"3\" Triple -->

<!-- descri=\"\" equivale referencia pax -->
<!-- observ=\"\" equivale observaciones -->
<Planilla>
<PllDatos fecini=\"".$this->parseDateReserve( $fecini )."\" />
<PllDatos codcli=\"5DESPE\" />
<PllDatos descri=\"".$paxdes."\" />
<PllDatos observ=\"".$import."\" />
<PllDatos canadu=\"".$canadu."\" />
<PllDatos canchd=\"".$canchd."\" />
<PllDatos caninf=\"".$caninf."\" />
<PllDatos import=\"".$import."\" />
<PllDatos operad=\"".Auth::user()->code."\" />
<PllDatos refext=\"".""."\" />
<PllDatos nroref=\"".$nrofile."\" />
<PllDatos viene=\"DESPEG\" />
</Planilla>";

        $nrolin = 0;

        if( $r == 'ok' ) {

            for( $s=0; $s < count( $servs ); $s++ ){
                $nrolin++;

                $service = ExtensionDespegarService::find($servs[$s]['id']);
                $find_homologation = ExtensionDespegarHomologation::where('description',$service->name)
                                        ->orWhere('description',$service->modality)->first();

                $internal_code = '';
                if( isset($find_homologation->id) ){
                    $internal_code = $find_homologation->internal_code;
                } else {
                    $r = 'Equivalencia del servicio: "' . $service->name . '" no encontrada.';
                    break;
                }

                $paxServ = (int)$servs[$s]['canadu'];
                $buffer.="
<Servicio>
<SerDatos nrolin=\"". $nrolin ."\" />
<SerDatos fecha=\"". $this->parseDateReserve( $servs[$s]['start_date'] ) ."\" />
<SerDatos codigo=\"". $internal_code ."\" />
<SerDatos canpax=\"". $paxServ ."\" />
<SerDatos infoad=\"". "" ."\" />
</Servicio>";

            }
        }

        if( $r == 'ok' ){

            $buffer.="
<DatosPax>";
            for( $p=0; $p < (int)$canadu; $p++ ){
                $buffer.="
<Pax>
    <PaxDatos nombre=\"". $paxdes ."\" />
    <PaxDatos genero=\"\" />
    <PaxDatos edad=\"\" />
    <PaxDatos nac=\"\" />
    <PaxDatos tipdoc=\"". "" ."\" />
    <PaxDatos nrodoc=\"". "" ."\" />
</Pax>";
            }

            $buffer.="
</DatosPax>";
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

    public function homologations(){

        $homologations = ExtensionDespegarHomologation::orderBy('created_at', 'desc')->get();

        $response = array(
            'success' => true,
            'data' => $homologations
        );
        return Response::json($response);
    }

    public function storeHomologation(Request $request)
    {
        $homologation = new ExtensionDespegarHomologation();
        $homologation->service_type = $request->input('service_type');
        $homologation->description = $request->input('description');
        $homologation->internal_code = $request->input('internal_code');

        return Response::json(['success' => $homologation->save()]);
    }
    public function destroyHomologation($id)
    {
        $homologation = ExtensionDespegarHomologation::find($id);

        return Response::json(['success' => $homologation->delete()]);
    }

}

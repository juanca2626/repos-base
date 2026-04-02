<?php

namespace App\Http\Expedia\Controller;

use App\CentralBooking;
use App\ExtensionExpediaHeader;
use App\ExtensionExpediaService;
use App\Http\Controllers\Controller;
use App\Ota;
use App\Service;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use App\Http\Traits\Aurora3;

class ExpediaController extends Controller
{
    use Aurora3;
    // https://backend.limatours.com.pe/api/channel/expedia/booking

    public function booking(Request $request)
    {
        try
        {
            $data_feccre = $request->input('feccre');
            $data_horcre = $request->input('horcre');
            $data_token = $request->input('token');
            $data_user = $request->input('user');
            $data_state = $request->input('state');

            $ota = Ota::select('id','name')->where('name','expedia')->first();



            $data = $request->input('data');
    //        $data = ['2021-08-19 ****<br><p>              Ticket Type: Vehicle: 1 (LIN1H2/LIN2H2)<br>          Lead Traveler Name: Jason Lowndes<br>      Traveler Phone: 16173833424<br>      Traveler Email: <a href="mailto:jlowndes12@gmail.com" target="_blank">jlowndes12@gmail.com</a><br>          Travel Date: Aug 19, 2021<br>      Product: Private Standard Car: Lima Airport (LIM) (435419)<br>      Option: Private Standard Car(LIM): Roundtrip for Hotels in Lima (435421)<br>              Voucher:  84170101<br>      Itinerary: 72141975757426<br>                  Pickup Location: LIM - Lima (LIM-Jorge Chavez Intl.) <br>                  Dropoff Location: Swiss, Via Central 150,Centro Empresarial Real, Lima<br>                  Journey: Roundtrip<br>              Arrival Airline: CM<br>              Arrival Flight Number: 131 <br>              Flight Arrival Date: Aug 19, 2021 <br>              Flight Arrival Time: 15:00 <br>              Departure Airline: CM<br>              Departure Flight Number: 760 <br>                  Flight Departure Date: Aug 23, 2021 <br>              Flight Departure Time: 13:30 <br></p><img src="https://ci3.googleusercontent.com/proxy/dgUMqHFcsjX_uhUStp9uoFeSCtOU5TXazKFYbGXIEy2fPsE_ghW1WZv3GBOhd1GiwG7YqvYUfVUPwQkfP1XY2pd3G3moh4jalC0sbj6AddBChDGu1_aC0ccKEb1uw7H_ASqkK8mnGpE-tpB9uXpkRdYLaDkcqlwMRY-r5hxU9om_=s0-d-e1-ft#https://notification.lxhub.com/v1/notification/pixelRead?id=4427876&amp;email=destinationservices@limatours.com.pe" width="1" height="1" border="0" class="CToWUd">'];
    //        $data = ['2021-05-09 ****<br><p>              Ticket Type: Adult: 1 (LIN476)<br>          Lead Traveler Name: David Roberts<br>      Traveler Phone: 4407928332293<br>      Traveler Email: <a href="mailto:millenniumdave@hotmail.com" target="_blank">millenniumdave@hotmail.com</a><br>          Travel Date: May 9, 2021<br>      Product: Pachacamac archaeological complex &amp; Barranco (636885)<br>      Option: 9:15 AM, Lima: Pachacamac ruins and Barranco half day tour in English (636921)<br>              Voucher:  82661630<br>      Itinerary: 7549269287743<br></p><img src="https://ci6.googleusercontent.com/proxy/3ia3CTI9LnmRsBegVdxHEc0u528KXTfLUBFwpWAEdvLzXjnq0uTLpRw-GUTK6l8wU1XljSmF3JO1-qhlTKo4IpnFNVzEkudJC4Rd7oiBXVsMi8nKHWJS6vXEb-WWCHWx2ugy93qseO76IyDxU_J5H7yMpY8iNyfWGY6L_c3NI-Np=s0-d-e1-ft#https://notification.lxhub.com/v1/notification/pixelRead?id=4156000&amp;email=destinationservices@limatours.com.pe" width="1" height="1" border="0" class="CToWUd"></div></div><div class="yj6qo"></div><div class="adL"></div>'];

            $data = json_encode( $data );
            $dataExtract = json_decode( $data );

            $servicesJoins = array();
            foreach( $dataExtract as $dato ){
                $servicesJoins[ count( $servicesJoins ) ] = $dato;
            }

            $fechoy = date("d/m/Y");
            $timnow = date("H:i");
            $codeXML = trim( str_replace( "/", "", $data_feccre ) ) . trim( str_replace( ":", "", $data_horcre ) ) .
                        $data_token;

            $findCodeXML = ExtensionExpediaHeader::where( 'code', $codeXML );

            if( $findCodeXML->count() == 0) {

                $servs = $dataExtract;
                $arrayServs = array();

                for( $i=0; $i<count( $servs ); $i++ ){
                    $_date = substr( $servs[$i], 0, 10 );
                    $dateServ = convertDate( $_date, '-', '/', 1 );
                    $explodeServs = explode("</p><p>", $servs[$i] );
                    for( $ii=0; $ii<count( $explodeServs ); $ii++ ){
                        if( count( explode( "For help, please do not reply to this email", $explodeServs[ $ii ] ) ) == 1 &&
                            count( explode( "IMPORTANT -- The traveler information", $explodeServs[ $ii ] ) ) == 1 ){
        /*
        * Ticket Type: Room: 1 ()<br>      
        * Primary Redeemer: James Finnigan, 13062626910, <a href="mailto:jtfinnigan@gmail.com" target="_blank">jtfinnigan@gmail.com</a><br>          
        * Valid Day: Sep 14, 2017<br>      
        * Item: 4-Day Magical Cusco / 4-Day Magical Cusco - Single Room - Standard Category / 473350<br>              
        * Voucher:  67811789, 1454848<br>      
        * Itinerary: 7288199959405<br></p>
        * */
                    $explodeBrsServs = explode( "<br>", $explodeServs[$ii] );
                    $ticketType = '';
                    $pax = 0;
                    $boonum = '';
                    $tipser = 'X';
                    $primaryRedeemerName = ''; $primaryRedeemerNumber = ''; $primaryRedeemerMail = '';
                    $itemText = '';
                    $aurora_code = '';
                    for ( $br=0; $br<count( $explodeBrsServs ); $br++ ){
                        // * Ticket Type: Room: 1 ()<br>
                        if( count( explode( "Ticket Type", $explodeBrsServs[$br] ) ) > 1 ){
                            $explodeTicket = explode( ":", $explodeBrsServs[$br] );
                            $ticketType = trim( $explodeTicket[1] );
                            if( $ticketType == 'Traveler' ){
                                $tipser = "T";
                            }
                            $explodePax = explode( "(", $explodeTicket[2] );
                            $pax = (int)$explodePax[0];

                            $aurora_code = explode( ")", $explodePax[1] );
                            $aurora_code = $aurora_code[0];
                        }

                        if( count( explode( "Lead Traveler Name", $explodeBrsServs[$br] ) ) > 1 ){
                            $explodeRedeemer = explode( "Lead Traveler Name:", $explodeBrsServs[$br] );
                            $primaryRedeemerName = $explodeRedeemer[1];
                        }

                        if( count( explode( "Traveler Phone", $explodeBrsServs[$br] ) ) > 1 ){
                            $explodeRedeemer = explode( "Traveler Phone:", $explodeBrsServs[$br] );
                            $primaryRedeemerNumber = $explodeRedeemer[1];
                        }

                        if( count( explode( "Traveler Email", $explodeBrsServs[$br] ) ) > 1 ){
                            $primaryRedeemerMail = explode( "Traveler Email:", $explodeBrsServs[$br] );

                            if(strpos($primaryRedeemerMail[1], "<br>") !== FALSE OR strpos($primaryRedeemerMail[1], "</a>") !== FALSE)
                            {
                                $primaryRedeemerMail = trim( str_replace( "<br>", "",
                                                            str_replace( "</a>",'',$primaryRedeemerMail[1] )
                                                        )
                                                    );
                                $primaryRedeemerMail = explode('>', $primaryRedeemerMail);
                            }

                            $primaryRedeemerMail = $primaryRedeemerMail[1];
                        }

                        //  * Voucher:  67811789, 1454848<br>
                        if( count( explode( "Voucher:", $explodeBrsServs[$br] ) ) > 1 ){
                            $explodeItem = explode( "Voucher:", $explodeBrsServs[$br] );
                            $itemText = str_replace( "<br>", "", $explodeItem[1] );
                            $explodeItemCode = explode( ",", $itemText );
                            $boonum = trim( $explodeItemCode[ 0 ] );
                        }
                    }
                        //  TIPSER = X - P - T
                        $arrayServs[ count( $arrayServs ) ] = array(
                            "dateHead" => $dateServ,
                            "ticketType" => $ticketType,
                            "tipser" => $tipser,
                            "pax" => $pax,
                            "aurora_code" => $aurora_code,
                            "boonum" => $boonum,
                            "primaryRedeemer" => array(
                                "name" => $primaryRedeemerName,
                                "number" => $primaryRedeemerNumber,
                                "mail" => $primaryRedeemerMail
                            ),
                            "item" => $itemText
                        );
                        }
                    }
                }

                $new_header = new ExtensionExpediaHeader();
                $new_header->code = $codeXML;
                $new_header->email = $data_user;
                $new_header->date_created = convertDate( $data_feccre, '/', '-', 1 ) . ' ' . $data_horcre;
                $new_header->status = 1;
                $new_header->save();

                $errS = 0;

                for ( $s=0; $s<count( $arrayServs ); $s++ ) {

                    $_desnom = str_replace( '<span class="il">', '', $arrayServs[$s]["primaryRedeemer"]["name"] );
                    $_desnom = str_replace( '</span>', '', $_desnom );

                    $aurora_codes = explode('/', $arrayServs[$s]['aurora_code']);
                    foreach ( $aurora_codes as $aurora_code ){

                        $count_svs = ExtensionExpediaService::where('code', '=', $arrayServs[$s]["boonum"])->count();

                        if($count_svs == 0)
                        {
                            $new_service = new ExtensionExpediaService();
                            $new_service->extension_expedia_header_id = $new_header->id;
                            $new_service->code = $arrayServs[$s]["boonum"];
                            $new_service->date_start = convertDate( $arrayServs[$s]["dateHead"], '/', '-', 1 );
                            $new_service->paxs = $arrayServs[$s]["pax"];
                            $new_service->passenger = $_desnom;
                            $new_service->passenger_email = $arrayServs[$s]["primaryRedeemer"]["mail"];
                            $new_service->status = 1;
                            $new_service->booking_state = $data_state;
                            $new_service->type = $arrayServs[$s]["tipser"];
                            $new_service->name = $arrayServs[$s]["item"];
                            $new_service->detail = $arrayServs[$s]["primaryRedeemer"]["number"];
                            $new_service->ticket_type = $arrayServs[$s]["ticketType"];
                            $new_service->aurora_code = $aurora_code;

                            if( !($new_service->save()) ){
                                $errS++;
                            }

                            // -------- GENERAL TABLE UNIFIED ----------------
                            $tags_expedia =
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
                                "model" => 'App\ExtensionExpediaService',
                                "object_id" => $new_service->id,
                                "code" => $new_service->code,
                                "tags" => $tags_expedia,
                                "status" => 1,
                                "made_date_time" => $header->date_created,
                                "start_date" => $service->date_start,
                                "end_date" => null,
                                "description" => $service->description,
                                "passenger" => $service->passenger,
                                "agent" => null,
                                "file_number" => null,
                                "aurora_code" => $service->aurora_code,
                                "type_service" => $service->type,
                                "quantity_pax" => $service->paxs,
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
                            'title' => "Expedia - Nueva reserva",
                            'body' => 'Ha llegado una nueva reserva a la Aurora 2 desde el correo de Expedia. Toque este mensaje para revisarlo.',
                            'click_action' => 'https://aurora.limatours.com.pe/central_bookings/expedia'];
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

        $list = ExtensionExpediaService::with('header','reserves.file');

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
        $package = ExtensionExpediaService::find($id);
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
        $package->save();
        $update_central->save();

        return Response::json(['success' => true]);
    }

    public function updateStatusExternal($id, Request $request)
    {
        $package = ExtensionExpediaService::find($id);
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
        $package = ExtensionExpediaService::find($id);
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

        $package->aurora_code = $request->__get('code');
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
                    "type" => 'expedia',
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);
                foreach ( $servs as $serv ){
                    DB::table('extension_expedia_services')->where('id', $serv['id'])
                        ->update([
                            'status' => 0
                        ]);
                    DB::table('extension_expedia_reserves')->insert([
                        "reserve_file_id" => $reserve_file_id,
                        "expedia_service_id" => $serv['id'],
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);

                    $update_central = CentralBooking::where('model', 'App\ExtensionExpediaService')
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
                'codcli' => "2EXPE",
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
}

<?php

namespace App\Http\TrainServices\Controller;

use App\Http\Controllers\Controller;
use App\Train;
use App\TrainRailRoute;
use App\TrainTemplate;
use App\TrainUser;
use App\Http\Traits\TrainService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;

class TrainServicesController extends Controller
{
    use TrainService;

    const WSDL_IR = "https://ws.incarail.app/itinerario?wsdl";
//    const WSDL_IR = "https://ws.incarail.com/itinerario?wsdl"; // PROD
    const TOKEN_IR = "wq3H4pxa3zpLj62LS5h36RSK7M3n4LMJ";
    const AGENCY_ID_IR = 1078;

    public function search_incarail($user_id, $route_id, $train_type_id, $date_from, $date_to, $quantity_num){

        // Logica para IncaRail
        $train_ir_id = Train::where('code','IR')->first()->id;
        $train_user_id = TrainUser::where('user_id',$user_id)->where('train_id',$train_ir_id);
        if( $user_id == 1 ){
            $train_user_id = 2941; // Test IR CODE
        }
        else {
            if( $train_user_id->count() > 0 ){
                $train_user_id = $train_user_id->first()->id;
            } else{
                $data = [
                    'success' => false,
                    'data' => "Su cuenta de usuario no está registrada en Inca Rail"
                ];
                return $data;
            }
        }

        // Primero partir de los resultados del ws, luego encontrar match con 43 y el otro code
//        $train_templates = TrainTemplate::where('status',1)
//            ->where('train_rail_route_id', $train_rail_route->id)
//            ->with('train_train_class')
//            ->get();
        $this->setUserIdIR($train_user_id);

        $error = '';
        $response = [];
        $query_inca_rail = $this->searchIR(
            $train_type_id,
            $route_id,
            $date_from,
            $date_to,
            $quantity_num,
            null
        );
        //        $data_inca_rail = $this->searchIR(2, 1, '01/05/2020', '02/05/2020', 2, 2);
        if( $query_inca_rail->codMensaje != 'M001001' ){
            $error = $query_inca_rail->desMensaje;
        } else {
            $response = $query_inca_rail->oResultado->itinerarios;
        }
        // A CADA RESULTADO BUSCARLE SU TRAIN TEMPLATE
        if( $error == '' ){
            $data = [
                'success' => true,
                'data' => $response
            ];
        } else {
            $data = [
                'success' => false,
                'data' => $error
            ];
        }

        return $data;

    }

    public function search(Request $request)
    {
        ini_set('soap.wsdl_cache_enabled', 0);
        ini_set('soap.wsdl_cache_ttl', 0);
        @ini_set('memory_limit', '-1');

        $train_type_id = $request->input('train_type_id'); // 1 // One way
        $route_id = $request->input('rail_route_id'); // 1 // Idas - vueltas
        $user_id = $request->input('user_id'); // 1 // Admin Test
        $client_id = $request->input('client_id'); // 1 // 9CORTE

        $date_from = $request->input('date_from'); // dd/mm/yyyy
        $date_to = $request->input('date_to'); // dd/mm/yyyy
        $guides = (int)$request->input('guides'); // 0
        $adults = (int)$request->input('adults'); // 1
        $children = (int)$request->input('children'); // 0
        $quantity_num = $adults + $children + $guides;


        $incarail_trains = $this->search_incarail($user_id, $route_id, $train_type_id, $date_from, $date_to, $quantity_num);

//        $this->setUserIdIR(2941);
//        $incarail_trains = $this->searchIR(2, 1, '20/07/2020', '20/07/2020', 2, 2);
//        var_export( $incarail_trains ); die;
//        $perurail_trains = $this->search_perurail($user_id, $rail_route_id, $train_type_id, $date_from, $date_to, $quantity_num);
        $perurail_trains = [];
//        $incarail_trains = [];

        $data = array(
            "incarail" => $incarail_trains,
            "perurail" => $perurail_trains,
        );

        return Response::json($data);

    }

    public function search_perurail($user_id, $rail_route_id, $train_type_id, $date_from, $date_to, $quantity_num)
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);
        @ini_set('memory_limit', '-1');
        ini_set("soap.wsdl_cache_enabled", 0);
        ini_set('default_socket_timeout', 600);
        date_default_timezone_set('America/Lima');
        ini_set('soap.wsdl_cache_ttl', 0);

        define('BASE_PATH', realpath(dirname(__FILE__)));
//        $base_path = $_SERVER['DOCUMENT_ROOT'];
        define('SOAP_DOMAIN_PERURAIL' , 'https://pax.perurail.com/desapr/rcusi/wsappcondor/public_html/service/');
//        define('SOAP_DOMAIN_PERURAIL' , 'https://servidor.perurail.com/webservice/public_html/service/');

        $WSDL_URI = SOAP_DOMAIN_PERURAIL . "access?wsdl";
//        $publicKeyServer = file_get_contents(BASE_PATH."/Keys/pubkeyPeruRail.pem");
//        $privateKey = file_get_contents(BASE_PATH."/Keys/privAgencia.pem");
//        $publicKeyServer = file_get_contents(BASE_PATH . "/../../../Keys/key.public.pem");
        $publicKeyServer = file_get_contents(BASE_PATH . "/../../../Keys/pubkey.pem");
        $privateKey = file_get_contents(BASE_PATH . "/../../../Keys/key.pem");

        $conection = new \SoapClient('https://pax.perurail.com/desapr/rcusi/wsappcondor/public_html/service/access?wsdl', array(
//        $conection = new \SoapClient('https://servidor.perurail.com/webservice/public_html/service/access?wsdl', array(
            'exceptions' => true, 'trace' => 1 ));

        $AuthHeader = new \stdClass();
        $AuthHeader->token = new \stdClass();
        $AuthHeader->token->type = 'request';

        $AuthHeader->token->force = false; //true;

//        $tokenData = array("username"  => 'USUARIOAGENCIA',"password"  => 'ClaveAgencia', "user_ip" => $_SERVER['REMOTE_ADDR']);
//        $tokenData = array("username"=>'LMIRANDA002', "password"=>'Desaweb123', "user_ip"=>"54.144.115.239");
        $tokenData = array("username"=>'JNUNEZ001', "password"=>'Desaweb123', "user_ip"=>$_SERVER['REMOTE_ADDR']);
        $tokenDataJSON = json_encode($tokenData);

        openssl_public_encrypt($tokenDataJSON, $encryptedData, $publicKeyServer);
        $token = base64_encode($encryptedData);

        openssl_sign($token, $sign, $privateKey, OPENSSL_ALGO_SHA256);
        $sign = base64_encode($sign);

        $AuthHeader->token->token = $token.".".$sign;
//        $AuthHeader->token->token = "WEF34fwfeE==.65EF34rT=="; // Ejem

        $Headers[] = new \SoapHeader(SOAP_DOMAIN_PERURAIL, 'AuthHeader', $AuthHeader);
        $conection->__setSoapHeaders($Headers);

        $Response=$conection->rutas();

        return $Response;

    }

    public function search3(Request $request)
    {
        ini_set('soap.wsdl_cache_enabled', 0);
        ini_set('soap.wsdl_cache_ttl', 0);
        @ini_set('memory_limit', '-1');

        $train_type_id = $request->input('train_type_id'); // 1 // One way
        $rail_route_id = $request->input('rail_route_id'); // 1 // OLLANTAYTAMBO - MACHU PICCHU
        $user_id = $request->input('user_id'); // 1 // Admin Test
        $client_id = $request->input('client_id'); // 1 // 9CORTE

        $date_from = $request->input('date_from'); // dd/mm/yyyy
        $date_to = $request->input('date_to'); // dd/mm/yyyy
        $guides = (int)$request->input('guides'); // 0
        $adults = (int)$request->input('adults'); // 1
        $children = (int)$request->input('children'); // 0
        $quantity_num = $adults + $children + $guides;


//        $incarail_trains = $this->search_incarail($user_id, $rail_route_id, $train_type_id, $date_from, $date_to, $quantity_num);
//        $this->setUserIdIR(2941);
//        $incarail_trains = $this->searchIR(2, 1, '01/06/2020', '02/06/2020', 2, 2);
//        var_export( $incarail_trains ); die;
        $perurail_trains = $this->search_perurail($user_id, $rail_route_id, $train_type_id, $date_from, $date_to, $quantity_num);
//        $perurail_trains = [];
        $incarail_trains = [];

        $data = array(
            "incarail" => $incarail_trains,
            "perurail" => $perurail_trains,
        );

        return Response::json($data);

    }

}

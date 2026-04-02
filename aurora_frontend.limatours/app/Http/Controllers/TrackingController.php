<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Clients;
use App\Notification;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PassengersImport;
use GuzzleHttp\Client;

class TrackingController extends Controller
{
    public $baseAuroraWS = '';
    public $files_onedb_ms = '';
    // public $baseAuroraWS = 'http://localhost:9000';

    //TODO revisar trae problemas con usuarios de tipo cliente.
    public function __construct()
    {
        $this->baseAuroraWS = config('services.aurora_extranet.domain');
        $this->files_onedb_ms = config('services.aurora_files.domain');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $return = (int) $request->__get('return');

        return view('menu.consulta_files')
            ->with('lang', config('app.locale'))
            ->with('bossFlag', 1)
            ->with('return', $return);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(){}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id){}

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id){}

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){}

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){}

    public function filter(Request $request)
    {
        try
        {
            set_time_limit(0);

            $customer = $request->__get('user'); $user_type = $request->__get('type'); $team = $request->__get('');
            $date_range = (array) $request->__get('dateRange'); $fecini = $date_range['startDate']; $fecfin = $date_range['endDate'];

            $fecini = explode("T", $fecini);
            $fecfin = explode("T", $fecfin);

            // -- ACUMULADO --
            $_fecini = strtotime($fecini[0]); $_fecfin = strtotime($fecfin[0]);

            $team = $request->__get('team'); $executives = (array) session()->get('executives_user');
            $type = 'success'; $files = [];

            if($team != '')
            {
                if(count($executives) == 0)
                {
                    $type = 'error';
                }
            }

            if($type == 'success')
            {
                if($customer == '')
                {
                    if($user_type == 'QRR' OR $user_type == 'QRV')
                    {
                        $customer = '';

                        foreach($executives as $key => $value)
                        {
                            $customer .= ($key > 0) ? ',' : ''; $customer .= "'" . $value['value'] . "'";
                        }
                    }
                }
                else
                {
                    $customer = "'" . $customer . "'";
                }

                $data = [
                    'executive' => $customer,
                    'tipuser' => $user_type,
                    'fecini' => date("d/m/Y", $_fecini),
                    'fecfin' => date("d/m/Y", $_fecfin),
                ];

                $params = http_build_query($data);

                $client = new \GuzzleHttp\Client();
                $baseUrlExtra = config('services.aurora_extranet.domain');
                // $baseUrlLocal = 'http://localhost:9000';
                $request = $client->get($baseUrlExtra.'/api/orders/api.php?method=cuadreFiles&'. $params);
                $files = json_decode($request->getBody()->getContents(), true);
            }

            return response()->json(['files' => $files, 'quantity' => count($files), 'type' => $type]);
        }
        catch (\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    /*
    public function search (Request $request){
        try
        {
            dd($request->all());

            $client = new \GuzzleHttp\Client();
            $type = $request->__get('typeSearch');
            $url = sprintf('%stracking/search', $this->baseAuroraWS);

            $data = [
                'form_params' => [
                    'client_code' => $request->__get('codCli'),
                    'date_from' => $request->__get('var1'),
                    'date_to' => $request->__get('var2'),
                ]
            ];
            $request = $client->post($url, $data);
            $response = json_decode($request->getBody()->getContents(), true);

            return $response;
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }
    */

    public function parse_response($response)
    {
        $files = [];

        foreach($response['data']['data'] as $file)
        {
            $file['paxs'] = (int) ($file['adults']) + (int) ($file['children']) + (int) ($file['infants']);

            $files[] = $file;
        }

        $response['items'] = $response['data']['totalItems'];
        $response['pages'] = $response['data']['totalPages'];
        $response['files'] = $files;

        return $response;
    }

    public function searchByDates(Request $request) {
        try
        {
            $client = new \GuzzleHttp\Client();
            $url = sprintf('%stracking/search', $this->files_onedb_ms);

            $fecini = $request->__get('fecIni') ?? '';
            $fecout = $request->__get('fecOut') ?? '';

            $fecini = ($fecini === 0 || $fecini === "0") ? '' : $fecini;
            $fecout = ($fecout === 0 || $fecout === "0") ? '' : $fecout;

            $data = [
                'form_params' => [
                    'client_code' => $request->__get('codCli'),
                    'date_from' => $fecini,
                    'date_to' => $fecout,
                ]
            ];

            $request = $client->post($url, $data);
            $response = json_decode($request->getBody()->getContents(), true);
            $response = $this->parse_response($response);

            return $response;
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function searchByFile(Request $request) {
        try
        {
            $client = new \GuzzleHttp\Client(["verify"=>false]);
            $url = sprintf('%stracking/search', $this->files_onedb_ms);

            $data = [
                'form_params' => [
                    'client_code' => $request->__get('codCli'),
                    'file' => $request->__get('nroRef'),
                ]
            ];
            $request = $client->post($url, $data);
            $response = json_decode($request->getBody()->getContents(), true);
            $response = $this->parse_response($response);

            return $response;
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function searchByOrder(Request $request) {
        try
        {
            $client = new \GuzzleHttp\Client();
            $url = sprintf('%stracking/search', $this->files_onedb_ms);

            $data = [
                'form_params' => [
                    'client_code' => $request->__get('codCli'),
                    'filter' => $request->__get('nroRes'),
                ]
            ];
            $request = $client->post($url, $data);
            $response = json_decode($request->getBody()->getContents(), true);
            $response = $this->parse_response($response);

            return $response;
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function searchByDescription(Request $request) {
        try
        {
            $client = new \GuzzleHttp\Client();
            $url = sprintf('%stracking/search', $this->files_onedb_ms);

            $data = [
                'form_params' => [
                    'client_code' => $request->__get('codCli'),
                    'filter' => $request->__get('description'),
                ]
            ];
            $request = $client->post($url, $data);
            $response = json_decode($request->getBody()->getContents(), true);
            $response = $this->parse_response($response);

            return $response;
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function searchByLocator(Request $request) {
        try
        {
            $client = new \GuzzleHttp\Client();
            $url = sprintf('%stracking/search', $this->files_onedb_ms);

            $data = [
                'form_params' => [
                    'client_code' => $request->__get('codCli'),
                    'filter' => $request->__get('description'),
                ]
            ];
            $request = $client->post($url, $data);
            $response = json_decode($request->getBody()->getContents(), true);
            $response = $this->parse_response($response);

            return $response;
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function getServices(Request $request, $nroref, $lang) {
        try
        {
            $client = new \GuzzleHttp\Client();

//            $url = $this->baseAuroraWS.'/api/tracking/services/'.
//                $nroref . '/' . $lang;

            $url = sprintf('%stracking/search/services/%s/%s',
                $this->files_onedb_ms,
                $nroref,
                $lang
            );

            $guzzleRequest = $client->get($url);
            $response = json_decode($guzzleRequest->getBody()->getContents(), true);
            return $response;

        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function getSkeleton(Request $request)
    {
        try
        {
            set_time_limit(0);
/*
            $client = new Client();
            $url = sprintf('%s/api/tracking/skeleton/%s/%s/%s/%s',
                $this->baseAuroraWS,
                $request->__get('codCli'),
                $request->__get('nroRef'),
                $request->__get('nroLoc'),
                $request->__get('lang'));
            $request = $client->get($url);
            $response = json_decode($request->getBody()->getContents(),true);
            session()->put('skeleton', $response);
*/

            $client = new \GuzzleHttp\Client();
            $url = sprintf('%stracking/search/skeleton', $this->files_onedb_ms);
            $params = [
                'query' => [
                    'codcli'   => $request->__get('codCli'),
                    'nroref'   => $request->__get('nroRef'),
                    'locator'  => $request->__get('nroLoc'),
                    'language' => $request->__get('lang')
                ]
            ];
            $apiResponse = $client->get($url, $params);
            $contents = $apiResponse->getBody()->getContents();
            $response = json_decode($contents, true);
            session()->put('skeleton', $response);

            return $response;

        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function getScheduled(Request $request)
    {
        try
        {
            $client = new Client();
//            $url = sprintf('%s/api/tracking/scheduled/%s/%s/%s',
//                $this->baseAuroraWS,
//                $request->__get('codCli'),
//                $request->__get('nroRef'),
//                $request->__get('lang')
//            );
//            $request = $client->get($url);
            $url = sprintf('%stracking/search/scheduled', $this->files_onedb_ms);

            $params = [
                'query' => [
                    'codcli' => $request->__get('codCli'),
                    'nroref' => $request->__get('nroRef'),
                    'lang'   => $request->__get('lang')
                ]
            ];

            $request = $client->get($url, $params);
            $response = json_decode($request->getBody()->getContents(),true);
            session()->put('scheduled', $response);
            return $response;
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function getPaxs(Request $request) {
        try
        {
            $client = new \GuzzleHttp\Client();

//            $url = $this->baseAuroraWS.'/api/tracking/paxs/'.
//                $request->__get('nroRef');

            $url = sprintf('%stracking/search/paxs/%s',
                $this->files_onedb_ms,
                $request->__get('nroRef')
            );

            $request = $client->get($url);
            $response = json_decode($request->getBody()->getContents(), true);
            return $response;
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function getHotels(Request $request) {

        try
        {
            $client = new \GuzzleHttp\Client();
//            $url = $this->baseAuroraWS.'/api/tracking/hotels/'.
//                $request->__get('codCli').'/'.
//                $request->__get('nroRef');
            $url = sprintf('%stracking/search/hotels/%s/%s',
                $this->files_onedb_ms, // Asegúrate que esta variable apunte a tu server Node
                $request->__get('codCli'),
                $request->__get('nroRef')
            );
            $request = $client->get($url);
            $response = json_decode($request->getBody()->getContents(), true);

            session()->put('hotels', $response); session()->put('format_pdf', 'portrait');
            return $response;
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function getGuides(Request $request) {

        try
        {
            $client = new \GuzzleHttp\Client();

//            $url = $this->baseAuroraWS.'/api/tracking/guides/'.
//                $request->__get('nroRef').'/'.
//                $request->__get('lang');

            $url = sprintf('%stracking/search/guides/%s/%s',
                $this->files_onedb_ms,
                $request->__get('nroRef'),
                $request->__get('lang')
            );

            $request = $client->get($url);
            $response = json_decode($request->getBody()->getContents(), true);
            return $response;
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function getInvoice(Request $request) {
        try
        {
            $client = new \GuzzleHttp\Client();
//            $url = $this->baseAuroraWS.'/api/tracking/invoice/'.
//                $request->__get('codCli').'/'.
//                $request->__get('nroRef');
//            $request = $client->get($url);
            $url = sprintf('%stracking/search/invoice', $this->files_onedb_ms);

            $params = [
                'query' => [
                    'codcli' => $request->__get('codCli'),
                    'nroref' => $request->__get('nroRef')
                ]
            ];
            $request = $client->get($url, $params);
            $response = json_decode($request->getBody()->getContents(), true);
            return $response;
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function getPdfInvoice(Request $request) {
        try {
            $codCli = $request->__get('codCli');
            $nroRef = $request->__get('nroRef');

            $client = new \GuzzleHttp\Client();
//            $url = $this->baseAuroraWS.'/api/tracking/search/file/'.
//                $codCli.'/'.
//                $nroRef;
            $url = sprintf('%stracking/search/file/' .
                $codCli . '/' .
                $nroRef, $this->files_onedb_ms);

            $request = $client->get($url);
//
//            print_r($url);
//            print_r($request);
//            die;

            $cabecera = json_decode($request->getBody()->getContents(), true);

            if(count($cabecera['files'])>0){

                $cabecera = $cabecera['files'][0];

                $client = new \GuzzleHttp\Client();
//                $url = $this->baseAuroraWS.'/api/tracking/invoice/'.
//                    $codCli.'/'.
//                    $nroRef;
//                $request = $client->get($url);
                $url = sprintf('%stracking/search/invoice', $this->files_onedb_ms);
                $params = [
                    'query' => [
                        'codcli' => $codCli,
                        'nroref' => $nroRef
                    ]
                ];
                $request = $client->get($url, $params);
                $detalle = json_decode($request->getBody()->getContents(), true);
                $totalInvoice = 0;
                $totalNCredito = 0;
                $totalNDebito = 0;

                foreach($detalle['report'][0] as $row){
                    $totalInvoice = $totalInvoice  + $row['DEBE'];
                }

                $pdf = app('dompdf.wrapper');
                $pdf->setPaper('a4');
                $pdf->loadview('exports.files', ['lang' => config('app.locale'),'cabecera' => $cabecera, 'detalle' => $detalle, 'totales' => ['invoice' => $totalInvoice, 'nota_credito' => $totalNCredito, 'nota_devito' => $totalNDebito ]]);

                // return $pdf->stream('reporte.pdf');
                return $pdf->download('invoice-'.$nroRef.'.pdf');

            } else{
                throw new \Exception("parametros incorrectos");
            }
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }


    public function getFlights(Request $request) {
        try
        {
            $client = new \GuzzleHttp\Client();
            $url = $this->baseAuroraWS.'/api/tracking/flights/'.
                $request->__get('nroRef');
            $request = $client->get($url);
            $response = json_decode($request->getBody()->getContents(), true);
            return $response;
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function getFlightsData() {
        try
        {
            $client = new \GuzzleHttp\Client();
            $url = $this->baseAuroraWS.'/api/generals/flights';
            $request = $client->get($url);
            $response = json_decode($request->getBody()->getContents(), true);
            return $response;
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function saveFlight(Request $request) {

        try
        {
            $client = new \GuzzleHttp\Client();

            $params = $request->__get('params');
            $options = [
                'form_params' => [
                    'flights'   => $params['flights'],
                    'file'      => $params['file']
                ]
            ];
            $request = $client->post($this->baseAuroraWS.'/api/flight/save', $options, []);
            $response = $request->getBody()->getContents();
            return $response;
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function removeFlight(Request $request) {

        try
        {
            $client = new \GuzzleHttp\Client();

            $params = $request->__get('params');
            $options = [
                'form_params' => [
                    'flight'   => $params['flight']
                ]
            ];
            $request = $client->post($this->baseAuroraWS.'/api/flight/remove', $options, []);
            $response = $request->getBody()->getContents();
            return $response;
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

//    public function getItinerary(Request $request)
//    {
//        try
//        {
//            $client = new \GuzzleHttp\Client(); $client_logo = '-';
//
//            if($request->__get('withClientLogo') == 1)
//            {
//                $client_logo = Clients::where('code', '=', $request->__get('codCli'))->first()->logo;
//            }
//
//            $data = [
//                'nroref' => $request->__get('nroRef'),
//                'imgcod' => $request->__get('imgCod'),
//                'withHeader' => $request->__get('withHeader'),
//                'withClientLogo' => $request->__get('withClientLogo'),
//                'lang' => $request->__get('lang'),
//                'logo' => urlencode($client_logo),
//                'portada' => $request->__get('portada'),
//
//            ];
//
//            $params = http_build_query($data);
//
//            $url = $this->baseAuroraWS.'/api/tracking/search.php?fx=itinerary&'. $params;
//
//            // dd($url);
//            // die("..");
//            $request = $client->get($url);
//            $response = json_decode($request->getBody()->getContents(), true);
//            return $response;
//        }
//        catch(\Exception $ex)
//        {
//            return $this->throwError($ex);
//        }
//    }

    public function getItinerary(Request $request)
    {
        try
        {
            $client = new \GuzzleHttp\Client();
            $client_logo = '-';

            // Lógica original para obtener el logo del cliente
            if($request->__get('withClientLogo') == 1)
            {
                $client_logo = Clients::where('code', '=', $request->__get('codCli'))->first()->logo;
            }

            // Preparamos los parámetros para el microservicio Node
            $data = [
                'fx'             => 'itinerary', // Obligatorio según tu router Node
                'nroref'         => $request->__get('nroRef'),
                'imgcod'         => $request->__get('imgCod'),
                'withheader'     => $request->__get('withHeader'),
                'withclientlogo' => $request->__get('withClientLogo'),
                'lang'           => $request->__get('lang'),
                'logo'           => $client_logo, // Guzzle url-encodeará esto automáticamente
                'portada'        => $request->__get('portada'),
                // 'locator'     => '' // Puedes agregarlo si lo tienes en el request de Laravel
            ];

            $url = $this->files_onedb_ms . '/tracking/handleSearch';

            // Realizamos la petición GET pasando los parámetros
            $response = $client->request('GET', $url, [
                'query' => $data
            ]);

            $resultNode = json_decode($response->getBody()->getContents(), true);

            // Mapeamos la respuesta de Node (success/download_url) a lo que espera Vue (estado/message)
            if (isset($resultNode['success']) && $resultNode['success'] === true) {
                return [
                    'estado'  => 1,
                    'message' => $resultNode['download_url'] // URL de S3
                ];
            } else {
                return [
                    'estado'  => 0,
                    'message' => 'Error generating itinerary in microservice'
                ];
            }
        }
        catch(\Exception $ex)
        {
            \Log::error("Error en getItinerary: " . $ex->getMessage());
            return $this->throwError($ex);
        }
    }

    public function getPortadas(Request $request)
    {
        try
        {
            $client = new \GuzzleHttp\Client();
            $url = $this->baseAuroraWS.'/api/tracking/portadas/'.$request->__get('lang');
            $request = $client->get($url);
            $response = json_decode($request->getBody()->getContents(), true);
            return $response;
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

    public function getClient(Request $request)
    {
        try
        {
            $client = Clients::find($request->__get('id'));
            return json_decode($client, true);
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

}

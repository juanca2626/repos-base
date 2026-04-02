<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client; // Volvemos a importar Guzzle

class ItineraryExtranetController extends Controller
{
    private $API_Endpoint = '';

    public function __construct()
    {
        // Apuntamos a la nueva ruta de Express
        $baseUrl = config('services.files_onedb.domain');
        $this->API_Endpoint = $baseUrl . 'tracking/handleSearch';
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identi' => 'required',
            'nroref' => 'required',
            'destiny' => 'required',
            'language' => 'required',
            'tipdoc' => 'required',
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'message' => $validator->getMessageBag()]);
        }

        $params = [
            'fx'             => ($request->post('tipdoc') == 'H') ? 'accommodation' : 'itinerary',
            'nroref'         => $request->post('nroref'),
            'destiny'        => $request->post('destiny'),
            'lang'           => $request->post('language'),
            'imgcod'         => $request->post('imgcod'),
            'withheader'     => 1,
            'withclientlogo' => 0,
            'logo'           => 0,
        ];

        $response = $this->callApi($params);

        return Response::json($response);
    }

    private function callApi($params)
    {
        try {
            $client = new Client();

            // Usamos el método GET y pasamos el arreglo en la llave 'query' (Query String)
            $response = $client->get($this->API_Endpoint, [
                'query'   => $params,
                'timeout' => 240,
            ]);

            $data = json_decode($response->getBody(), true);

            return [
                'success'      => isset($data['success']) ? $data['success'] : false,
                'message'      => isset($data['message']) ? $data['message'] : 'Error desconocido',
                'download_url' => isset($data['download_url']) ? $data['download_url'] : null,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al procesar la solicitud. Detalles: ' . $e->getMessage(),
            ];
        }
    }
}


/*
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;

class ItineraryExtranetController extends Controller
{
    private $API_Endpoint = '';

    public function __construct()
    {
//        $this->API_Endpoint = 'https://extranet.litoapps.com/masi/api/generate_itinerary/';
        $this->API_Endpoint = 'https://extranet.limatours.dev/masi/api/generate_itinerary/';
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identi' => 'required',
            'nroref' => 'required',
            'destiny' => 'required',
            'language' => 'required',
            'tipdoc' => 'required',
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'message' => $validator->getMessageBag()]);
        }

        $params = [
            'identi' => $request->post('identi'),
            'nroref' => $request->post('nroref'),
            'destiny' => $request->post('destiny'),
            'language' => $request->post('language'),
            'tipdoc' => $request->post('tipdoc'),
            'imgcod' => $request->post('imgcod'),
            'optimg' => $request->post('optimg'),
        ];

        $response = $this->callApi($params);

        return Response::json($response);
    }

    private function callApi($params)
    {
        try {
            $client = new Client();
            $response = $client->post($this->API_Endpoint, [
                'json' => $params,
                'timeout' => 240,
            ]);

            $data = json_decode($response->getBody(), true);

            return [
                'success' => isset($data['estado']) && $data['estado'] == 1,
                'message' => $data['message'] ?? 'Error desconocido',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al procesar la solicitud. Detalles: ' . $e->getMessage(),
            ];
        }
    }

}
*/

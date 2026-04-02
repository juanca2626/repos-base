<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public $baseAuroraWS;
    // public $baseAuroraWS = 'http://localhost:9000';

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:mfusuariostom.read');
        $this->baseAuroraWS = config('services.aurora_extranet.domain');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('users.usuariosTOM');
    }

    public function users_tom(Request $request)
    {
        $client = new \GuzzleHttp\Client();
        $url = $this->baseAuroraWS.'/api/orders/api.php?method=usersTOM&nomesp=';
        $request = $client->get($url);
        $users = json_decode($request->getBody()->getContents(), true);

        return response()->json(['users' => $users, 'quantity' => count($users)]);
    }

    public function export_tom(Request $request)
    {
        $client = new \GuzzleHttp\Client();
        $url = $this->baseAuroraWS.'/api/orders/api.php?method=exportTOM&nomesp=';
        $request = $client->get($url);
        $users = json_decode($request->getBody()->getContents(), true);

        session()->put('usersTOM', $users);

        return response()->json(['users' => $users, 'quantity' => count($users)]);
    }

    public function add_tom(Request $request)
    {
        $data = $request->all(); $params = http_build_query($data);

        $client = new \GuzzleHttp\Client();
        $url = $this->baseAuroraWS.'/api/orders/api.php?method=addTOM&' . $params;
        $request = $client->get($url);
        $response = json_decode($request->getBody()->getContents(), true);

        return response()->json($response);
    }

    public function update_tom(Request $request)
    {
        $data = $request->all(); $params = http_build_query($data);

        $client = new \GuzzleHttp\Client();
        $url = $this->baseAuroraWS.'/api/orders/api.php?method=updateTOM&' . $params;
        $request = $client->get($url);
        $response = json_decode($request->getBody()->getContents(), true);

        return response()->json($response);
    }

    public function update_state_tom(Request $request)
    {
        $data = $request->all(); $params = http_build_query($data);

        $client = new \GuzzleHttp\Client();
        $url = $this->baseAuroraWS.'/api/orders/api.php?method=updateStateTOM&' . $params;
        $request = $client->get($url);
        $response = json_decode($request->getBody()->getContents(), true);

        return response()->json($response);
    }

    public function vacations_tom(Request $request)
    {
        $client = new \GuzzleHttp\Client();
        $url = $this->baseAuroraWS.'/api/orders/api.php?method=usersTOM&nomesp=' . $request->__get('id');
        $request = $client->get($url);
        $response = json_decode($request->getBody()->getContents(), true);

        return response()->json($response);
    }

    public function customersTOM(Request $request)
    {
        $data = $request->all(); $params = http_build_query($data);

        $client = new \GuzzleHttp\Client();
        $url = $this->baseAuroraWS.'/api/orders/api.php?method=customersTOM&' . $params;
        $request = $client->get($url);
        $customers = json_decode($request->getBody()->getContents(), true);

        return response()->json(['customers' => $customers, 'quantity' => count($customers)]);
    }

    public function addCustomerTOM(Request $request)
    {
        $data = $request->all(); $params = http_build_query($data);

        $client = new \GuzzleHttp\Client();
        $url = $this->baseAuroraWS.'/api/orders/api.php?method=addCustomerTOM&' . $params;
        $request = $client->get($url);
        $response = json_decode($request->getBody()->getContents(), true);

        return response()->json($response);
    }

    public function removeCustomerTOM(Request $request)
    {
        $data = $request->all(); $params = http_build_query($data);

        $client = new \GuzzleHttp\Client();
        $url = $this->baseAuroraWS.'/api/orders/api.php?method=removeCustomerTOM&' . $params;
        $request = $client->get($url);
        $response = json_decode($request->getBody()->getContents(), true);

        return response()->json($response);
    }

    public function countriesTOM(Request $request)
    {
        $data = $request->all(); $params = http_build_query($data);

        $client = new \GuzzleHttp\Client();
        $url = $this->baseAuroraWS.'/api/orders/api.php?method=countriesTOM&' . $params;
        $request = $client->get($url);
        $customers = json_decode($request->getBody()->getContents(), true);

        return response()->json(['countries' => $customers, 'quantity' => count($customers)]);
    }

    public function addCountryTOM(Request $request)
    {
        $data = $request->all(); $params = http_build_query($data);

        $client = new \GuzzleHttp\Client();
        $url = $this->baseAuroraWS.'/api/orders/api.php?method=addCountryTOM&' . $params;
        $request = $client->get($url);
        $response = json_decode($request->getBody()->getContents(), true);

        return response()->json($response);
    }

    public function removeCountryTOM(Request $request)
    {
        $data = $request->all(); $params = http_build_query($data);

        $client = new \GuzzleHttp\Client();
        $url = $this->baseAuroraWS.'/api/orders/api.php?method=removeCountryTOM&' . $params;
        $request = $client->get($url);
        $response = json_decode($request->getBody()->getContents(), true);

        return response()->json($response);
    }

}

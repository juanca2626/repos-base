<?php

namespace App\Http\Controllers;

use App\Country;
use App\Doctype;
use App\User;
use Auth;
use Illuminate\Http\Request;
use Config;

class StatsController extends Controller
{
    public $baseUrlExtra = '';
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:mfstatclients.read');
        $this->baseUrlExtra = config('app.mix_base_external_url');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('stats.index')->with('bossFlag', (int) $this->hasPermission('stats.boss'));
    }

    public function login()
    {
        return view('stats.login')->with('bossFlag', (int) $this->hasPermission('stats.boss'));
    }

    public function search(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $client = new \GuzzleHttp\Client();
            $type = $request->__get('type');
            $date_range = (array) $request->__get('date_range');
            $sector = $request->__get('sector');

            $params = [
                'type' => $type,
                'sector' => $sector,
                'start' => (@$date_range['startDate'] != '') ? date("Y-m-d", strtotime($date_range['startDate'])) : '',
                'end' => (@$date_range['endDate'] != '') ? date("Y-m-d", strtotime($date_range['endDate'])) : '',
            ];
            $_request = $client->get($this->baseUrlExtra . 'clients/stats?' . http_build_query($params));
            $response = (array) json_decode($_request->getBody()->getContents(), true);

            if ($response['type'] == 'success') {
                session()->put('stats_clients', $response['response']);
            }

            return response()->json($response);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }
}

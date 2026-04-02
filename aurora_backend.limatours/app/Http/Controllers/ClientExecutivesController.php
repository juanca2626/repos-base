<?php

namespace App\Http\Controllers;

use App\ClientExecutive;
use App\User;
use App\Client;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ClientExecutivesController extends Controller
{
    public function __construct()
    {
        // $this->middleware('permission:contacts.read')->only('index');
        // $this->middleware('permission:contacts.create')->only('store');
        // $this->middleware('permission:contacts.update')->only('update');
        // $this->middleware('permission:contacts.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $client_id = $request->input('client_id');
        $region_id = $request->input('region_id');

        $executive_client_frontend = [];

        $executive_client_database = ClientExecutive::select(['id', 'user_id', 'client_id', 'use_email_reserve'])->
        whereHas('user', function ($query) use ($querySearch) {
            $query->where('user_type_id',3);
            $query->where('name', 'like', '%' . $querySearch . '%');
        })
        ->where(['client_id' => $client_id])
        ->where('business_region_id', $region_id);

        $count = $executive_client_database->count();

        if ($paging === 1) {
            $executive_client_database = $executive_client_database->take($limit)->get();
        } else {
            $executive_client_database = $executive_client_database->skip($limit * ($paging - 1))->take($limit)->get();
        }

        if ($count > 0) {
            for ($j = 0; $j < count($executive_client_database); $j++) {
                $executive_client_frontend[$j]["executive_id"] = $executive_client_database[$j]["id"];
                $executive_client_frontend[$j]["client_id"] = $executive_client_database[$j]["client_id"];
                $executive_client_frontend[$j]["name"] = $executive_client_database[$j]["user"]["name"] ? $executive_client_database[$j]["user"]["name"] : $executive_client_database[$j]["user"]["email"];
                $executive_client_frontend[$j]["code"] = $executive_client_database[$j]["user"]["code"];
                $executive_client_frontend[$j]["user_id"] = $executive_client_database[$j]["user_id"];
                $executive_client_frontend[$j]["use_email_reserve"] = $executive_client_database[$j]["use_email_reserve"];
                $executive_client_frontend[$j]["selected"] = false;
            }
        }

        $data = [
            'data' => $executive_client_frontend,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    public function byExecutive($user_id)
    {

        $executive_client_database = ClientExecutive::select(['id', 'user_id', 'client_id'])
            ->with('user')->where(['user_id' => $user_id])->get();


        $data = [
            'data' => $executive_client_database,
            'success' => true
        ];

        return Response::json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $executive_user_id = $request->input('user_id');
        $client_id = $request->input('client_id');
        $region_id = $request->input('region_id');

        $executive_ = User::find($executive_user_id);
        $client_ = Client::find($client_id);

        $executive = new ClientExecutive();
        $executive->status = 1;
        $executive->user_id = $executive_user_id;
        $executive->client_id = $client_id;
        $executive->business_region_id = $region_id;

        $response = '';
        if( $executive->save() ) {

            /*Migration*/
//            $params = 'executive=' . strtoupper($executive_->code) . '&identi=J&customer=' . strtoupper($client_->code);
//            $client = new \GuzzleHttp\Client();
//            $url = config('services.aurora_extranet.domain') . '/api/orders/api.php?method=addCustomerTOM&' . $params;
//            $request = $client->get($url);
            /*Migration*/

            /*Migration*/
            $client = new \GuzzleHttp\Client();
            $request = $client->post(config('services.files_onedb.domain') .
                'customers/' . strtoupper($client_->code) . '/executives/tom', [
                'json' => [
                    "identi" => "J",
                    "executive" => strtoupper($executive_->code)
                ],
                'timeout' => 60,
            ]);
            /*Migration*/

            $response = json_decode($request->getBody()->getContents(), true);
        }

        return Response::json(['success' => true, 'executive_id' => $executive->id, "response_extranet"=>$response]);
    }

    public function storeAll(Request $request)
    {
        $client_id = $request->input('client_id');

        $client_executive_database = ClientExecutive::select(['id'])->where('client_id', $client_id);

        $users_database = User::select(['id'])->where('user_type_id', 1);

        if ($client_executive_database->count() > 0) {
            $users_database->whereNotIn('id', $client_executive_database);
        }

        $users_database = $users_database->get();

        $users_transaction_save = [];

        date_default_timezone_set("America/Lima");

        foreach ($users_database as $key => $user) {
            $users_transaction_save[$key] = [
                'user_id' => $user['id'],
                'client_id' => $client_id,
                'status' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ];
        }

        if (!empty($users_transaction_save)) {
            $result = ClientExecutive::insert($users_transaction_save);
        }

        return Response::json(['success' => true]);
    }

    public function inverse(Request $request)
    {
        $executive = ClientExecutive::where('id', $request->input('executive_id'))
            ->with(['user', 'client'])
            ->first();

        /* Migration
        $params = 'executive='.strtoupper($executive->user->code).'&identi=J&customer='.strtoupper($executive->client->code);
        $client = new \GuzzleHttp\Client();
        $url = config('services.aurora_extranet.domain').'/api/orders/api.php?method=removeCustomerTOM&' . $params;
        $request = $client->get($url);
        * Migration */

        /*Migration*/
        $client = new \GuzzleHttp\Client();
        $request = $client->delete(config('services.files_onedb.domain') .
            'customers/' . strtoupper($executive->client->code) . '/executives/tom', [
            'json' => [
                "identi" => "J",
                "executive" => strtoupper($executive->user->code)
            ],
            'timeout' => 60,
        ]);
        /*Migration*/

        $response = json_decode($request->getBody()->getContents(), true);

        if( $response === 'success' || (isset($response['error']) && $response['error']===false) ){
            $executive->forceDelete();
            return Response::json(['success' => true, 'response_extranet'=> $response]);
        } else {
            return Response::json(['success' => false, 'response_extranet'=> $response['error']]);
        }
    }

    public function inverseAll(Request $request)
    {
        $client_id = $request->input('client_id');

        DB::transaction(function () use ($client_id) {

            ClientExecutive::where('client_id', $client_id)->forceDelete();
        });
        return Response::json(['success' => true]);
    }

    public function update_use_email($id, Request $request)
    {
        $use_email_reserve = $request->input('use_email_reserve');

        $client_executive = ClientExecutive::find($id);
        $client_executive->use_email_reserve = $use_email_reserve;
        $client_executive->save();

        return Response::json(['success' => true]);
    }

    public function update_clients_brasil(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try
        {
            $ignore = [
                '5PRIVI', '5UPSCA', '5BRIEF', '5CWLIT', '5CENTR', '5NASCI', '5INCO', '5INTR',
                '9WAYT', '5CANTA', '5FENIX', '5ADV', '5THOUS', '5TOPSE', '5PERTR', '5SANOF',
                '5IMMAG', '5FLYTO', '5TAVAR', '5TSOLU', '5INCEN', '5CASEI', '5GIGRU', '5BRCON',
                '5DMCB', '5AMBTE', '3MMASS', '5ETOUR', '5LABR', '7OPCO', 'OEMA3', 'ONRP42', 'OERP43',
                '7HISBR', '5OPUS', '8COMPA', '3MSC', '5EHTL', '5LADIF', '5LADIT', '9TSB', '5TBOTS',
                '5VITER', '5BARAT', '9PROME', 'FS9603', '5HURBA'
            ]; $codes = [];

            $clients = Client::where('country_id', '=', 12)->whereNotIn('code', $ignore)->get();
            $executive_code = 'RCA'; $executive_code_update = 'DDL';
            $client = new \GuzzleHttp\Client();

            $executive_code = User::where('code', '=', $executive_code)->first();
            $executive_code_update = User::where('code', '=', $executive_code_update)->first();

            foreach($clients as $key => $_client)
            {
                ClientExecutive::where('client_id', '=', $_client->id)->where('user_id', '=', $executive_code->id)->update([
                    'user_id' => $executive_code_update->id,
                ]);

                $codes[] = "'" . $_client->code . "'";
            }

            return response()->json([
                'type' => 'success',
                'clients' => implode(",", $codes),
                'message' => 'Especialistas asociados de ' . $executive_code . ' a ' . $executive_code_update,
            ]);
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }

}

<?php

namespace App\Http\Controllers;

use App\Client;
use App\Market;
use App\UserMarket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class MarketsController extends Controller
{
    public function __construct()
    {
//        $this->middleware('permission:markets.read')->only('index');
        $this->middleware('permission:markets.create')->only('store');
        $this->middleware('permission:markets.update')->only('update');
        $this->middleware('permission:markets.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index()
    {
        $market = Market::with([
            'clients' => function ($query) {
                $query->select(['id', 'code', 'country_id', 'language_id', 'market_id', 'name', 'status']);
                $query->with([
                    'countries.translations' => function ($query) {
                        $query->where('type', 'country');
                        $query->where('language_id', 1);
                    }
                ]);
            }
        ])->where('status', 1)->get();
        return Response::json(['success' => true, 'data' => $market]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:markets,name'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false]);
        } else {
            $markets = new Market();
            $markets->name = $request->input('name');
            $markets->status = $request->input('status');
            $markets->save();

            return Response::json(['success' => true]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $markets = Market::find($id);

        return Response::json(['success' => true, 'data' => $markets]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:markets,name,'.$id
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false]);
        } else {
            $markets = Market::find($id);
            $markets->name = $request->input('name');
            $markets->status = $request->input('status');
            $markets->save();
            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $markets = Market::find($id);

        $markets->delete();

        return Response::json(['success' => true]);
    }

    public function selectBox()
    {
        $markets = Market::select('id', 'name', 'code')->where('status', 1)->get();
        $result = [];
        foreach ($markets as $market) {
            array_push($result, [
                'text' => $market->name,
                'value' => $market->id,
                'code' => $market->code
            ]);
        }
        return Response::json(['success' => true, 'data' => $result]);
    }

    public function updateStatus($id, Request $request)
    {
        $market = Market::find($id);
        if ($request->input("status")) {
            $market->status = false;
        } else {
            $market->status = true;
        }
        $market->save();
        return Response::json(['success' => true]);
    }

    public function selectMarketsModalShare()
    {

        $markets_of_user = UserMarket::where('user_id', '=', Auth::user()->id)->get();

        $markets = Market::select('id', 'name')->where('status', 1)->get();

        $markets = $markets->transform(function ($item) use ($markets_of_user) {

            $item['belongs_user'] = false;

            foreach ($markets_of_user as $market_of_user) {
                if ($market_of_user->market_id === $item['id']) {
                    $item['belongs_user'] = true;
                    break;
                }
            }

            return $item;
        });

        return \response()->json($markets, 200);
    }

    public function getClientsMarket(Request $request)
    {
        $market_id = $request->post('market_id');

        $clients = Client::select('id', 'name', 'code')->where('market_id', $market_id)->where('status', 1)->get();

        $clients_new = [];

        foreach ($clients as $client) {
            array_push($clients_new, ["code" => $client["id"], "label" => "(".$client["code"].") ".$client["name"]]);
        }

        return \response()->json($clients_new, 200);
    }

    public function getCountries(Request $request)
    {
        $markets = $request->get('regions');
        $countries = Client::whereIn('market_id', $markets)
            ->where('status', 1)
            ->with([
                'countries' => function ($query) {
                    $query->select(['id', 'iso']);
                    $query->with([
                        'translations' => function ($query) {
                            $query->select(['object_id', 'value']);
                            $query->where('language_id',1);
                            $query->where('type','country');
                        }
                    ]);
                }
            ])
            ->distinct()
            ->get(['country_id']);

        $countries = $countries->transform(function ($country){
            return [
                'id' => $country->country_id,
                'name' => $country->countries->translations[0]->value,
                'iso' => $country->countries->iso,
            ];
        });

        $data = [
            'countries' => $countries,
        ];

        return \response()->json($data);
    }
}

<?php

namespace App\Http\Controllers;

use App\Client;
use App\ClientRatePlan;
use App\HotelClient;
use App\MarkupRatePlan;
use App\RatesPlans;
use App\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ClientRatePlansController extends Controller
{
    public function index(Request $request)
    {
        $hotel_id = $request->input('hotel_id');
        $client_id = $request->input('client_id');
        $period = $request->input('period');

        if ($client_id) {
            $client_rate_ids = ClientRatePlan::where([
                'client_id' => $client_id,
                'period' => $period
            ])->pluck('rate_plan_id');

            $rate_plans = RatesPlans::select('id', 'name')->where('hotel_id', $hotel_id)->where('status',
                1)->whereIn('id', $client_rate_ids)->get();
        } else {
            $rate_plans = RatesPlans::select('id', 'name')->where('hotel_id', $hotel_id)->where('status', 1)->get();
        }

        $tarifas = array();
        foreach ($rate_plans as $rate_plan) {

            $rate_plan->selected = false;
            $tarifas[] = $rate_plan;
        }

        return Response::json(['success' => true, 'data' => $tarifas]);
    }

    public function consultSelected(Request $request)
    {
        $hotel_id = $request->input('hotel_id');
        $client_id = $request->input('client_id');
        $period = $request->input('period');

        $client_rate_ids = ClientRatePlan::where([
            'client_id' => $client_id,
            'period' => $period
        ])->pluck('rate_plan_id');

        $rate_plans = RatesPlans::select('id', 'name')->where('hotel_id', $hotel_id)->whereNotIn('id',
            $client_rate_ids)->get();

        $markupRates = MarkupRatePlan::select(['markup', 'rate_plan_id'])->where([
            'client_id' => $client_id,
            'period' => $period
        ])->get();

        $tarifas = array();
        foreach ($rate_plans as $rate_plan) {

            $markup = "";
            foreach ($markupRates as $markupRate) {
                if ($markupRate->rate_plan_id == $rate_plan->id) {
                    $markup = $markupRate->markup;
                }
            }
            $rate_plan->markup = $markup;
            $rate_plan->selected = false;
            $tarifas[] = $rate_plan;
        }

        return Response::json(['success' => true, 'data' => $tarifas]);
    }

    public function bloquearTarifaClientes($rate_plan_id, $period, $client_id)
    {

        $clienteRatePlan = ClientRatePlan::where([
            'rate_plan_id' => $rate_plan_id,
            'period' => $period,
            'client_id' => $client_id
        ]);
        if ($clienteRatePlan->count() == 0) {

            $rate_plans = new ClientRatePlan();
            $rate_plans->rate_plan_id = $rate_plan_id;
            $rate_plans->period = $period;
            $rate_plans->client_id = $client_id;
            $rate_plans->save();
        }
        $this->deleteMarkupRatePlans($client_id, $rate_plan_id, $period);

    }

    public function clienteAll($period, $hotel_id, $market, $querySearch)
    {

        $clientes = [];

        $client_database = Client::select(['id', 'code', 'name'])->with([
            'markups' => function ($query) use ($period, $hotel_id) {
                $query->where('period', $period);
            }
        ])->whereHas('markups', function ($query) use ($period, $hotel_id) {
            $query->where('period', $period);
        }
        )->where('status', 1);
        if ($market) {
            $client_database->where('market_id', $market);
        }


        if ($querySearch) {
            $country_ids = Translation::where('value', 'LIKE', '%'.$querySearch.'%')->where('type',
                'country')->where('language_id', 1)->pluck('object_id');
            $client_database->whereIn('country_id', $country_ids);
            $client_database->orWhere(function ($query) use ($querySearch) {
                $query->orWhereRaw("CONCAT(code, '', name) LIKE ?", ['%'.$querySearch.'%']);
            });
        }


        $hotel_client_database = HotelClient::select(['client_id', 'period'])->where([
            'hotel_id' => $hotel_id,
            'period' => $period
        ]);
        $hotel_client_ids = $hotel_client_database->pluck('client_id');
        if ($hotel_client_database->count() > 0) {
            $client_database->whereNotIn('id', $hotel_client_ids);
        }
        $clientes = $client_database->get();

        return $clientes;
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {

            $markup = $request->input('porcentage');
            $client_id = $request->input('client_id');
            $hotel_id = $request->input('hotel_id');
            $period = $request->input('period');
            $market = $request->input('market');
            $rate_plan_id = $request->input('rate_plan_id');
            $aplicaTarifa = $request->input('aplicaTarifa');
            $query = $request->input('query');

            if ($aplicaTarifa == "1") {

                foreach ($rate_plan_id as $rateId) {
                    $this->bloquearTarifaClientes($rateId, $period, $client_id);
                }

            } else {
                // bloqueamos todos los clientes del filtro

                $clientes = $this->clienteAll($period, $hotel_id, $market, $query);


                foreach ($clientes as $cliente) {


                    $ratesPeriod = $cliente->rateplans($period)->pluck('rates_plans.id')->toArray();


                    $ratesIds = [];
                    foreach ($rate_plan_id as $rateId) {
                        if (!in_array($rateId, $ratesPeriod)) {
                            $ratesIds[$rateId] = ['period' => $period];
                        }
                    }

                    if (count($ratesIds) > 0) {
                        $cliente->rateplans($period)->attach($ratesIds);
                    }


//                    $cliente->rateplans()->wherePivot('period', $period)->sync($ratesIds);
//                    $cliente->markup_rateplans()->wherePivot('period', $period)->detach($ratesIds);
                }


            }
        });

        return Response::json(['success' => true]);

    }

    public function storeAll(Request $request)
    {


        DB::transaction(function () use ($request) {
            $hotel_id = $request->input('hotel_id');
            $client_id = $request->input('client_id');
            $period = $request->input('period');
            $market = $request->input('market');
            $markup = $request->input('porcentage');
            $aplicaTarifa = $request->input('aplicaTarifa');
            $query = $request->input('query');


            if ($aplicaTarifa == "1") {

                $client_rate_ids = ClientRatePlan::where([
                    'client_id' => $client_id,
                    'period' => $period
                ])->pluck('rate_plan_id');

                $rate_plans = RatesPlans::select('id', 'name')->where('hotel_id', $hotel_id)->whereNotIn('id',
                    $client_rate_ids)->get();

                foreach ($rate_plans as $rate_plan) {

                    $this->bloquearTarifaClientes($rate_plan->id, $period, $client_id);
                }
            } else {


                $rate_plans = RatesPlans::select('id', 'name')->where('hotel_id', $hotel_id)->get();


                $clientes = $this->clienteAll($period, $hotel_id, $market, $query);

                foreach ($clientes as $cliente) {

                    $ratesPeriod = $cliente->rateplans($period)->pluck('rates_plans.id')->toArray();


                    $ratesIds = [];
                    foreach ($rate_plans as $rate_plan) {
                        if (!in_array($rate_plan->id, $ratesPeriod)) {
                            $ratesIds[$rate_plan->id] = ['period' => $period];
                        }
                    }


                    if (count($ratesIds) > 0) {
                        $cliente->rateplans($period)->attach($ratesIds);
                    }

                }

//                  Codigo que hay que revisar ....
//                $rate_plans = RatesPlans::select('id', 'name')->where('hotel_id', $hotel_id)->get();
//                $ratesIds = [];
//                foreach ($rate_plans as $rate_plan) {
//                    $ratesIds[$rate_plan->id] = ['period' => $period ];
//                }
//
//                $clientes = $this->clienteAll($period,$hotel_id,$market,$query);
//
//                foreach ($clientes as $cliente) {
//
//                    $cliente->rateplans()->wherePivot('period', $period)->sync($ratesIds);
////                    $cliente->markup_rateplans()->wherePivot('period', $period)->detach($ratesIds);
//
//
//                }


            }
        });

        return Response::json(['success' => true]);
    }


    public function deleteMarkupRatePlans($client_id, $rate_plan_id, $period)
    {

        $ratesMarkup = MarkupRatePlan::where('client_id', $client_id)->where('rate_plan_id',
            $rate_plan_id)->where('period', $period)->first();
        if (is_object($ratesMarkup)) {
            $ratesMarkup->delete();
        }
    }

    public function inverse(Request $request)
    {

        DB::transaction(function () use ($request) {

            $client_id = $request->input('client_id');
            $hotel_id = $request->input('hotel_id');
            $period = $request->input('period');
            $rate_plan_id = $request->input('rate_plan_id');
            $aplicaTarifa = $request->input('aplicaTarifa');
            $market = $request->input('market');
            $query = $request->input('query');


            if ($aplicaTarifa == 1) {
                ClientRatePlan::where(['period' => $period, 'client_id' => $client_id, 'rate_plan_id' => $rate_plan_id])
                    ->each(function ($rate) {
                        $rate->delete();
                    });
            } else {

                // bloqueamos todos los clientes del filtro

                $clientes = $this->clienteAll($period, $hotel_id, $market, $query);

                foreach ($clientes as $cliente) {
                    $cliente->rate_plans()->wherePivot('period', '=', $period)->detach($rate_plan_id);
                }

            }

        });

        return Response::json(['success' => true]);
    }

    public function inverseAll(Request $request)
    {
        DB::transaction(function () use ($request) {

            $hotel_id = $request->input('hotel_id');
            $client_id = $request->input('client_id');
            $hotel_id = $request->input('hotel_id');
            $period = $request->input('period');
            $aplicaTarifa = $request->input('aplicaTarifa');
            $market = $request->input('market');
            $query = $request->input('query');


            if ($aplicaTarifa == 1) {

                $clientRatePlans = ClientRatePlan::whereHas('ratePlan', function ($query) use ($hotel_id) {
                    $query->where('hotel_id', $hotel_id);
                })->where('period', $period)->where('client_id', $client_id)->get();


                foreach ($clientRatePlans as $clientRatePlan) {
                    ClientRatePlan::where(['period' => $period, 'client_id' => $client_id, 'rate_plan_id' => $clientRatePlan->rate_plan_id])
                        ->each(function ($rate) {
                            $rate->delete();
                        });
                }
            } else {


                $rate_plans = RatesPlans::select('id', 'name')->where('hotel_id', $hotel_id)->get();
                $ratesIds = [];
                foreach ($rate_plans as $rate_plan) {
//                    $ratesIds[$rate_plan->id] = ['period' => $period ];
                    $ratesIds[] = $rate_plan->id;
                }

                // bloqueamos todos los clientes del filtro

                $clientes = $this->clienteAll($period, $hotel_id, $market, $query);

                foreach ($clientes as $cliente) {
                    $cliente->rate_plans()->wherePivot('period', '=', $period)->detach($ratesIds);
                }
            }

        });

        return Response::json(['success' => true]);
    }


    public function destroy(Request $request)
    {
        $id = $request->input('id');

        $client_rate_plan = ClientRatePlan::where('id', $id)->delete();

        return Response::json(['success' => true,]);
    }

    public function update(Request $request)
    {

        $rate_plan_id = $request->input('rate_plan_id');
        $client_id = $request->input('client_id');
        $markup = $request->input('markup');
        $period = $request->input('period');


        $ratesMarkup = MarkupRatePlan::where('client_id', $client_id)->where('rate_plan_id',
            $rate_plan_id)->where('period', $period)->first();

        if (is_object($ratesMarkup)) {
            $ratesMarkup->markup = $markup;
            $ratesMarkup->save();
        } else {
            $ratesMarkup = new MarkupRatePlan();
            $ratesMarkup->client_id = $client_id;
            $ratesMarkup->rate_plan_id = $rate_plan_id;
            $ratesMarkup->period = $period;
            $ratesMarkup->markup = $markup;
            $ratesMarkup->save();
        }

        /*
        $data = $request->input('data');

        $hotel_client = ClientRatePlan::find($data['id']);
        $hotel_client->markup = $data['markup'];
        $hotel_client->save();
        */
        return Response::json(['success' => true]);
    }

    // private function insertRatePlans($hotel_id, $client_id, $markup, $period)
    // {
    //     $id_rate_plans = RatesPlans::where('hotel_id', $hotel_id)->get();
    //     $client_rate_plan_save = [];
    //     $result = [];
    //     foreach ($id_rate_plans as $key => $value) {
    //         $client_rate_plan_save[] = [
    //             'rate_plan_id' => $value->id,
    //             'client_id' => $client_id,
    //             'markup' => $markup,
    //             'period' => $period,
    //             'created_at' => date("Y-m-d H:i:s"),
    //             'updated_at' => date("Y-m-d H:i:s")
    //         ];
    //     }
    //     if (!empty($client_rate_plan_save)) {
    //         $result = ClientRatePlan::insert($client_rate_plan_save);
    //     }
    //     return $result;
    // }
}

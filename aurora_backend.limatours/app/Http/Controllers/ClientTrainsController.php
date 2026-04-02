<?php

namespace App\Http\Controllers;

use App\Client;
use App\Markup;

//use App\Http\Traits\Services;
use App\MarkupTrain;
use App\TrainClient;
use App\TrainClientRatePlan;
use App\Http\Traits\ClientTrains;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ClientTrainsController extends Controller
{
    use ClientTrains;
//    use ClientTrains, Services;

    public function index(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $client_id = $request->input('client_id');
        $period = $request->input('period');

        $service_client_frontend = [];

        $service_client_database = TrainClient::select(['id', 'train_template_id', 'client_id', 'period'])->
        whereHas('train_template', function ($query) use ($querySearch) {
            $query->where('name', 'like', '%' . $querySearch . '%');
        })->where(['client_id' => $client_id, 'period' => $period]);

        $count = $service_client_database->count();

        if ($paging === 1) {
            $service_client_database = $service_client_database->take($limit)->get();
        } else {
            $service_client_database = $service_client_database->skip($limit * ($paging - 1))->take($limit)->get();
        }

        if ($count > 0) {
            for ($j = 0; $j < count($service_client_database); $j++) {
                $service_client_frontend[$j]["train_client_id"] = $service_client_database[$j]["id"];
                $service_client_frontend[$j]["client_id"] = $service_client_database[$j]["client_id"];
                $service_client_frontend[$j]["aurora_code"] = $service_client_database[$j]["train_template"]["aurora_code"];
                $service_client_frontend[$j]["name"] = $service_client_database[$j]["train_template"]["name"];
                $service_client_frontend[$j]["train_template_id"] = $service_client_database[$j]["train_template_id"];
                $service_client_frontend[$j]["selected"] = false;
            }
        }

        $data = [
            'data' => $service_client_frontend,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

//    public function selectPeriod(Request $request)
//    {
//        $client_id = $request->input('client_id');
//        $markups = Markup::select('id', 'period', 'service')->where(['status' => 1, 'client_id' => $client_id])->get();
//        $result = [];
//        foreach ($markups as $markup) {
//            array_push($result,
//                ['text' => $markup->period, 'value' => $markup->id, 'porcen_service' => $markup->service]);
//        }
//        return Response::json(['success' => true, 'data' => $result]);
//    }

    public function store(Request $request)
    {
        $data = $request->input('data');
        $period = $request->input('period');

        $store_service_client = $this->storeTrainClient($period, $data['client_id'], $data['train_template_id']);

        if (!empty($store_service_client) && $store_service_client->count() > 0) {
            $this->insertTrainRatePlans($data['train_template_id'], $data['client_id'], $period);
        }

        return Response::json(['success' => true, 'train_client_id' => $store_service_client->id]);
    }

//    public function storeAll(Request $request)
//    {
//        $client_id = $request->input('client_id');
//        $markup = $request->input('porcentage');
//        $period = $request->input('period');
//
//        $this->storeAllService($client_id, $markup, $period);
//
//        return Response::json(['success' => true]);
//    }

    public function inverse(Request $request)
    {
        $data = $request->input('data');
        $client_id = $data['client_id'];
        $period = $request->input('period');
        $train_template_id = $data['train_template_id'];

        DB::transaction(function () use ($client_id, $period, $train_template_id) {
            TrainClientRatePlan::where(['client_id' => $client_id, 'period' => $period])
                ->whereHas('trainRate', function ($query) use ($train_template_id) {
                    $query->where('train_template_id', $train_template_id);
                })->delete();
        });

        TrainClient::where('id', $data['train_client_id'])->delete();

        return Response::json(['success' => true]);
    }

//
//    public function inverseAll(Request $request)
//    {
//        $client_id = $request->input('client_id');
//        $period = $request->input('period');
//
//        DB::transaction(function () use ($client_id, $period) {
//            ServiceClientRatePlan::where(['client_id' => $client_id, 'period' => $period])->delete();
//        });
//
//        DB::transaction(function () use ($client_id, $period) {
//            ServiceClient::where(['client_id' => $client_id, 'period' => $period])->delete();
//        });
//
//        return Response::json(['success' => true]);
//    }

    public function update(Request $request)
    {
        $train_template_id = $request->input('train_template_id');
        $client_id = $request->input('client_id');
        $markup = $request->input('markup');
        $period = $request->input('period');

        DB::transaction(function () use ($train_template_id, $markup, $period, $client_id) {
            $hotelMarkup = MarkupTrain::where('client_id', $client_id)->where('train_template_id',
                $train_template_id)->where('period',
                $period)->first();
            if (is_object($hotelMarkup)) {
                $hotelMarkup->markup = $markup;
                $hotelMarkup->save();
            } else {
                $hotelMarkup = new MarkupTrain();
                $hotelMarkup->client_id = $client_id;
                $hotelMarkup->train_template_id = $train_template_id;
                $hotelMarkup->period = $period;
                $hotelMarkup->markup = $markup;
                $hotelMarkup->save();
            }
        });

        return Response::json(['success' => true]);
    }

    public function updateAllClients(Request $request)
    {
        $train_template_id = $request->input('train_template_id');
        $markup = $request->input('markup');
        $period = $request->input('period');
        $market = $request->input('market');
        try {
            DB::transaction(function () use ($train_template_id, $markup, $period, $market) {
                $client_database = Client::select(['id', 'code', 'name', 'market_id'])
                    ->with([
                        'markups' => function ($query) use ($period) {
                            $query->where('period', $period)->where('status', 1);
                        }
                    ])
                    ->whereHas('markups', function ($query) use ($period) {
                        $query->where('period', $period)->where('status', 1);
                    })->where('status', 1);

                if ($market) {
                    $client_database->where('market_id', $market);
                }

                $service_client_database = TrainClient::select(['client_id', 'period'])->where([
                    'train_template_id' => $train_template_id,
                    'period' => $period
                ]);

                $service_client_ids = $service_client_database->pluck('client_id');

                if ($service_client_database->count() > 0) {
                    $client_database->whereNotIn('id', $service_client_ids);
                }

                $client_database->chunk(100, function ($clients) use ($markup, $train_template_id, $period) {
                    foreach ($clients as $client) {
                        DB::table('markup_trains')->updateOrInsert(
                            [
                                'period' => $period,
                                'train_template_id' => $train_template_id,
                                'client_id' => $client->id
                            ],
                            [
                                'period' => $period,
                                'markup' => $markup,
                                'train_template_id' => $train_template_id,
                                'client_id' => $client->id,
                                'created_at' => Carbon::now('America/Lima')->format('Y-m-d H:i:s'),
                                'updated_at' => Carbon::now('America/Lima')->format('Y-m-d H:i:s')
                            ]
                        );
                    }
                });

            });
            return Response::json(['success' => true]);
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'data' => $exception->getMessage()]);
        }
    }


//    public function getClientsByPeriod(Request $request)
//    {
//        $service_id = $request->input('service_id');
//        $period = $request->input('period');
//
//        $service_client_database = ServiceClient::select(['id', 'service_id', 'client_id', 'period'])
//            ->with('client')
//            ->where(['service_id' => $service_id, 'period' => $period])->get();
//
//        $service_client_frontend = [];
//
//        for ($i = 0; $i < $service_client_database->count(); $i++) {
//            $service_client_frontend[$i]["service_client_rate_plan_id"] = "";
//            $service_client_frontend[$i]["id"] = $service_client_database[$i]["client"]["id"];
//            $service_client_frontend[$i]["name"] = $service_client_database[$i]["client"]["name"];
//            $service_client_frontend[$i]["selected"] = false;
//        }
//        $data = [
//            'data' => $service_client_frontend,
//            'success' => true
//        ];
//
//        return Response::json($data);
//    }
//
    public function clientLocked(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $train_template_id = $request->input('train_template_id');
        $period = $request->input('period');

        $service_client_frontend = [];

        $service_client_database = TrainClient::select(['id', 'train_template_id', 'client_id', 'period'])->
        whereHas('client', function ($query) use ($querySearch) {
            $query->where('name', 'like', '%' . $querySearch . '%');
            $query->orWhere('code', 'like', '%' . $querySearch . '%');
        })->where(['train_template_id' => $train_template_id, 'period' => $period]);

        $count = $service_client_database->count();

        if ($paging === 1) {
            $service_client_database = $service_client_database->take($limit)->get();
        } else {
            $service_client_database = $service_client_database->skip($limit * ($paging - 1))->take($limit)->get();
        }

        if ($count > 0) {
            for ($j = 0; $j < count($service_client_database); $j++) {
                $service_client_frontend[$j]["markup"] = $service_client_database[$j]["markup"];
                $service_client_frontend[$j]["train_client_id"] = $service_client_database[$j]["id"];
                $service_client_frontend[$j]["client_id"] = $service_client_database[$j]["client_id"];
                $service_client_frontend[$j]["name"] = $service_client_database[$j]["client"]["code"] . ' - ' . $service_client_database[$j]["client"]["name"];
                $service_client_frontend[$j]["train_template_id"] = $service_client_database[$j]["train_template_id"];
                $service_client_frontend[$j]["selected"] = false;
            }
        }

        $data = [
            'data' => $service_client_frontend,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    public function storeClientAll(Request $request)
    {
        $train_template_id = $request->input('train_template_id');
        $markup = $request->input('porcentage');
        $period = $request->input('period');
        $market = $request->input('market');

        $this->storeAllClient($train_template_id, $markup, $period, $market);

        return Response::json(['success' => true]);
    }

    public function storeAllClient($train_template_id, $markup, $period, $market)
    {
        DB::transaction(function () use ($train_template_id, $markup, $period, $market) {
            $service_client_database = TrainClient::select(['client_id'])->where([
                'train_template_id' => $train_template_id,
                'period' => $period
            ]);

            $client_database = Client::select(['id'])->where(['status' => 1]);

            if ($market) {
                $client_database->where('market_id', $market);
            }

            if ($service_client_database->count() > 0) {
                $client_database->whereNotIn('id', $service_client_database);
            }

            $client_database = $client_database->get();
            $service_client = [];
            $now = Carbon::now()->toDateTimeString();
            foreach ($client_database as $key => $client) {
                $service_client[] = [
                    'period' => $period,
                    'client_id' => $client['id'],
                    'train_template_id' => $train_template_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $this->deleteMarkupTrain($client['id'], $train_template_id, $period);
                $this->deleteMarkupRatePlansTrain($client['id'], $train_template_id, $period);
                $this->deleteClientRatePlansTrain($client['id'], $period, $train_template_id);
            }

            TrainClient::insert($service_client);

        });

        return true;
    }

    public function inverseClientAll(Request $request)
    {
        $train_template_id = $request->input('train_template_id');
        $period = $request->input('period');
        DB::transaction(function () use ($train_template_id, $period) {
            TrainClient::where(['train_template_id' => $train_template_id, 'period' => $period])->delete();
        });
        return Response::json(['success' => true]);
    }
}

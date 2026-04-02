<?php

namespace App\Http\Controllers;

use App\Client;
use App\Markup;
use App\Service;
use Carbon\Carbon;
use App\MarkupService;
use App\ServiceClient;
use Illuminate\Http\Request;
use App\Http\Traits\Services;
use App\ServiceClientRatePlan;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\ClientServices;
use App\Http\Traits\JobStatusRegister;
use Illuminate\Support\Facades\Response;
use App\Jobs\SaveBlockedServicesByDestination;

/**
 * Class ClientServicesController
 * @package App\Http\Controllers
 */
class ClientServicesController extends Controller
{
    use ClientServices, Services, JobStatusRegister;

    public function index(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $client_id = $request->input('client_id');
        $period = $request->input('period');
        $region_id = $request->input('region_id');

        $service_client_frontend = [];

        $service_client_database = ServiceClient::select(['id', 'service_id', 'client_id', 'period'])->
        whereHas('service', function ($query) use ($querySearch) {
            $query->where('aurora_code', 'like', $querySearch.'%');
        })->where([
            'client_id' => $client_id,
            'period' => $period,
            'business_region_id' => $region_id
        ]);

        $count = $service_client_database->count();

        if ($paging === 1) {
            $service_client_database = $service_client_database->take($limit)->get();
        } else {
            $service_client_database = $service_client_database->skip($limit * ($paging - 1))->take($limit)->get();
        }

        if ($count > 0) {
            for ($j = 0; $j < count($service_client_database); $j++) {
                $service_client_frontend[$j]["service_client_id"] = $service_client_database[$j]["id"];
                $service_client_frontend[$j]["client_id"] = $service_client_database[$j]["client_id"];
                $service_client_frontend[$j]["aurora_code"] = $service_client_database[$j]["service"]["aurora_code"];
                $service_client_frontend[$j]["name"] = $service_client_database[$j]["service"]["name"];
                $service_client_frontend[$j]["service_id"] = $service_client_database[$j]["service_id"];
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

    public function selectPeriod(Request $request)
    {
        $client_id = $request->input('client_id');
        $region_id = $request->input('region_id');

        $markups = Markup::select('id', 'period', 'service')->where([
            'status' => 1,
            'client_id' => $client_id,
            'business_region_id' => $region_id
        ])->get();

        $result = [];
        foreach ($markups as $markup) {
            array_push($result,
                ['text' => $markup->period, 'value' => $markup->id, 'porcen_service' => $markup->service]);
        }
        return Response::json(['success' => true, 'data' => $result]);
    }

    public function store(Request $request)
    {
        $data = $request->input('data');
        $period = $request->input('period');

        if($request->has('region_id')){
            $region_id = $request->input('region_id');
        }else{
            $region_id = null;
        }

        $store_service_client = $this->storeServiceClient($period, $data['client_id'], $data['service_id'], $region_id);

        if (!empty($store_service_client) && $store_service_client->count() > 0) {
            $this->insertServiceRatePlans($data['service_id'], $data['client_id'], $period);
        }

        return Response::json(['success' => true, 'service_client_id' => $store_service_client->id]);
    }

    public function storeAll(Request $request)
    {
        $client_id = $request->input('client_id');
        $markup = $request->input('porcentage');
        $period = $request->input('period');

        $this->storeAllService($client_id, $markup, $period);

        return Response::json(['success' => true]);
    }

    public function inverse(Request $request)
    {
        $data = $request->input('data');
        $client_id = $data['client_id'];
        $period = $request->input('period');
        $service_id = $data['service_id'];

        DB::transaction(function () use ($client_id, $period, $service_id) {
            ServiceClientRatePlan::where(['client_id' => $client_id, 'period' => $period, 'deleted_at' => null])
                ->whereHas('serviceRate', function ($query) use ($service_id) {
                    $query->where('service_id', $service_id);
                })->delete();
        });

        ServiceClient::find($data['service_client_id'])->delete();

        return Response::json(['success' => true]);
    }

    public function inverseAll(Request $request)
    {
        $client_id = $request->input('client_id');
        $period = $request->input('period');

        DB::transaction(function () use ($client_id, $period) {
            ServiceClientRatePlan::where(['client_id' => $client_id, 'period' => $period])->delete();
        });

        DB::transaction(function () use ($client_id, $period) {
            ServiceClient::where(['client_id' => $client_id, 'period' => $period])->each(function ($serviceClient) {
                $serviceClient->delete();
            });
        });

        return Response::json(['success' => true]);
    }

    public function update(Request $request)
    {
        $service_id = $request->input('service_id');
        $client_id = $request->input('client_id');
        $markup = $request->input('markup');
        $period = $request->input('period');

        DB::transaction(function () use ($service_id, $markup, $period, $client_id) {
            $hotelMarkup = MarkupService::where('client_id', $client_id)->where('service_id',
                $service_id)->where('period',
                $period)->first();
            if (is_object($hotelMarkup)) {
                $hotelMarkup->markup = $markup;
                $hotelMarkup->save();
            } else {
                $hotelMarkup = new MarkupService();
                $hotelMarkup->client_id = $client_id;
                $hotelMarkup->service_id = $service_id;
                $hotelMarkup->period = $period;
                $hotelMarkup->markup = $markup;
                $hotelMarkup->save();
            }
        });

        return Response::json(['success' => true]);
    }

    public function updateAllClients(Request $request)
    {
        $service_id = $request->input('service_id');
        $markup = $request->input('markup');
        $period = $request->input('period');
        $market = $request->input('market');
        try {
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

            $service_client_database = ServiceClient::select(['client_id', 'period'])->where([
                'service_id' => $service_id,
                'period' => $period
            ]);

            $service_client_ids = $service_client_database->pluck('client_id');

            if ($service_client_database->count() > 0) {
                $client_database->whereNotIn('id', $service_client_ids);
            }

            $client_database->chunk(100, function ($clients) use ($markup, $service_id, $period) {
                DB::transaction(function () use ($clients, $markup, $service_id, $period) {
                    $now = Carbon::now('America/Lima')->format('Y-m-d H:i:s');
                    foreach ($clients as $client) {
                        DB::table('markup_services')->updateOrInsert(
                            [
                                'period' => $period,
                                'service_id' => $service_id,
                                'client_id' => $client->id
                            ],
                            [
                                'markup' => $markup,
                                'updated_at' => $now
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


    public function getClientsByPeriod(Request $request)
    {
        $service_id = $request->input('service_id');
        $period = $request->input('period');

        $service_client_database = ServiceClient::select(['id', 'service_id', 'client_id', 'period'])
            ->with('client')
            ->where(['service_id' => $service_id, 'period' => $period])->get();

        $service_client_frontend = [];

        for ($i = 0; $i < $service_client_database->count(); $i++) {
            $service_client_frontend[$i]["service_client_rate_plan_id"] = "";
            $service_client_frontend[$i]["id"] = $service_client_database[$i]["client"]["id"];
            $service_client_frontend[$i]["name"] = $service_client_database[$i]["client"]["name"];
            $service_client_frontend[$i]["selected"] = false;
        }
        $data = [
            'data' => $service_client_frontend,
            'success' => true
        ];

        return Response::json($data);
    }

    public function clientLocked(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $service_id = $request->input('service_id');
        $period = $request->input('period');

        $service_client_frontend = [];

        $service_client_database = ServiceClient::select(['id', 'service_id', 'client_id', 'period'])->
        whereHas('client', function ($query) use ($querySearch) {
            $query->where('name', 'like', '%'.$querySearch.'%');
            $query->orWhere('code', 'like', '%'.$querySearch.'%');
        })->where(['service_id' => $service_id, 'period' => $period]);

        $count = $service_client_database->count();

        if ($paging === 1) {
            $service_client_database = $service_client_database->take($limit)->get();
        } else {
            $service_client_database = $service_client_database->skip($limit * ($paging - 1))->take($limit)->get();
        }

        if ($count > 0) {
            for ($j = 0; $j < count($service_client_database); $j++) {
                $service_client_frontend[$j]["markup"] = $service_client_database[$j]["markup"];
                $service_client_frontend[$j]["service_client_id"] = $service_client_database[$j]["id"];
                $service_client_frontend[$j]["client_id"] = $service_client_database[$j]["client_id"];
                $service_client_frontend[$j]["name"] = $service_client_database[$j]["client"]["code"].' - '.$service_client_database[$j]["client"]["name"];
                $service_client_frontend[$j]["service_id"] = $service_client_database[$j]["service_id"];
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
        $service_id = $request->input('service_id');
        $markup = $request->input('porcentage');
        $period = $request->input('period');
        $market = $request->input('market');

        $this->storeAllClient($service_id, $markup, $period, $market);

        return Response::json(['success' => true]);
    }

    public function storeAllClient($service_id, $markup, $period, $market)
    {
        DB::transaction(function () use ($service_id, $markup, $period, $market) {
            $service_client_database = ServiceClient::select(['client_id'])->where([
                'service_id' => $service_id,
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
                    'service_id' => $service_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
//                $service_client = new ServiceClient();
//                $service_client->period = $period;
//                $service_client->client_id = $client['id'];
//                $service_client->service_id = $service_id;
//                $service_client->save();
                $this->deleteMarkupService($client['id'], $service_id, $period);
                $this->deleteMarkupRatePlansService($client['id'], $service_id, $period);
                $this->deleteClientRatePlansService($client['id'], $period, $service_id);
            }

            ServiceClient::insert($service_client);

        });

        return true;
    }

    public function inverseClientAll(Request $request)
    {
        $service_id = $request->input('service_id');
        $period = $request->input('period');
        DB::transaction(function () use ($service_id, $period) {
            ServiceClient::where(['service_id' => $service_id, 'period' => $period])->delete();
        });
        return Response::json(['success' => true]);
    }


    public function storeByFilter(Request $request)
    {
        $client_id = $request->input('client_id');
        $filter_destiny = $request->input('filter');
        $period = $request->input('period');
        $region_id = $request->input('region_id');

        $country = [];
        $states = [];
        $cities = [];
        $zones = [];
        $job_status = $this->checkStatusJobExecute('SaveBlockedServicesByDestination', $client_id, null, $period);
        if (!$job_status) {
            if (count($filter_destiny) > 0) {
                foreach ($filter_destiny as $item) {
                    $explore_code = explode(',', $item['code']);
                    if (count($explore_code) === 1) {
                        $country[] = trim($explore_code[0]);
                    }

                    if (count($explore_code) === 2) {
                        $states[] = trim($explore_code[1]);
                    }

                    if (count($explore_code) === 3) {
                        $cities[] = trim($explore_code[2]);
                    }

                    if (count($explore_code) === 4) {
                        $zones[] = trim($explore_code[3]);
                    }
                }

                $this->store_job('SaveBlockedServicesByDestination', $client_id, $request->all(), $client_id, $period);
                SaveBlockedServicesByDestination::dispatch($client_id, $period, $country, $states, $cities, $zones, $region_id);
            }
        } else {
            return Response::json([
                'success' => false,
                'message' => 'Ya se encuentra procesando una solicitud, por favor espere un momento.'
            ]);
        }

        return Response::json(['success' => true]);
    }
}

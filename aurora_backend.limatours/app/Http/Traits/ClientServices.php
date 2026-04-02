<?php

namespace App\Http\Traits;

use App\Client;
use App\MarkupService;
use App\Service;
use App\ServiceClient;
use App\ServiceClientRatePlan;
use App\ServiceMarkupRatePlan;
use App\ServiceRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait ClientServices
{
    private function insertServiceRatePlans($service_id, $client_id, $period)
    {
        DB::transaction(function () use ($service_id, $client_id, $period) {
            $rate_plans = ServiceRate::where('service_id', $service_id)->get();
            $client_rate_plan_save = [];
            $result = [];
            foreach ($rate_plans as $key => $value) {
                $client_rate_plan_save[] = [
                    'service_rate_id' => $value->id,
                    'client_id' => $client_id,
                    'period' => $period,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s")
                ];
            }
            if (!empty($client_rate_plan_save)) {
                $result = ServiceClientRatePlan::insert($client_rate_plan_save);
            }
        });
        return true;
    }

    public function storeAllService($client_id, $markup, $period)
    {
        DB::transaction(function () use ($client_id, $markup, $period) {
            $service_client_database = ServiceClient::select(['client_id'])->where([
                'client_id' => $client_id,
                'period' => $period
            ]);

            $services_database = Service::select(['id']);

            if ($service_client_database->count() > 0) {
                $services_database->whereNotIn('id', $service_client_database);
            }

            $services_database = $services_database->get();

            foreach ($services_database as $key => $service) {
                $store_service_client = $this->storeServiceClient($period, $client_id, $service['id']);
                if (!empty($store_service_client) && $store_service_client->count() > 0) {
                    $this->insertServiceRatePlans($service['id'], $client_id, $markup, $period);
                }
            }

        });
        return true;
    }

    public function storeAllServiceClient($service_id)
    {

        $clients = Client::with('markups')->get();
        if (!empty($clients) && $clients->count() > 0) {
            foreach ($clients as $key => $client) {
                if (!empty($client->markups) && $client->markups->count() > 0) {
                    foreach ($client->markups as $value) {
                        $this->storeServiceClient($value->period, $client->id, $service_id);
                    }
                }
            }
        }
    }


    public function storeServiceClient($period, $client_id, $service_id, $region_id = null)
    {
        DB::beginTransaction();
        try {

            if($region_id == null){
                $region = Service::with([
                    'serviceOrigin' => function($query) {
                        $query->whereNull('deleted_at')
                            ->with([
                                'country' => function($query) {
                                    $query->whereNull('deleted_at')
                                        ->with([
                                            'businessRegions' => function($query) {
                                                $query->whereNull('business_region_country.deleted_at')
                                                    ->whereNull('business_regions.deleted_at');
                                            }
                                        ]);
                                }
                            ]);
                    }
                ])
                ->where('id', $service_id)
                ->whereNull('deleted_at')
                ->first()
                ->serviceOrigin
                ->first()
                ->country
                ->businessRegions
                ->first();

                $region_id = $region->id;
            }

            $service_client = new ServiceClient();
            $service_client->period = $period;
            $service_client->client_id = $client_id;
            $service_client->service_id = $service_id;
            $service_client->business_region_id = $region_id;
            $service_client->save();
            $this->deleteMarkupService($client_id, $service_id, $period);
            $this->deleteMarkupRatePlansService($client_id, $service_id, $period);
            $this->deleteClientRatePlansService($client_id, $period, $service_id);
            DB::commit();
            return $service_client;
        } catch (\Exception $e) {
            DB::rollback();
        } catch (\Throwable $e) {
            DB::rollback();
        }
    }

    public function deleteMarkupService($client_id, $service_id, $period)
    {
        DB::transaction(function () use ($client_id, $service_id, $period) {
            $serviceMarkup = MarkupService::where('client_id', $client_id)->where('service_id',
                $service_id)->where('period',
                $period)->first();
            if (is_object($serviceMarkup)) {
                $serviceMarkup->delete();
            }
        });
    }

    public function deleteMarkupRatePlansService($client_id, $service_id, $period)
    {
        DB::transaction(function () use ($client_id, $service_id, $period) {
            $client_rate_ids = ServiceMarkupRatePlan::where([
                'client_id' => $client_id,
                'period' => $period
            ])->pluck('service_rate_id');

            $rate_plans = ServiceRate::select('id', 'name')->where('service_id', $service_id)->whereIn('id',
                $client_rate_ids)->get();

            foreach ($rate_plans as $rate) {
                $ratesMarkup = ServiceMarkupRatePlan::where('client_id', $client_id)->where('service_rate_id',
                    $rate->id)->where('period', $period)->first();
                if (is_object($ratesMarkup)) {
                    $ratesMarkup->delete();
                }
            }
        });
    }

    public function deleteClientRatePlansService($client_id, $period, $service_id)
    {
        DB::transaction(function () use ($client_id, $service_id, $period) {
            ServiceClientRatePlan::where(['client_id' => $client_id, 'period' => $period])
                ->whereHas('service_rate', function ($query) use ($service_id) {
                    $query->where('service_id', $service_id);
                })->delete();
        });
    }


    public function storeServiceClientByDestiny($client_id, $period, $country, $states, $cities, $zones)
    {

        DB::transaction(function () use ($client_id, $period, $country, $states, $cities, $zones) {
            $services = Service::select([
                'id',
                'aurora_code',
            ]) //TODO filtrar Destino -> pais
            ->when(count($country) > 0, function ($query) use ($country) {
                return $query->whereHas('serviceDestination', function ($query) use ($country) {
                    $query->whereNotIn('country_id', $country);
                });
            })
                //TODO filtrar Destino -> estado
                ->when(count($states) > 0, function ($query) use ($states) {
                    return $query->whereHas('serviceDestination', function ($query) use ($states) {
                        $query->whereNotIn('state_id', $states);
                    });
                })
                //TODO filtrar Destino -> ciudad
                ->when(count($cities) > 0, function ($query) use ($cities) {
                    return $query->whereHas('serviceDestination', function ($query) use ($cities) {
                        $query->whereNotIn('city_id', $cities);
                    });
                })
                //TODO filtrar Destino -> zona
                ->when(count($zones) > 0, function ($query) use ($zones) {
                    return $query->whereHas('serviceDestination', function ($query) use ($zones) {
                        $query->whereNotIn('zone_id', $zones);
                    });
                })->whereDoesntHave('service_clients', function ($query) use ($client_id,$period) {
                    $query->where('client_id', $client_id);
                    $query->where('period', $period);
                })->get();
            foreach ($services as $key => $service) {
                $service_client = new ServiceClient();
                $service_client->period = $period;
                $service_client->client_id = $client_id;
                $service_client->service_id = $service->id;
                $service_client->save();
                $this->deleteMarkupService($client_id, $service->id, $period);
                $this->deleteMarkupRatePlansService($client_id, $service->id, $period);
                $this->deleteClientRatePlansService($client_id, $period, $service->id);
            }


//            return $service_client;
        });
    }

    private function block_in_clients( $service_id, $exclusive_client_id ){


        DB::transaction(function () use ($service_id, $exclusive_client_id) {

            Client::where('id', '!=', $exclusive_client_id)
                ->where('status', 1)
                ->chunk(500, function ($clients) use ($service_id, $exclusive_client_id) {
                    foreach ( $clients as $client ){
                        $years = 2;
                        for ($i=0; $i<$years; $i++) {
                            $year = Carbon::now()->year + $i;

                            $new_service_client = new ServiceClient();
                            $new_service_client->period = $year;
                            $new_service_client->client_id = $client->id;
                            $new_service_client->service_id = $service_id;
                            $new_service_client->save();
                        }
                    }
                });
        });

        return true;
    }

}

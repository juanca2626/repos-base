<?php

namespace App\Jobs;

use App\MarkupService;
use App\Service;
use App\ServiceClient;
use App\ServiceClientRatePlan;
use App\ServiceMarkupRatePlan;
use App\ServiceRate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SaveBlockedServicesByDestination implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $client_id;
    public $period;
    public $country;
    public $states;
    public $cities;
    public $zones;
    public $region_id;

    /**
     * Create a new job instance.
     *
     * @param $client_id
     * @param $period
     * @param $country
     * @param $states
     * @param $cities
     * @param $zones
     */
    public function __construct($client_id, $period, $country, $states, $cities, $zones, $region_id)
    {
        $this->client_id = $client_id;
        $this->period = $period;
        $this->country = $country;
        $this->states = $states;
        $this->cities = $cities;
        $this->zones = $zones;
        $this->region_id = $region_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::transaction(function () {
            $services = Service::select([
                'id',
                'aurora_code',
            ]) //TODO filtrar Destino -> pais
            ->when(count($this->country) > 0, function ($query) {
                return $query->whereHas('serviceDestination', function ($query) {
                    $query->whereNotIn('country_id', $this->country);
                });
            })
                //TODO filtrar Destino -> estado
                ->when(count($this->states) > 0, function ($query) {
                    return $query->whereHas('serviceDestination', function ($query) {
                        $query->whereNotIn('state_id', $this->states);
                    });
                })
                //TODO filtrar Destino -> ciudad
                ->when(count($this->cities) > 0, function ($query) {
                    return $query->whereHas('serviceDestination', function ($query) {
                        $query->whereNotIn('city_id', $this->cities);
                    });
                })
                //TODO filtrar Destino -> zona
                ->when(count($this->zones) > 0, function ($query) {
                    return $query->whereHas('serviceDestination', function ($query) {
                        $query->whereNotIn('zone_id', $this->zones);
                    });
                })->get();

            foreach ($services as $key => $service) {
                $service_client = new ServiceClient();
                $service_client->period = $this->period;
                $service_client->client_id = $this->client_id;
                $service_client->service_id = $service->id;
                $service_client->business_region_id = $this->region_id;
                $service_client->save();
                $this->deleteMarkupService($this->client_id, $service->id, $this->period);
                $this->deleteMarkupRatePlansService($this->client_id, $service->id, $this->period);
                $this->deleteClientRatePlansService($this->client_id, $this->period, $service->id);
            }
        });

    }

    /**
     * @param $client_id
     * @param $service_id
     * @param $period
     */
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

    /**
     * @param $client_id
     * @param $service_id
     * @param $period
     */
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

    /**
     * @param $client_id
     * @param $period
     * @param $service_id
     */
    public function deleteClientRatePlansService($client_id, $period, $service_id)
    {
        DB::transaction(function () use ($client_id, $service_id, $period) {
            ServiceClientRatePlan::where(['client_id' => $client_id, 'period' => $period])
                ->whereHas('service_rate', function ($query) use ($service_id) {
                    $query->where('service_id', $service_id);
                })->delete();
        });
    }

}

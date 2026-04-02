<?php

namespace App\Http\Traits;

use App\Client;
use App\MarkupTrain;
use App\TrainTemplate;
use App\TrainClient;
use App\TrainClientRatePlan;
use App\TrainMarkupRatePlan;
use App\TrainRate;
use Illuminate\Support\Facades\DB;

trait ClientTrains
{
    private function insertTrainRatePlans($train_template_id, $client_id, $period)
    {
        $rate_plans = TrainRate::where('train_template_id', $train_template_id)->get();
        $client_rate_plan_save = [];
        $result = [];
        foreach ($rate_plans as $key => $value) {
            $client_rate_plan_save[] = [
                'train_rate_id' => $value->id,
                'client_id' => $client_id,
                'period' => $period,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ];
        }
        if (!empty($client_rate_plan_save)) {
            $result = TrainClientRatePlan::insert($client_rate_plan_save);
        }
        return $result;
    }

    public function storeAllTrain($client_id, $markup, $period)
    {
        $service_client_database = TrainClient::select(['client_id'])->where([
            'client_id' => $client_id,
            'period' => $period
        ]);

        $services_database = TrainTemplate::select(['id']);

        if ($service_client_database->count() > 0) {
            $services_database->whereNotIn('id', $service_client_database);
        }

        $services_database = $services_database->get();

        foreach ($services_database as $key => $service) {
            $store_service_client = $this->storeTrainClient($period, $client_id, $service['id']);
            if (!empty($store_service_client) && $store_service_client->count() > 0) {
                $this->insertTrainRatePlans($service['id'], $client_id, $markup, $period);
            }
        }
        return $store_service_client;
    }

    public function storeAllTrainClient($train_template_id)
    {

        $clients = Client::with('markups')->get();
        if (!empty($clients) && $clients->count() > 0) {
            foreach ($clients as $key => $client) {
                if (!empty($client->markups) && $client->markups->count() > 0) {
                    foreach ($client->markups as $value) {
                        $this->storeTrainClient($value->period, $client->id, $train_template_id);
                    }
                }
            }
        }
    }


    public function storeTrainClient($period, $client_id, $train_template_id)
    {

        $service_client = new TrainClient();
        $service_client->period = $period;
        $service_client->client_id = $client_id;
        $service_client->train_template_id = $train_template_id;
        $service_client->save();

        $this->deleteMarkupTrain($client_id, $train_template_id, $period);
        $this->deleteMarkupRatePlansTrain($client_id, $train_template_id, $period);
        $this->deleteClientRatePlansTrain($client_id, $period, $train_template_id);

        return $service_client;
    }

    public function deleteMarkupTrain($client_id, $train_template_id, $period)
    {
        DB::transaction(function () use ($client_id, $train_template_id, $period) {
            $serviceMarkup = MarkupTrain::where('client_id', $client_id)->where('train_template_id',
                $train_template_id)->where('period',
                $period)->first();
            if (is_object($serviceMarkup)) {
                $serviceMarkup->delete();
            }
        });
    }

    public function deleteMarkupRatePlansTrain($client_id, $train_template_id, $period)
    {
        DB::transaction(function () use ($client_id, $train_template_id, $period) {
            $client_rate_ids = TrainMarkupRatePlan::where([
                'client_id' => $client_id,
                'period' => $period
            ])->pluck('train_rate_id');

            $rate_plans = TrainRate::select('id', 'name')->where('train_template_id', $train_template_id)->whereIn('id',
                $client_rate_ids)->get();

            foreach ($rate_plans as $rate) {
                $ratesMarkup = TrainMarkupRatePlan::where('client_id', $client_id)->where('train_rate_id',
                    $rate->id)->where('period', $period)->first();
                if (is_object($ratesMarkup)) {
                    $ratesMarkup->delete();
                }
            }
        });
    }

    public function deleteClientRatePlansTrain($client_id, $period, $train_template_id)
    {
        DB::transaction(function () use ($client_id, $train_template_id, $period) {
            TrainClientRatePlan::where(['client_id' => $client_id, 'period' => $period])
                ->whereHas('train_rate', function ($query) use ($train_template_id) {
                    $query->where('train_template_id', $train_template_id);
                })->delete();
        });
    }

}

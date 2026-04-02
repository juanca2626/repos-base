<?php

namespace App\Jobs;

use App\Client;
use App\ClientRatePlan;
use App\RatePlanAssociation;
use App\RatesPlans;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class DisabledRatesPlansClientHotel
 * @package App\Jobs
 */
class DisabledRatesPlansClientHotel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var
     */
    public $client;

    /**
     * Create a new job instance.
     *
     * @param $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        //Todo Obtenemos los 3 años proximos
        $years = collect();
        for ($i = 0; $i < 3; $i++) {
            $years->add([
                'year' => (integer)Carbon::now()->format('Y') + $i
            ]);
        }

        //Todo Listo todas las tarifas activas
        $rate_plans = RatesPlans::where('status', 1)->get();
        foreach ($rate_plans as $rate_plan) {
            $country_ids = [];
            $client_ids = [];
            $market_ids = [];
            $except_country = false;
            $except_client = false;
            //Todo obtenemos las asociaciones por el plan tarifario del hotel
            $associations = RatePlanAssociation::where('rate_plan_id', $rate_plan["id"])->get();
            foreach ($associations as $association) {

                if ($association["entity"] == "Market") {
                    array_push($market_ids, $association["object_id"]);
                }

                if ($association["entity"] == "Country") {
                    array_push($country_ids, $association["object_id"]);
                    $except_country = (bool)$association['except'];
                }

                if ($association["entity"] == "Client") {
                    array_push($client_ids, $association["object_id"]);
                    $except_client = (bool)$association['except'];
                }
            }
            // $id_clients = Client::select('id')->where('status', 1);
            $id_clients = Client::select('id');
            if (count($market_ids)) {
                $id_clients = $id_clients->whereIn('market_id', $market_ids);
            }

            if (count($country_ids) > 0 and $except_country) {
                $id_clients = $id_clients->whereNotIn('country_id', $country_ids);
            } elseif (count($country_ids) > 0 and !$except_client) {
                $id_clients = $id_clients->whereIn('country_id', $country_ids);
            }

            if (count($client_ids) > 0 and $except_client) {
                $id_clients = $id_clients->whereNotIn('id', $client_ids);
            } elseif (count($client_ids) > 0 and !$except_client) {
                $id_clients = $id_clients->whereIn('id', $client_ids);
            }

            $id_clients = $id_clients->where('id', $this->client->id)->get();

            //Todo Agregamos el cliente nuevo al bloqueo de tarifa
            if ($id_clients->count() === 0) {
                foreach ($years as $year) {
                    ClientRatePlan::insert([
                        "period" => $year["year"],
                        "client_id" => $this->client->id,
                        "rate_plan_id" => $rate_plan["id"],
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);
                }
            }  else {
                $query_deletes = ClientRatePlan::where('client_id', $this->client->id)
                    ->where('rate_plan_id', $rate_plan["id"])
                    ->get(['id']);
                foreach ($query_deletes as $delete) {
                    $remove = ClientRatePlan::find($delete['id']);
                    $remove->delete();
                }
            }
        }
    }
}

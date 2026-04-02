<?php

namespace App\Console\Commands;

use App\Client;
use App\ClientRatePlan;
use App\RatePlanAssociation;
use App\RatesPlans;
use App\Service;
use App\ServiceClient;
use App\ServiceClientRatePlan;
use App\ServiceRate;
use App\ServiceRateAssociation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DisabledRatesPlansHotelsByClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aurora:disabled_rates_hotels_client {client_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hoteles - Deshabilita las tarifas a los clientes de acuerdo a la asociacion';
    public $client;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $client_id = $this->argument('client_id');
        if (!empty($client_id)) {
            $this->client = Client::find($client_id);
            if ($this->client) {
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

                    $id_clients = Client::select('id')->where('status', 1);
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
                    } else {
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
    }
}

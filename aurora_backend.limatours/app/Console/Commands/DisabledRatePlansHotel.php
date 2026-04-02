<?php

namespace App\Console\Commands;

use App\Client;
use App\ClientRatePlan;
use App\Market;
use App\RatePlanAssociation;
use App\RatesPlans;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DisabledRatePlansHotel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotels:disabled_rates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Blouear Tarifas de hotel ano actual y dos años siguientes';

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
        $id_markets = Market::select('id')->where('status',0)->get();
        $client_ids = [];
        $client_ids = Client::select('id')->where('status',1)->whereIn('market_id',$id_markets)->pluck('id')->toArray();


        $rate_plans = RatesPlans::where('status',1)->get();

        foreach ($rate_plans as $rate_plan){

            $country_ids = [];
            $market_ids = [];

            $associations = RatePlanAssociation::where('rate_plan_id',$rate_plan["id"])->get();

            foreach ($associations as $association){

                if ($association["entity"] =="Market"){
                    array_push($market_ids,$association["object_id"]);
                }
                if ($association["entity"] =="Client"){
                    array_push($client_ids,$association["object_id"]);
                }
                if ($association["entity"] =="Country"){
                    array_push($country_ids,$association["object_id"]);
                }
            }

            $id_clients = Client::select('id')->where('status',1)
                ->when($country_ids, function ($query) use ($country_ids) {
                    return $query->whereNotIn('country_id',$country_ids);
                })->when($market_ids, function ($query) use ($market_ids) {
                    return $query->whereNotIn('market_id',$market_ids);
                })->when($client_ids, function ($query) use ($client_ids) {
                    return $query->whereNotIn('id',$client_ids);
                })->pluck('id')->toArray();

            foreach ($id_clients as $id_client){

                ClientRatePlan::insert([
                    "period"=>2021,
                    "client_id"=>$id_client,
                    "rate_plan_id"=>$rate_plan["id"],
                    "created_at"=>Carbon::now(),
                    "updated_at"=>Carbon::now()
                ]);
                ClientRatePlan::insert([
                    "period"=>2022,
                    "client_id"=>$id_client,
                    "rate_plan_id"=>$rate_plan["id"],
                    "created_at"=>Carbon::now(),
                    "updated_at"=>Carbon::now()
                ]);
                ClientRatePlan::insert([
                    "period"=>2023,
                    "client_id"=>$id_client,
                    "rate_plan_id"=>$rate_plan["id"],
                    "created_at"=>Carbon::now(),
                    "updated_at"=>Carbon::now()
                ]);
            }

            }
    }
}

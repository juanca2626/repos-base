<?php

namespace App\Console\Commands;

use App\Client;
use App\ClientRatePlan;
use App\RatePlanAssociation;
use App\RatesPlans;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HotfixPermissionsClientsError extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotfix:permissions_clients_error';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar permisos de clientes creados a partir de la fecha 13-10-2021 por error en permisos';

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
        DB::beginTransaction();
        try {
            DB::raw('SET SQL_SAFE_UPDATES = 0');
            $client_permissions_ids = Client::whereIn('code', '4BUMER', '9ZEPEL', '2SKINE', '5JAPTU', '3ADTRA', '2POWWO', '5PRIDE', '9TAIKO',
                        '5VETA', '4TITI')->pluck('id')->toArray();

            ClientRatePlan::whereIn('client_id',$client_permissions_ids)->forceDelete();

            $rate_plans = RatesPlans::where('status', 1)->get();

            foreach ($rate_plans as $rate_plan) {

                $country_ids = [];
                $client_ids = [];
                $market_ids = [];

                $associations = RatePlanAssociation::where('rate_plan_id', $rate_plan["id"])->get();

                foreach ($associations as $association) {

                    if ($association["entity"] == "Market") {
                        array_push($market_ids, $association["object_id"]);
                    }
                    if ($association["entity"] == "Client") {
                        array_push($client_ids, $association["object_id"]);
                    }
                    if ($association["entity"] == "Country") {
                        array_push($country_ids, $association["object_id"]);
                    }
                }

                $id_clients = Client::select('id')->where('status', 1)
                    ->when($country_ids, function ($query) use ($country_ids) {
                        return $query->whereNotIn('country_id', $country_ids);
                    })->when($market_ids, function ($query) use ($market_ids) {
                        return $query->whereNotIn('market_id', $market_ids);
                    })->when($client_ids, function ($query) use ($client_ids) {
                        return $query->whereNotIn('id', $client_ids);
                    })->whereIn('code', '4BUMER', '9ZEPEL', '2SKINE', '5JAPTU', '3ADTRA', '2POWWO', '5PRIDE', '9TAIKO',
                        '5VETA', '4TITI')->pluck('id')->toArray();

                foreach ($id_clients as $id_client) {

                    ClientRatePlan::insert([
                        "period" => 2021,
                        "client_id" => $id_client,
                        "rate_plan_id" => $rate_plan["id"],
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);
                    ClientRatePlan::insert([
                        "period" => 2022,
                        "client_id" => $id_client,
                        "rate_plan_id" => $rate_plan["id"],
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);
                    ClientRatePlan::insert([
                        "period" => 2023,
                        "client_id" => $id_client,
                        "rate_plan_id" => $rate_plan["id"],
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now()
                    ]);
                }

            }

            DB::commit();

        } catch(\Exception $exception){
            DB::rollback();
            return ($exception);
        }
    }

}

<?php

use App\Service;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddDataRates2020ServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $file_service_rates = File::get("database/data/service_rates_year_2020.json");
        $rates = json_decode($file_service_rates, true);
        $created_at = date("Y-m-d H:i:s");
        DB::transaction(function () use ($rates, $created_at) {
            foreach ($rates as $key => $rate) {
                $findService = Service::select(['id', 'aurora_code', 'equivalence_aurora'])
                    ->with([
                        'service_rate.service_rate_plans' => function ($query) {
                            $query->whereRaw('YEAR(date_from) = 2020');
                        }
                    ])
                    ->where('aurora_code', $rate['aurora_code'])
                    ->where('equivalence_aurora', $rate['equivalence_aurora'])->get()->first();
                if ($findService) {
                    if (count($rate['rates_2020']) > 0) {
                        if ($findService->service_rate->count() > 0) {
                            $serviceRateId = $findService->service_rate[0]->id;
                            if ($findService->service_rate[0]->service_rate_plans->count() > 0) {
                                $policy_id = $findService->service_rate[0]->service_rate_plans[0]->service_cancellation_policy_id;
                                $proveedor_id = $findService->service_rate[0]->service_rate_plans[0]->user_id;

                            } else {
                                $policy_id = 1;
                                $proveedor_id = 3182;
                            }

                            $delete_ids = DB::table('service_rate_plans')
                                ->where('service_rate_id', $findService->service_rate[0]->id)
                                ->whereYear('date_from', '2020')->get()->toArray();

                            if (count($delete_ids) > 0) {
                                foreach ($delete_ids as $item) {
                                   $detle = \App\ServiceRatePlan::find($item->id);
                                   if($detle){
                                       $detle->delete();
                                   }
                                }
                            }

                            foreach ($rate['rates_2020'] as $rate2020) {
                                DB::table('service_rate_plans')->insert([
                                    "service_rate_id" => $serviceRateId,
                                    "service_cancellation_policy_id" => $policy_id,
                                    "user_id" => $proveedor_id,
                                    "date_from" => $rate2020['date_from'],
                                    "date_to" => $rate2020['date_to'],
                                    "monday" => 1,
                                    "tuesday" => 1,
                                    "wednesday" => 1,
                                    "thursday" => 1,
                                    "friday" => 1,
                                    "saturday" => 1,
                                    "sunday" => 1,
                                    "pax_from" => $rate2020['canpax'],
                                    "pax_to" => $rate2020['canpax'],
                                    "price_adult" => $rate2020['price'],
                                    "price_child" => $rate2020['price'],
                                    "price_infant" => 0,
                                    "price_guide" => 0,
                                    "status" => 1,
                                    "created_at" => $created_at,
                                    "updated_at" => $created_at,
                                ]);

                            }

                        }

                    }
                }

            }

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

<?php

use App\ProgressBar;
use App\Service;
use App\Http\Traits\Translations;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceCostRatesTableSeeder extends Seeder
{
    use Translations;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file_service_rates = File::get("database/data/service_rates.json");
        $rates = json_decode($file_service_rates, true);
        $created_at = date("Y-m-d H:i:s");
        $proveedor = User::where('code', 'LITO')->where('user_type_id', 2)->get()->first();

        if (!$proveedor) {
            dd('No existe el proveedor LITO');
        }
        DB::transaction(function () use ($rates, $created_at,$proveedor) {
            foreach ($rates as $key => $rate) {
                $findService = Service::with('cancellation_policy')->where('aurora_code', $rate['aurora_code'])
                    ->where('equivalence_aurora', $rate['equivalence_aurora'])->get()->first()->toArray();
                if ($findService) {
                    if (isset($findService['cancellation_policy']) and count($findService['cancellation_policy']) > 0) {
                        $policy_id = $findService['cancellation_policy'][0]['id'];
                        $service_id = $findService['id'];
                        if (count($rate['rates_2019']) > 0) {
                            $serviceRateId = DB::table('service_rates')->insertGetId([
                                "name" => 'Tarifa',
                                "allotment" => 1,
                                "taxes" => 0,
                                "services" => 0,
                                "advance_sales" => 0,
                                "promotions" => 0,
                                "status" => 1,
                                "service_id" => $service_id,
                                "service_type_rate_id" => 1,
                                "created_at" => $created_at,
                                "updated_at" => $created_at,
                            ]);
                            //Nombre comercial de la tarifa
                            $names = [
                                1 => ['id' => '', 'commercial_name' => 'Tarifa'],
                                2 => ['id' => '', 'commercial_name' => 'Tarifa'],
                                3 => ['id' => '', 'commercial_name' => 'Tarifa'],
                                4 => ['id' => '', 'commercial_name' => 'Tarifa'],
                            ];
                            $this->saveTranslation($names, 'servicerate', $serviceRateId);
                            //ProgressBar
                            ProgressBar::updateOrCreate([
                                'slug' => 'service_progress_rates',
                                'value' => 10,
                                'type' => 'service',
                                'object_id' => $service_id
                            ]);

                            foreach ($rate['rates_2019'] as $rate2019) {
                                DB::table('service_rate_plans')->insert([
                                    "service_rate_id" => $serviceRateId,
                                    "service_cancellation_policy_id" => $policy_id,
                                    "user_id" => $proveedor->id,
                                    "date_from" => $rate2019['date_from'],
                                    "date_to" => $rate2019['date_to'],
                                    "monday" => 1,
                                    "tuesday" => 1,
                                    "wednesday" => 1,
                                    "thursday" => 1,
                                    "friday" => 1,
                                    "saturday" => 1,
                                    "sunday" => 1,
                                    "pax_from" => $rate2019['canpax'],
                                    "pax_to" => $rate2019['canpax'],
                                    "price_adult" => $rate2019['price'],
                                    "price_child" => $rate2019['price'],
                                    "price_infant" => 0,
                                    "price_guide" => 0,
                                    "status" => 1,
                                    "created_at" => $created_at,
                                    "updated_at" => $created_at,
                                ]);
                            }
                        }

                        if (count($rate['rates_2020']) > 0) {
                            foreach ($rate['rates_2020'] as $rate2020) {
                                DB::table('service_rate_plans')->insert([
                                    "service_rate_id" => $serviceRateId,
                                    "service_cancellation_policy_id" => $policy_id,
                                    "user_id" => $proveedor->id,
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

                        if (count($rate['rates_2021']) > 0) {
                            foreach ($rate['rates_2021'] as $rate2021) {
                                DB::table('service_rate_plans')->insert([
                                    "service_rate_id" => $serviceRateId,
                                    "service_cancellation_policy_id" => $policy_id,
                                    "user_id" => $proveedor->id,
                                    "date_from" => $rate2021['date_from'],
                                    "date_to" => $rate2021['date_to'],
                                    "monday" => 1,
                                    "tuesday" => 1,
                                    "wednesday" => 1,
                                    "thursday" => 1,
                                    "friday" => 1,
                                    "saturday" => 1,
                                    "sunday" => 1,
                                    "pax_from" => $rate2021['canpax'],
                                    "pax_to" => $rate2021['canpax'],
                                    "price_adult" => $rate2021['price'],
                                    "price_child" => $rate2021['price'],
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
}

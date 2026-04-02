<?php

use App\BusinessRegionsCountry;
use App\ClientRatePlan;
use App\Hotel;
use App\RatesPlans;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateClientRatePlansBusinessRegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $archivo1 = storage_path('logs/mi_archivo.log');

        // DB::beginTransaction();
        // try
        // {
            
            $total = 0;
            $business_region_id = 1;           
            $region_countries = BusinessRegionsCountry::where('business_region_id', $business_region_id)->groupBy('country_id')->pluck('country_id','country_id');                 
            RatesPlans::join('hotels', 'rates_plans.hotel_id', '=', 'hotels.id')->whereIn('hotels.country_id', $region_countries)->select('rates_plans.id', 'rates_plans.hotel_id')
            ->chunk(100, function($rates_plans) use ($business_region_id, &$total, $archivo1) {
                
                $update = date('Y-m-d H:i:s');
                $total += 100;
                $ratePlanIds = $rates_plans->pluck('id')->toArray();

                $updated = DB::table('client_rate_plans')
                    ->whereNull('business_region_id')
                    ->whereIn('rate_plan_id', $ratePlanIds)
                    ->whereNull('deleted_at')                    
                    ->update(['business_region_id' => $business_region_id]);

                file_put_contents(
                    $archivo1,
                    "{$update} Actualizados {$updated} registros en lote (acumulado: {$total})\n",
                    FILE_APPEND
                );

            });

        //     DB::commit();

        // } catch(\Exception $exception){
        //     DB::rollback();
        //     $this->command->info($exception->getMessage());
        // }
 
        $this->command->info('Todos los ClientRatePlan han sido actualizados con business_region_id = 1');


    }
}

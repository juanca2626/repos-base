<?php

use App\Service;
use App\ServiceTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceMaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $services = File::get("database/data/service_mask.json");
        $services = json_decode($services, true);
        DB::beginTransaction();
            
            foreach($services as $serviceData){
   
                $service = new Service([
                    'aurora_code' => $serviceData['aurora_code'],
                    'name' => $serviceData['name'],
                    'currency_id' => 2,
                    'latitude' => '',
                    'longitude' => '',
                    'qty_reserve' => 0,
                    'affected_igv' => 0,
                    'affected_markup' => 0,
                    'affected_schedule' => 0,
                    'allow_child' => 0,
                    'allow_infant' => 0,
                    'limit_confirm_hours' => 0,
                    'unit_duration_limit_confirmation' => 0,
                    'infant_min_age' => 0,
                    'infant_max_age' => 0,
                    'include_accommodation' => 0,
                    'unit_id' => 2,
                    'unit_duration_id' => 2,
                    'unit_duration_reserve' => 0,
                    'service_type_id' => 1,
                    'classification_id' => 1,
                    'service_sub_category_id' => 24,
                    'user_id' => 1,
                    'date_solicitude' => 0,
                    'duration' => 0,
                    'pax_min' => 1,
                    'pax_max' => 130,
                    'min_age' => 0,
                    'require_itinerary' => 0,
                    'require_image_itinerary' => 0,
                    'type' => 'service',
                    'status' => 1,
                    'compensation' => 0,
                    'rate_order' => 0,
                    'service_mask' => true        
                ]);
    
                $service->save();
                foreach($serviceData['translate'] as $translate){
                    $serviceTranslation = new ServiceTranslation();
                    $serviceTranslation->language_id = $translate['language_id'];
                    $serviceTranslation->name = $translate['name'];
                    $serviceTranslation->name_commercial = $translate['name_commercial'];
                    $serviceTranslation->service_id = $service->id;
                    $serviceTranslation->save();
                }
            }           
        DB::commit();
       
    }
}

<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ZoneInAirportTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function(){

            $zones = \App\Zone::with([
                'translations' => function ($query) {
                    $query->select(['id', 'object_id', 'value']);
                    $query->where('slug', 'zone_name');
                    $query->where('language_id', 1);
                }
            ])->get();

            foreach ( $zones as $zone ){
 
                $zoneTranslation = $zone->translations ? $zone->translations->first() : null; 

                $zone->in_airport = 0;
                if(isset($zoneTranslation) and strtolower($zoneTranslation->value) == 'aeropuerto'){
                    $zone->in_airport = 1;
                    $zone->save();
                }
 

            }
        });
    }
}

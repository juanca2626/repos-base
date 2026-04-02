<?php

use App\BusinessRegion;
use App\BusinessRegionsCountry;
use App\Hotel;
use App\HotelClient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateHotelClientsBusinessRegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $business_region = BusinessRegion::with('countries')->get();
        foreach($business_region as $region){
            $business_region_id = $region->id;
            // $region_countries = BusinessRegionsCountry::where('business_region_id', $business_region_id)->groupBy('country_id')->pluck('country_id','country_id');
            $region_countries = $region->countries->pluck('id');
            $hotels = Hotel::select('id', 'country_id')->whereIn('country_id', $region_countries)->pluck('id')->toArray();

            $updated = DB::table('hotel_clients')
                ->whereIn('hotel_id', $hotels)
                ->whereNull('deleted_at')
                ->whereNull('business_region_id')
                ->update([
                    'business_region_id' => $business_region_id
                ]);
        }

        $this->command->info('Todos los registos de HotelClient han sido actualizados');
    }
}

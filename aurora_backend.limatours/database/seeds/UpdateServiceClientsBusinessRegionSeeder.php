<?php

use App\Service;
use App\BusinessRegion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateServiceClientsBusinessRegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $region_id = 1;
        $regions = BusinessRegion::with('countries')->get();
        foreach($regions as $region){
            $countryIds = $region->countries->pluck('id');
            $serviceIds = Service::whereHas('serviceOrigin', function($query) use ($countryIds) {
                $query->whereIn('country_id', $countryIds);
            })
            ->pluck('id');
            DB::table('service_clients')->whereIn('service_id', $serviceIds)
                ->whereNull('business_region_id')
                ->update(['business_region_id' => $region->id]);
        }

        $this->command->info('Asignación de BusinessRegion a ServiceClients completada.');
    }
}

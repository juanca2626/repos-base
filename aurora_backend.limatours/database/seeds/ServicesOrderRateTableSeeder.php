<?php

use App\Service;
use Illuminate\Database\Seeder;

class ServicesOrderRateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $services = Service::all();

        foreach ($services as $service) {
            $service_update_order = Service::find($service["id"]);
            $service_update_order->rate_order = $service["id"];
            $service_update_order->save();
        }
    }
}

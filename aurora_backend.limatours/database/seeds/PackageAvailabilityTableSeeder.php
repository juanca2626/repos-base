<?php

use App\Package;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackageAvailabilityTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {
            $packages = Package::with('plan_rates')->where('status', 1)->get();
            $created_at = date("Y-m-d H:i:s");
            foreach ($packages as $key => $package) {
                foreach ($package->plan_rates as $key_plan => $plan_rate) {
                    $date_from = Carbon::createFromFormat('d/m/Y', date('d/m/Y'))->setTimezone('America/Lima');
                    $date_to = Carbon::createFromFormat('d/m/Y', '31/12/2020')->setTimezone('America/Lima');
                    $difference_days = $date_from->diffInDays($date_to->addDay());
                    for ($i = 0; $i <= $difference_days; $i++) {
                        $date = ($i === 0) ? $date_from : $date_from->addDay();
                        DB::table('package_inventories')->insert([
                            'day' => $date->day,
                            'date' => $date->format('Y-m-d'),
                            'inventory_num' => 100,
                            'total_booking' => 0,
                            'total_cancelled' => 0,
                            'locked' => false,
                            'package_plan_rate_id' => $plan_rate['id'],
                            'created_at' => $created_at,
                            'updated_at' => $created_at
                        ]);
                    }
                }
            }

        });

    }
}

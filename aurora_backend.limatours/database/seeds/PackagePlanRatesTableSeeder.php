<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackagePlanRatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

//        function sumar_anios($fecha, $nAnios){
//            $days = $nAnios * 365;
//            $nuevafecha = strtotime ( '+' . $days . 'day' , strtotime ( $fecha ) ) ;
//            return date ( 'Y-m-j' , $nuevafecha );
//        }
//
//        $file_package_plan_rates = File::get("database/data/package_plan_rates.json");
//        $package_plan_rates = json_decode($file_package_plan_rates, true);
//        $created_at = date("Y-m-d H:i:s");
//
//        DB::transaction(function () use ($package_plan_rates, $created_at) {
//            foreach ($package_plan_rates as $package_plan_rate) {
//                $year_date_in = (int) substr( $package_plan_rate['date_from'],0,4 );
//                $year_date_out = (int) substr( $package_plan_rate['date_to'],0,4 );
//
//                if( $year_date_in < 2020 ) {
//                    $new_date_in = sumar_anios($package_plan_rate['date_from'], (2020 - $year_date_in));
//                    $new_date_out = sumar_anios($package_plan_rate['date_to'],(2020 - $year_date_out));
//                } else {
//                    $new_date_in = $package_plan_rate['date_in'];
//                    $new_date_out = $package_plan_rate['date_out'];
//                }
//                DB::table('package_plan_rates')->insert([
//                    'package_id' => $package_plan_rate["package_id"],
//                    'code' => $package_plan_rate["code"],
//                    'name' => $package_plan_rate["name"],
//                    'date_from' => $new_date_in,
//                    'date_to' => $new_date_out,
//                    'status' => $package_plan_rate["status"],
//                    'created_at' => $created_at,
//                    'updated_at' => $created_at
//                ]);
//            }
//        });
        $path = 'database/data/sql/package_plan_rates_more.sql';
        DB::transaction(function () use ($path) {
            DB::unprepared(file_get_contents($path));
        });
    }
}

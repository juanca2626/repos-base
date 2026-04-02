<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataPackageRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $file_package_rates = File::get("database/data/package_rates.json");
        $package_rates = json_decode($file_package_rates, true);
        $created_at = date("Y-m-d H:i:s");

        DB::transaction(function () use ($package_rates,$created_at) {
            foreach ($package_rates as $package_rate) {
                DB::table('package_rates')->insert([
                    'id' => $package_rate["id"],
                    'reference_number' => $package_rate["reference_number"],
                    'simple' => $package_rate["simple"],
                    'double' => $package_rate["double"],
                    'triple' => $package_rate["triple"],
                    'boy' => $package_rate["boy"],
                    'infant' => $package_rate["infant"],
                    'date_from' => $package_rate["date_from"],
                    'date_to' => $package_rate["date_to"],
                    'package_id' => $package_rate["package_id"],
                    'type_class_id' => $package_rate["type_class_id"],
                    'service_type_id' => $package_rate["service_type_id"],
                    'created_at' => $created_at,
                    'updated_at' => $created_at
                ]);
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
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        App\PackageRate::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}

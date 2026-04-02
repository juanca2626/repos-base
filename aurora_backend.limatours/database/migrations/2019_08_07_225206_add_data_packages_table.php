<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $file_packages = File::get("database/data/packages.json");
        $packages = json_decode($file_packages, true);
        $created_at = date("Y-m-d H:i:s");

        DB::transaction(function () use ($packages,$created_at) {
            foreach ($packages as $package) {
                DB::table('packages')->insert([
                    'id' => $package["id"],
                    'country_id' => $package["country_id"],
                    "code" => $package["code"],
                    "nights" => $package["nights"],
                    "map_link" => $package["map_link"],
                    "image_link" => $package["image_link"],
                    "status" => $package["status"],
                    "reference" => $package["reference"],
                    "rate_type" => $package["rate_type"],
                    "rate_dynamic" => $package["rate_dynamic"],
                    "allow_guide" => $package["allow_guide"],
                    "allow_child" => $package["allow_child"],
                    "allow_infant" => $package["allow_infant"],
                    "limit_confirmation_hours" => $package["limit_confirmation_hours"],
                    "infant_min_age" => $package["infant_min_age"],
                    "infant_max_age" => $package["infant_max_age"],
                    "physical_intensity_id" => $package["physical_intensity_id"],
                    "tag_id" => $package["tag_id"],
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
        App\Package::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}

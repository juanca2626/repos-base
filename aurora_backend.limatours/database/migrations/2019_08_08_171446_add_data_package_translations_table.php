<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataPackageTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $file_package_translations = File::get("database/data/package_translations.json");
        $package_translations = json_decode($file_package_translations, true);
        $created_at = date("Y-m-d H:i:s");

        DB::transaction(function () use ($package_translations,$created_at) {
            foreach ($package_translations as $package_translation) {

                DB::table('package_translations')->insert([
                    'id' => $package_translation["id"],
                    'name' => $package_translation["name"],
                    "tradename" => $package_translation["tradename"],
                    "description" => $package_translation["description"],
                    "label" => $package_translation["label"],
                    "itinerary_link" => $package_translation["itinerary_link"],
                    "itinerary_label" => $package_translation["itinerary_label"],
                    "itinerary_description" => $package_translation["itinerary_description"],
                    "inclusion" => $package_translation["inclusion"],
                    "restriction" => $package_translation["restriction"],
                    "policies" => $package_translation["policies"],
                    "language_id" => $package_translation["language_id"],
                    "package_id" => $package_translation["package_id"],
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
        App\PackageTranslation::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}

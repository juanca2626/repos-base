<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CopyContentColumnsPackageTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement("SET SQL_SAFE_UPDATES = 0;");
        \Illuminate\Support\Facades\DB::statement('update `package_translations` set `description_commercial` =  `description`, `itinerary_link_commercial` =  `itinerary_link`, `itinerary_commercial` =  `itinerary_description`, `restriction_commercial` =  `restriction`, `policies_commercial` =  `policies`');
        \Illuminate\Support\Facades\DB::statement("SET SQL_SAFE_UPDATES = 1;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

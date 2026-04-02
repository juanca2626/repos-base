<?php

use Illuminate\Database\Migrations\Migration;

class CopyContentColumnsTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement("SET SQL_SAFE_UPDATES = 0;");
        \Illuminate\Support\Facades\DB::statement('update `service_translations` set `name` =  `name_commercial`, `description_commercial` =  `description`, `itinerary_commercial` =  `itinerary`, `summary_commercial` =  `summary`');
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

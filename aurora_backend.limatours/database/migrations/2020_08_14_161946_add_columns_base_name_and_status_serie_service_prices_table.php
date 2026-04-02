<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsBaseNameAndStatusSerieServicePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('serie_service_prices', function (Blueprint $table) {
            $table->tinyInteger('status')->after('base_code');
            $table->string('base_name')->after('base_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('serie_service_prices', function (Blueprint $table) {
            $table->dropColumn(['status','base_name']);
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateReferenceServiceColumnForeignSerieServicePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('serie_service_prices', function (Blueprint $table) {
            $table->dropForeign('serie_service_prices_serie_service_id_foreign');
            $table->dropColumn('serie_service_id');
            $table->unsignedBigInteger('serie_service_departure_id')->after('id');
            $table->foreign('serie_service_departure_id')->references('id')->on('serie_service_departures');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('serie_service_prices', function (Blueprint $table) {
            $table->dropForeign('serie_service_prices_serie_service_departure_id_foreign');
            $table->dropColumn('serie_service_departure_id');
            $table->unsignedBigInteger('serie_service_id')->after('id');
            $table->foreign('serie_service_id')->references('id')->on('serie_services');
        });
    }
}

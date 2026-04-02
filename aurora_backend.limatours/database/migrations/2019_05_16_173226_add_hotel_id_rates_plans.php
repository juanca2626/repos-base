<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class AddHotelIdRatesPlans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rates_plans', function (Blueprint $table) {
            $table->unsignedBigInteger('hotel_id')->nullable()->after('charge_type_id');

            $table->foreign('hotel_id')->references('id')->on('hotels');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rates_plans', function (Blueprint $table) {
            $table->dropForeign('rates_plans_hotel_id_foreign');
            $table->dropColumn('hotel_id');
        });
    }
}

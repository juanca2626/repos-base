<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class ModifyRatesHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rates_histories', function (Blueprint $table) {
            $table->dropForeign('rates_histories_policies_rate_id_foreign');
            $table->dropColumn('policies_rate_id');

            $table->longText('dataRooms')->after('data');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rates_histories', function (Blueprint $table) {
            $table->dropColumn('dataRooms');
            $table->unsignedBigInteger('policies_rate_id')->after('rates_plan_id');
            $table->foreign('policies_rate_id')->references('id')->on('policies_rates');
        });
    }
}

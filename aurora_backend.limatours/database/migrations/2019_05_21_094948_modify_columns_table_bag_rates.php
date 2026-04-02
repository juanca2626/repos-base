<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class ModifyColumnsTableBagRates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bag_rates', function (Blueprint $table) {
            $table->renameColumn('rate_plan_id', 'rate_plan_rooms_id');
            $table->dropForeign('bag_rates_rate_plan_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bag_rates', function (Blueprint $table) {
            $table->renameColumn('rate_plan_rooms_id', 'rate_plan_id');
            $table->foreign('rate_plan_id')->references('id')->on('rates_plans');
        });
    }
}

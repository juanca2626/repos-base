<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChannelRatePlanIdToRatesPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rates_plans', function (Blueprint $table) {
            $table->string('channel_rate_plan_id')->nullable()->after('code');
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
            $table->dropColumn('channel_rate_plan_id');
        });
    }
}

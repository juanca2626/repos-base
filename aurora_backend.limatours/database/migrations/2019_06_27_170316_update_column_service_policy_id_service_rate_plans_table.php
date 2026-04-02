<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateColumnServicePolicyIdServiceRatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_rate_plans', function (Blueprint $table) {
            $table->dropForeign(['service_penalty_id']);
            $table->dropColumn('service_penalty_id');
            $table->unsignedBigInteger('service_cancellation_policy_id')->default(1)->after('service_rate_id');
            $table->foreign('service_cancellation_policy_id')->references('id')->on('service_cancellation_policies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_rate_plans', function (Blueprint $table) {
            $table->dropForeign(['service_cancellation_policy_id']);
            $table->dropColumn('service_cancellation_policy_id');
            $table->unsignedBigInteger('service_penalty_id')->default(1)->after('service_rate_id');
            $table->foreign('service_penalty_id')->references('id')->on('service_penalties');
        });
    }
}

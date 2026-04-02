<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class DropPolicyCancellationIdToPolicyCancellationParameters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('policy_cancellation_parameters', function (Blueprint $table) {
            $table->dropForeign(['policy_cancellation_id']);
            $table->dropColumn('policy_cancellation_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('policy_cancellation_parameters', function (Blueprint $table) {
            $table->integer('policy_cancellation_id')->unsigned();
            $table->foreign('policy_cancellation_id')->references('id')->on('cancellation_policies');
        });
    }
}

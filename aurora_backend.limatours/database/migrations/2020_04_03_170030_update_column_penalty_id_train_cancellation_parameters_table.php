<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateColumnPenaltyIdTrainCancellationParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('train_cancellation_parameters', function (Blueprint $table) {
            $table->dropForeign(['penalty_id']);
            $table->dropColumn('penalty_id');
            $table->unsignedBigInteger('service_penalty_id')->default(1)->after('train_cancellation_id');
            $table->foreign('service_penalty_id')->references('id')->on('service_penalties');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('train_cancellation_parameters', function (Blueprint $table) {
            $table->dropForeign(['service_penalty_id']);
            $table->dropColumn('service_penalty_id');
            $table->unsignedBigInteger('penalty_id')->default(1)->after('train_cancellation_id');
            $table->foreign('penalty_id')->references('id')->on('penalties');
        });
    }
}

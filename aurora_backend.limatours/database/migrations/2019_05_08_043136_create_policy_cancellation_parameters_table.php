<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class CreatePolicyCancellationParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('policy_cancellation_parameters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('min_day');
            $table->integer('max_day');
            $table->unsignedBigInteger('policy_cancellation_id');
            $table->unsignedBigInteger('penalty_id');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('policy_cancellation_id')->references('id')->on('cancellation_policies');
            $table->foreign('penalty_id')->references('id')->on('penalties');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('policy_cancellation_parameters');
    }
}

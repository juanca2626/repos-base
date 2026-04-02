<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicePoliticsParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_politics_parameters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('min_hour');
            $table->integer('max_hour');
            $table->unsignedBigInteger('service_politics_id');
            $table->unsignedBigInteger('service_penalty_id');
            $table->double('amount')->nullable();
            $table->boolean('tax');
            $table->boolean('service');
            $table->foreign('service_politics_id')->references('id')->on('service_cancellation_policies');
            $table->foreign('service_penalty_id')->references('id')->on('service_penalties');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_politics_parameters');
    }
}

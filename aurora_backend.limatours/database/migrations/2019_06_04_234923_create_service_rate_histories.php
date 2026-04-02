<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceRateHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_rate_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('service_rate_id');
            $table->unsignedBigInteger('service_id');
            $table->longText('data');
            $table->longText('dataRooms');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('service_rate_id')->references('id')->on('service_rates');
            $table->foreign('service_id')->references('id')->on('services');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_rate_histories');
    }
}

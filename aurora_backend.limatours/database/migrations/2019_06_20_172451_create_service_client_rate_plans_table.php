<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceClientRatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_client_rate_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('period');
            $table->decimal('markup', 10, 2);
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('service_rate_id');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('service_rate_id')->references('id')->on('service_rates');
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
        Schema::dropIfExists('service_client_rate_plans');
    }
}

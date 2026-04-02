<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceRateAssociationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_rate_associations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('service_rate_id');
            $table->foreign('service_rate_id')->references('id')->on('service_rates');
            $table->string('entity');
            $table->bigInteger('object_id');
            $table->boolean('except')->default(0)->comment('Si el campo es true debe de excluir de acuerdo al campo entity');
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
        Schema::dropIfExists('service_rate_plan_associations');
    }
}

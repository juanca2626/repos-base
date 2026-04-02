<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationsServicesSupplementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations_services_supplements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reservations_service_id');
            $table->foreign('reservations_service_id','re_ser_supp_res_service_id_foreign')->references('id')->on('reservations_services');
            $table->unsignedBigInteger('service_rate_id')->nullable();
            $table->string('service_rate_name')->nullable();
            $table->float('total_amount')->default(0);
            $table->float('total_adult_amount')->default(0);
            $table->float('total_child_amount')->default(0);
            $table->unsignedBigInteger('supplement_id')->nullable();
            $table->string('supplement_name')->nullable();
            $table->smallInteger('amount_extra')->nullable();
            $table->string('type_req_opt')->nullable();
            $table->unsignedTinyInteger('adults')->default(0);
            $table->unsignedTinyInteger('child')->default(0);
            $table->json('rates')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reservations_services_supplements');
    }
}

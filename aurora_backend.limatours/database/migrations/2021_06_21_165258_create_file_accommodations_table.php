<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFileAccommodationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_accommodations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('file_service_id');
            $table->foreign('file_service_id')->references('id')->on('file_services');
            $table->unsignedBigInteger('reservation_passenger_id');
            $table->foreign('reservation_passenger_id')->references('id')->on('reservation_passengers');
            $table->string('room_key', 3)->nullable()->comment('*nrohab= (tipo+numero de orden de ese tipo de hab) D=Double S=simple T=triple
   Cuando son servicios, terrestres, paquetes o aereos en nrohab no va nada');
            $table->softDeletes();
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
        Schema::dropIfExists('file_accommodations');
    }
}

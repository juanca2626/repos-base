<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_schedules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->time('from');
            $table->time('to');
            $table->unsignedBigInteger('service_id')->unsigned()->index();
            $table->foreign('service_id')->references('id')->on('services');
            $table->enum('day',
                [
                    'L', //Lunes
                    'M', //Martes
                    'X', //Miercoles
                    'J', //Jueves
                    'V', //Viernes
                    'S', //Sabado
                    'D', //Domingo
                ]);
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
        Schema::dropIfExists('service_schedules');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceScheduleDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_schedule_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('service_schedule_id')->unsigned()->index();
            $table->foreign('service_schedule_id')->references('id')->on('service_schedules');
            $table->enum('type',
                [
                    'I', //Inicio
                    'F', //Fin
                ])->nullable(false);
            $table->time('monday')->nullable();
            $table->time('tuesday')->nullable();
            $table->time('wednesday')->nullable();
            $table->time('thursday')->nullable();
            $table->time('friday')->nullable();
            $table->time('saturday')->nullable();
            $table->time('sunday')->nullable();
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
        Schema::dropIfExists('service_schedule_details');
    }
}

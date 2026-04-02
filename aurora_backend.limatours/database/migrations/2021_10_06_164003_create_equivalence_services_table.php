<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEquivalenceServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equivalence_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('service_id');
            $table->foreign('service_id')->references('id')->on('services');
            $table->unsignedBigInteger('master_service_id');
            $table->foreign('master_service_id')->references('id')->on('master_services');
            $table->smallInteger('incremental')->nullable()->comment('nroite	(incremental) como una identificacion incremental');
            $table->date('date_in')->nullable()->comment('fecin	(date_in) fecha de inicio del svs');
            $table->date('date_out')->nullable()->comment('fecout	(date_out) fecha de termino del svs');
            $table->string('status_ifx', 2)->comment('estado	(status_ifx) estado del registro de ifx');
            $table->smallInteger('nights')->nullable()->comment('cansvs	(nights) cantidad de noches en caso de hotel');
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
        Schema::dropIfExists('equivalence_services');
    }
}

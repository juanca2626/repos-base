<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtensionPentagramaDetailServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('extension_pentagrama_detail_services');
        Schema::create('extension_pentagrama_detail_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('extension_pentagrama_service_id')->unsigned()->nullable();
            $table->string('executive_name');
            $table->string('city');
            $table->string('single_date');
            $table->string('single_hour');
            $table->enum('type_service', ['hotel', 'service', 'other'])->default('service');
            $table->string('external_service_id');
            $table->string('external_service_description');
            $table->tinyInteger('status_service')->default(0); // RQ=0 OK=1
            $table->tinyInteger('status_selected')->default(1); // Seleccionado = 1, No seleccionado = 0
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
        Schema::dropIfExists('extension_pentagrama_detail_services');
    }
}

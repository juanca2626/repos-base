<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtensionDespegarServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extension_despegar_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('extension_despegar_header_id');
            $table->foreign('extension_despegar_header_id')->references('id')->on('extension_despegar_headers');
            $table->string('code');
            $table->string('detail')->nullable();
            $table->date('date_from');
            $table->date('date_to');
            $table->integer('adults');
            $table->integer('children');
            $table->integer('infants');
            $table->string('passenger')->nullable();
            $table->tinyInteger('status');
            $table->char('type',1);
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->string('modality')->nullable();
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
        Schema::dropIfExists('extension_despegar_services');
    }
}

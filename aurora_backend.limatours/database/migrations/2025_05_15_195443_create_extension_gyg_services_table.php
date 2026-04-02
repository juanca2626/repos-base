<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtensionGygServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extension_gyg_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('extension_gyg_header_id');
            $table->foreign('extension_gyg_header_id')->references('id')->on('extension_gyg_headers');
            $table->string('code');
            $table->date('date_start');
            $table->integer('paxs');
            $table->string('passenger')->nullable();
            $table->string('passenger_email')->nullable();
            $table->tinyInteger('status');
            $table->char('booking_state',1);
            $table->char('type',1);
            $table->string('name')->nullable();
            $table->string('detail')->nullable();
            $table->string('booking_code')->nullable();
            $table->string('ticket_type')->nullable();
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
        Schema::dropIfExists('extension_gyg_services');
    }
}

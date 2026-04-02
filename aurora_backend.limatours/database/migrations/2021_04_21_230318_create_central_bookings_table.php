<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCentralBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('central_bookings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('channel_name', 45);
            $table->string('model', 100);
            $table->bigInteger('object_id');
            $table->string('code', 45);
            $table->string('tags', 400)->nullable();
            $table->smallInteger('status');
            $table->dateTime('made_date_time')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('description', 250)->nullable();
            $table->string('passenger', 250)->nullable();
            $table->string('agent', 250)->nullable();
            $table->integer('file_number')->nullable();
            $table->string('aurora_code', 45)->nullable();
            $table->string('type_service', 45)->nullable();
            $table->smallInteger('quantity_pax')->nullable();
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
        Schema::dropIfExists('central_bookings');
    }
}

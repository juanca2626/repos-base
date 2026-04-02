<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRatePlanRoomDateRangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rate_plan_room_date_ranges', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('rate_plan_room_id');
            $table->foreign('rate_plan_room_id')->references('id')->on('rates_plans_rooms');
            $table->date('date_from');
            $table->date('date_to');
            $table->decimal('price_child');
            $table->decimal('price_infant');
            $table->integer('group');
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
        Schema::dropIfExists('rate_plan_room_date_ranges');
    }
}

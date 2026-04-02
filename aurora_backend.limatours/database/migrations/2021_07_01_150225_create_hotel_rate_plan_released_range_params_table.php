<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHotelRatePlanReleasedRangeParamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hotel_rate_plan_released_range_params', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('hotel_rate_plan_released_range_id');
            $table->unsignedBigInteger('room_id')->nullable();
            $table->enum('type', ['room', 'passenger'])->default('room');
            $table->smallInteger('from');
            $table->smallInteger('to');
            $table->smallInteger('qty_released')->default(1);
            $table->enum('type_discount', ['percentage', 'amount'])->default('percentage');
            $table->double('value')->default(0);
            $table->foreign('hotel_rate_plan_released_range_id','htl_r_p_r_r_id')->references('id')->on('hotel_rate_plan_released_ranges');
            $table->foreign('room_id')->references('id')->on('rooms');
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
        Schema::dropIfExists('hotel_rate_plan_released_range_params');
    }
}

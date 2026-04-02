<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceRatePlanCalendarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_rate_plan_calendars', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date');
            $table->unsignedBigInteger('service_rate_id');
            $table->boolean('status')->default(true);
            $table->unsignedInteger('max_ab_offset')->nullable();
            $table->unsignedInteger('min_ab_offset')->nullable();
            $table->unsignedInteger('min_length_stay')->nullable();
            $table->unsignedInteger('max_length_stay')->nullable();
            $table->unsignedInteger('max_occupancy')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('service_rate_id')->references('id')->on('service_rates');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_rate_plan_calendars');
    }
}

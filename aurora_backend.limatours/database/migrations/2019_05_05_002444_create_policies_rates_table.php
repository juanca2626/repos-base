<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class CreatePoliciesRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('policies_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 45);
            $table->string('description', 45);
            $table->boolean('status');
            $table->string('days_apply', 20);
            $table->unsignedInteger('max_ab_offset');
            $table->unsignedInteger('min_ab_offset');
            $table->unsignedInteger('min_length_stay');
            $table->unsignedInteger('max_length_stay');
            $table->unsignedInteger('max_occupancy');
            $table->unsignedBigInteger('policies_cancelation_id');
            $table->unsignedBigInteger('hotel_id');
            $table->foreign('policies_cancelation_id')->references('id')->on('policies_cancelations');
            // $table->foreign('hotel_id')->references('id')->on('hotels');
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
        Schema::dropIfExists('policies_rates');
    }
}

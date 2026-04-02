<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChainsMultiplePropertyHotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chains_multiple_property_hotels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('chains_multiple_property_id');
            $table->foreign('chains_multiple_property_id','chain_multi_prop_hotels_chain_multi_prop_id_foreign')->references('id')->on('chains_multiple_properties');
            $table->unsignedBigInteger('rate_plan_id');
            $table->foreign('rate_plan_id')->references('id')->on('rates_plans');
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
        Schema::dropIfExists('chains_multiple_property_hotels');
    }
}

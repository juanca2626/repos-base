<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class CreateRatesPlansPromotionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rates_plans_promotions', function (Blueprint $table) {
            $table->unsignedBigInteger('rates_plans_id');
            $table->date('promotion_from');
            $table->date('promotion_to');
            $table->timestamps();

            $table->foreign('rates_plans_id')->references('id')->on('rates_plans');
            $table->index('rates_plans_id');
            $table->index('promotion_from');
            $table->index('promotion_to');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rates_plans_promotions');
    }
}

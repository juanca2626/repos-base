<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class CreateRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('rates_plans_calendarys_id');
            $table->tinyInteger('num_adult');
            $table->tinyInteger('num_child');
            $table->tinyInteger('num_infant');
            $table->decimal('price_adult', 10, 4);
            $table->decimal('price_child', 10, 4);
            $table->decimal('price_infant', 10, 4);
            $table->decimal('price_extra', 10, 4);
            $table->decimal('price_total', 10, 4);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('rates_plans_calendarys_id')->references('id')->on('rates_plans_calendarys');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rates');
    }
}

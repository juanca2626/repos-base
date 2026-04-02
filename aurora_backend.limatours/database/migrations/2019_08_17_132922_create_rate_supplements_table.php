<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRateSupplementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rate_supplements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type',['required','optional']);
            $table->boolean('amount_extra')->default(true);
            $table->timestamps();

            $table->unsignedBigInteger('rate_plan_id');
            $table->foreign('rate_plan_id')->references('id')->on('rates_plans');

            $table->unsignedBigInteger('supplement_id');
            $table->foreign('supplement_id')->references('id')->on('suplements');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rate_supplements');
    }
}

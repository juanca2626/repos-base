<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRatePlanAssociationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rate_plan_associations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('rate_plan_id');
            $table->foreign('rate_plan_id')->references('id')->on('rates_plans');
            $table->string('entity');
            $table->bigInteger('object_id');
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
        Schema::dropIfExists('rate_plan_associations');
    }
}

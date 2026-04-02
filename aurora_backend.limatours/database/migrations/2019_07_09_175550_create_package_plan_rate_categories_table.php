<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackagePlanRateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_plan_rate_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('package_plan_rate_id');
            $table->foreign('package_plan_rate_id')->references('id')->on('package_plan_rates');
            $table->unsignedBigInteger('type_class_id');
            $table->foreign('type_class_id')->references('id')->on('type_classes');
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
        Schema::dropIfExists('package_plan_rate_categories');
    }
}

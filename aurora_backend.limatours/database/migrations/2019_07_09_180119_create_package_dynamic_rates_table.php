<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageDynamicRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_dynamic_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('service_type_id');
            $table->foreign('service_type_id')->references('id')->on('service_types');
            $table->unsignedBigInteger('package_plan_rate_category_id');
            $table->foreign('package_plan_rate_category_id')->references('id')->on('package_plan_rate_categories');
            $table->integer('pax_from');
            $table->integer('pax_to');
            $table->decimal('simple', 10, 2);
            $table->decimal('double', 10, 2);
            $table->decimal('triple', 10, 2);
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
        Schema::dropIfExists('package_dynamic_rates');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageFixedSaleRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_fixed_sale_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('package_plan_rate_category_id');
            $table->foreign('package_plan_rate_category_id')->references('id')->on('package_plan_rate_categories');
            $table->decimal('simple', 10, 2);
            $table->decimal('double', 10, 2);
            $table->decimal('triple', 10, 2);
            $table->decimal('child_with_bed', 10, 2);
            $table->decimal('child_without_bed', 10, 2);
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
        Schema::dropIfExists('package_fixed_sale_rates');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageDynamicSaleRateCopiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_dynamic_sale_rate_copies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('object_id');
            $table->bigInteger('package_id');
            $table->unsignedBigInteger('service_type_id');
            $table->unsignedBigInteger('package_plan_rate_category_id');
            $table->integer('pax_from');
            $table->integer('pax_to');
            $table->decimal('simple', 10, 2);
            $table->decimal('double', 10, 2);
            $table->decimal('triple', 10, 2);
            $table->unsignedBigInteger('package_rate_sale_markup_id');
            $table->char('status', 1);
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
        Schema::dropIfExists('package_dynamic_sale_rate_copies');
    }
}

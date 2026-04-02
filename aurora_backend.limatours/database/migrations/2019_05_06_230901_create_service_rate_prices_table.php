<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceRatePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_rate_prices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('service_rate_plan_id')->unsigned()->index();
            $table->foreign('service_rate_plan_id')->references('id')->on('service_rate_plans')->onDelete('cascade');
            $table->integer('pax_from');
            $table->integer('pax_to');
            $table->decimal('price_adult', 8, 2);
            $table->decimal('price_child', 8, 2);
            $table->decimal('price_infant', 8, 2);
            $table->decimal('price_guide', 8, 2);
            $table->boolean('status');
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
        Schema::dropIfExists('service_rate_prices');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuoteDynamicSaleRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quote_dynamic_sale_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date_service');
            $table->unsignedBigInteger('quote_service_id');
            $table->integer('pax_from');
            $table->integer('pax_to');
            $table->float('simple', 10, 2);
            $table->float('double', 10, 2);
            $table->float('triple', 10, 2);

            $table->unsignedBigInteger('quote_category_id');
            $table->foreign('quote_category_id')->references('id')->on('quote_categories');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quote_dynamic_sale_rates');
    }
}

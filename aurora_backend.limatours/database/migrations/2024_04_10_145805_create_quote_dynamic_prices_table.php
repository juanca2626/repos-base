<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuoteDynamicPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quote_dynamic_prices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('quote_id');
            $table->foreign('quote_id')->references('id')->on('quotes')->onDelete('cascade');
            $table->unsignedBigInteger('quote_service_id');
            $table->unsignedBigInteger('object_id');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('type');
            $table->decimal('price_adl', 8, 2);
            $table->decimal('markup', 8, 2);
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
        Schema::dropIfExists('quote_dynamic_prices');
    }
}
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuoteCategoryRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quote_category_rates', function (Blueprint $table) {
            $table->bigIncrements('id'); 
            $table->unsignedBigInteger('quote_category_id')->unsigned();
            $table->string('type'); 
            $table->boolean('optional')->default(0); 
            $table->decimal('total_price', 8, 2);
            $table->timestamps();
            $table->foreign('quote_category_id')->references('id')->on('quote_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quote_category_rates');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuoteServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quote_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('quote_category_id');
            $table->foreign('quote_category_id')->references('id')->on('quote_categories');
            $table->enum('type',['service','hotel']);
            $table->bigInteger('object_id');
            $table->integer('order');
            $table->date('date_in');
            $table->date('date_out');
            $table->integer('nights');
            $table->integer('adult');
            $table->integer('child');
            $table->integer('infant');
            $table->integer('single');
            $table->integer('double');
            $table->integer('triple');
            $table->tinyInteger('triple_active');
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
        Schema::dropIfExists('quote_services');
    }
}

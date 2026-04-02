<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarkupHotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('markup_hotels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('period');         
            $table->decimal('markup', 10, 2); 
            $table->unsignedBigInteger('hotel_id')->unsigned()->index();
            $table->unsignedBigInteger('client_id')->unsigned()->index();            
            $table->foreign('hotel_id')->references('id')->on('hotels');            
            $table->foreign('client_id')->references('id')->on('clients');                            
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
        Schema::dropIfExists('markup_hotels');
    }
}

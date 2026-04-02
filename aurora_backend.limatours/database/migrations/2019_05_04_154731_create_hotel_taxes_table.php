<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class CreateHotelTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hotel_taxes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->double('amount');
            $table->boolean('status')->default(false);
            $table->unsignedBigInteger('tax_id');
            $table->unsignedBigInteger('hotel_id');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tax_id')->references('id')->on('taxes')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('hotel_id')->references('id')->on('hotels')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hotel_taxes');
    }
}

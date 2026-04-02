<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

// @codingStandardsIgnoreLine
class CreateChainsMultiplePropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chains_multiple_properties', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('chain_id');
            $table->bigInteger('quantity');
            $table->string('discount');
            $table->foreign('chain_id')->references('id')->on('chains');
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
        Schema::dropIfExists('chains_multiple_properties');
    }
}

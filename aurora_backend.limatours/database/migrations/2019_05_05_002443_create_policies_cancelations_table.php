<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class CreatePoliciesCancelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('policies_cancelations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 150);
            $table->tinyInteger('check_taxe');
            $table->tinyInteger('check_service');
            $table->unsignedBigInteger('hotel_id');

            $table->timestamps();
            $table->softDeletes();

//            $table->foreign('hotel_id')->references('id')->on('hotels');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('policies_cancelations');
    }
}

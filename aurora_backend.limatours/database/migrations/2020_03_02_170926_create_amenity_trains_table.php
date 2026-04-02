<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAmenityTrainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amenity_trains', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('train_template_id');
            $table->unsignedBigInteger('amenity_id');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('train_template_id')->references('id')->on('train_templates');
            $table->foreign('amenity_id')->references('id')->on('amenities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amenity_trains');
    }
}

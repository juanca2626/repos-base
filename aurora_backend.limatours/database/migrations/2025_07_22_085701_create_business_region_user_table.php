<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBusinessRegionUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_region_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('business_region_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            //claves foreanas
            $table->foreign('business_region_id')->references('id')->on('business_regions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['business_region_id','user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_region_user');
    }
}

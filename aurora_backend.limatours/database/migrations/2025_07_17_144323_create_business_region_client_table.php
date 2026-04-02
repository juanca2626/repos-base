<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBusinessRegionClientTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_region_client', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('business_region_id');
            $table->unsignedBigInteger('client_id');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('business_region_id')
                  ->references('id')
                  ->on('business_regions')
                  ->onDelete('cascade');

            $table->foreign('client_id')
                  ->references('id')
                  ->on('clients')
                  ->onDelete('cascade');

            $table->unique(['business_region_id', 'client_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_region_client');
    }
}

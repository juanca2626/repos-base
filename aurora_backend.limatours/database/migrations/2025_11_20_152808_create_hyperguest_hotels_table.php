<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHyperguestHotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hyperguest_hotels', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('hotel_id')->unique()->comment('ID del hotel en Hyperguest');
            $table->string('name', 500);

            $table->string('country', 5)->nullable();
            $table->string('city', 255)->nullable();
            $table->unsignedInteger('city_id')->nullable();
            $table->string('region', 255)->nullable();

            $table->unsignedBigInteger('chain_id')->nullable();
            $table->string('chain_name', 255)->nullable();

            $table->dateTime('last_updated')->nullable();
            $table->string('version', 100)->nullable();

            $table->string('status', 20)->default('active')->comment('Los posibles valores de este campo son: Approved, Incomplete');

            $table->timestamps();
            $table->softDeletes();
            // Índices recomendados
            $table->index('city_id');
            $table->index('country');
            $table->index('region');
            $table->index('chain_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hyperguest_hotels');
    }
}

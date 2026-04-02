<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class CreateHotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->enum('stars', ['1', '2', '3', '4', '5']);
            $table->string('aurora_code');
            //$table->string('zip_code');
            $table->string('web_site')->nullable();
            $table->tinyInteger('status');
            $table->double('latitude');
            $table->double('longitude');
            $table->time('check_in_time');
            $table->time('check_out_time');
            $table->tinyInteger('percentage_completion');
            $table->unsignedBigInteger('typeclass_id');
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('currency_id');
            $table->unsignedBigInteger('hotel_type_id');
            $table->unsignedBigInteger('hotelcategory_id');
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('state_id');
            $table->unsignedBigInteger('city_id');
            $table->unsignedBigInteger('district_id');
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->boolean('allows_child')->nullable()->default(false);
            $table->boolean('allows_teenagers')->nullable()->default(false);
            $table->integer('min_age_child')->nullable();
            $table->integer('max_age_child')->nullable();
            $table->integer('min_age_teenagers')->nullable();
            $table->integer('max_age_teenagers')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('typeclass_id')->references('id')->on('type_classes');
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->foreign('hotel_type_id')->references('id')->on('hotel_types');
            $table->foreign('hotelcategory_id')->references('id')->on('hotel_categories');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->foreign('state_id')->references('id')->on('states');
            $table->foreign('city_id')->references('id')->on('cities');
            $table->foreign('district_id')->references('id')->on('districts');
            $table->foreign('zone_id')->references('id')->on('zones');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hotels');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataToQuotePassengers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quote_passengers', function (Blueprint $table) {
            $table->longText('dietary_restrictions')->nullable();
            $table->longText('medical_restrictions')->nullable();
            $table->enum('type',['ADL','CHD', 'INF'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quote_passengers', function (Blueprint $table) {
            //
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class AddNewColumnTableBags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bags', function (Blueprint $table) {
            $table->unsignedBigInteger('hotel_id')->default(1);

            $table->foreign('hotel_id')->references('id')->on('hotels');
        });
        Schema::table('bags', function (Blueprint $table) {
            $table->unsignedBigInteger('hotel_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bags', function (Blueprint $table) {
            $table->dropColumn('hotel_id');
            $table->dropForeign('hotel_id');
        });
    }
}

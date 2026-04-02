<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class AddColumnToRatesPlansRooms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rates_plans_rooms', function (Blueprint $table) {
            $table->unsignedBigInteger('channel_id')->default(1);
            $table->foreign('channel_id')->references('id')->on('channels');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rates_plans_rooms', function (Blueprint $table) {
            $table->dropForeign('rates_plans_rooms_channels_id_foreign');
            $table->dropColumn('channel_id');
        });
    }
}

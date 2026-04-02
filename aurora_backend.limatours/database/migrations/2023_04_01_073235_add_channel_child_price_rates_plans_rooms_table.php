<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChannelChildPriceRatesPlansRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rates_plans_rooms', function (Blueprint $table) {
            $table->float('channel_child_price')->default(0)->after('channel_id');
            $table->float('channel_infant_price')->default(0)->after('channel_id');
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
            $table->dropColumn(['channel_child_price']);
            $table->dropColumn(['channel_infant_price']);
        });
    }
}

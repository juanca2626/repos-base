<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateColumnTableBagRates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('bag_rates')->truncate();
        DB::table('rates_plans_rooms')->update(['bag' => 0]);

        Schema::table('bag_rates', function (Blueprint $table) {
            $table->dropForeign(['bag_id']);
            $table->renameColumn('bag_id', 'bag_room_id');
            $table->foreign('bag_room_id')->references('id')->on('bag_rooms');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

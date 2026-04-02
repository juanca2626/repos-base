<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class ModifyColumnTableInventoryBags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('inventories')->truncate();
        DB::table('inventory_bags')->truncate();
        DB::table('activity_log')->truncate();
        
        Schema::table('inventory_bags', function (Blueprint $table) {
            $table->renameColumn('bag_rate_id', 'bag_room_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_bags', function (Blueprint $table) {
            $table->renameColumn('bag_room_id', 'bag_rate_id');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class ModifyColumnsTableInventoryBags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_bags', function (Blueprint $table) {
            $table->renameColumn('quantity', 'inventory_num');
            $table->dropColumn('room_id');
            $table->renameColumn('bag_id', 'bag_rate_id');
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
            $table->renameColumn('inventory_num', 'quantity');
            $table->unsignedBigInteger('room_id');
            $table->renameColumn('bag_rate_id', 'bag_id');
        });
    }
}

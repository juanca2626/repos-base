<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class ModifyColumnsTableInventories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->renameColumn('quantity', 'inventory_num');
            $table->dropColumn('room_id');
            $table->renameColumn('rate_plan_id', 'rate_plan_rooms_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->renameColumn('inventory_num', 'quantity');
            $table->unsignedBigInteger('room_id');
            $table->renameColumn('rate_plan_rooms_id', 'rate_plan_id');
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyTableInventoryBags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_bags', function (Blueprint $table) {
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
        Schema::table('inventory_bags', function (Blueprint $table) {
            $table->dropForeign('bag_room_id');
        });
    }
}

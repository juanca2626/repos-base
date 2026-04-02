<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnTableInventories extends Migration
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
        
        Schema::table('inventories', function (Blueprint $table) {

            $table->unsignedBigInteger('rate_plan_rooms_id')->nullable(false)->change();
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

            $table->unsignedBigInteger('rate_plan_rooms_id')->nullable()->change();
        });
    }
}

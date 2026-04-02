<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultValueColumsInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->integer('inventory_num')->nullable()->change();
            $table->integer('total_booking')->nullable()->change();
            $table->integer('total_canceled')->nullable()->change();
            $table->boolean('locked')->nullable()->change();
            $table->unsignedBigInteger('rate_plan_rooms_id')->nullable()->change();
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

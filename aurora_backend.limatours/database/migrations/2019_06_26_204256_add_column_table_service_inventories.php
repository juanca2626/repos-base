<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnTableServiceInventories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_inventories', function (Blueprint $table) {
            $table->integer('inventory_num')->nullable()->after('date');
            $table->tinyInteger('day')->nullable()->after('service_id');
            $table->boolean('locked')->nullable()->after('total_blocking');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_inventories', function (Blueprint $table) {
            $table->dropColumn('inventory_num');
            $table->dropColumn('day');
            $table->dropColumn('locked');
        });
    }
}

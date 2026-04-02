<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameColumnServiceInventories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_inventories', function (Blueprint $table) {
            $table->renameColumn('total_blocking', 'total_canceled');
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
            $table->renameColumn('total_canceled', 'total_blocking');
        });
    }
}

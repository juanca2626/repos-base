<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->boolean('affected_markup')->default(0)->after('affected_igv');
            $table->unsignedSmallInteger('unit_duration_reserve')->default(2)->after('unit_duration_id')->comment('1 - horas; 2 - dias');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('affected_markup');
            $table->dropColumn('unit_duration_reserve_id');
        });
    }
}

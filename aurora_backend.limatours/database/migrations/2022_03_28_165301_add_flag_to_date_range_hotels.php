<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFlagToDateRangeHotels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('date_range_hotels', function (Blueprint $table) {
            $table->smallInteger('flag_migrate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('date_range_hotels', function (Blueprint $table) {
            $table->dropColumn('flag_migrate');
        });
    }
}

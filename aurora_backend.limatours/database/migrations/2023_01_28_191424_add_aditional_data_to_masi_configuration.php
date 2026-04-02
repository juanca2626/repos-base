<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAditionalDataToMasiConfiguration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('masi_configuration', function (Blueprint $table) {
            $table->date('date')->nullable();
            $table->smallInteger('flag_schedule');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('masi_configuration', function (Blueprint $table) {
            $table->dropColumn('date');
            $table->dropColumn('flag_schedule');
        });
    }
}

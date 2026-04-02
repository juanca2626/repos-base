<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusMigrateToMasiFileDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('masi_file_detail', function (Blueprint $table) {
            $table->smallInteger('status_migrate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('masi_file_detail', function (Blueprint $table) {
            $table->dropColumn('status_migrate');
        });
    }
}

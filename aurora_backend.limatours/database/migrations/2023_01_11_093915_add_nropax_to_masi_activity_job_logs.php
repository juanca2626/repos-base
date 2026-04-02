<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNropaxToMasiActivityJobLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('masi_activity_job_logs', function (Blueprint $table) {
            $table->integer('nropax')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('masi_activity_job_logs', function (Blueprint $table) {
            $table->dropColumn('nropax');
        });
    }
}

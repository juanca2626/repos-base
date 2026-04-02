<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsVistaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vista', function (Blueprint $table) {
            $table->date('to')->nullable(true)->after('type_banner_main');
            $table->date('from')->nullable(true)->after('type_banner_main');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vista', function (Blueprint $table) {
            $table->dropColumn('main');
            $table->dropColumn('to');
            $table->dropColumn('from');
        });
    }
}

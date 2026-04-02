<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPercentToConfigMarkups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('config_markups', function (Blueprint $table) {
            $table->decimal('percent')->nullable();
            $table->decimal('prev')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('config_markups', function (Blueprint $table) {
            $table->dropColumn('percent');
            $table->dropColumn('prev');
        });
    }
}

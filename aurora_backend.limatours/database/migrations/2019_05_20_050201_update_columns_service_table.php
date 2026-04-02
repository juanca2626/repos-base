<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class UpdateColumnsServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('equivalence_aurora', 10)->nullable()->change();
            $table->string('stela_code', 10)->nullable()->change();
            $table->string('equivalence_stela', 10)->nullable()->change();
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
            $table->string('equivalence_aurora', 10)->nullable(false)->change();
            $table->string('stela_code', 10)->nullable(false)->change();
            $table->string('equivalence_stela', 10)->nullable()->change();
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnsQuoteServiceAmountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quote_service_amounts', function (Blueprint $table) {
            $table->float('single',8,4)->default(0);
            $table->float('double',8,4)->default(0);
            $table->float('triple',8,4)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quote_service_amounts', function (Blueprint $table) {
            $table->dropColumn('single',8,4);
            $table->dropColumn('double',8,4);
            $table->dropColumn('triple',8,4);
        });
    }
}

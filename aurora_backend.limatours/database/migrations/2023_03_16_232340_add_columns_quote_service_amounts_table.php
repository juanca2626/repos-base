<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsQuoteServiceAmountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quote_service_amounts', function (Blueprint $table) {
            $table->float('price_teenagers_without_markup',8,2);
            $table->float('price_teenagers',8,2); 
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
            $table->dropColumn('price_teenagers_without_markup');
            $table->dropColumn('price_teenagers'); 
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPricesAdultsChildsToQuoteServiceAmounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quote_service_amounts', function (Blueprint $table) {
            $table->float('amount_adult',8,2);
            $table->float('amount_child',8,2);
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
            $table->dropColumn('amount_adult');
            $table->dropColumn('amount_child');
        });
    }
}

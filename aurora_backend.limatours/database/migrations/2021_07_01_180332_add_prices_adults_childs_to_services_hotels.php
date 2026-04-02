<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPricesAdultsChildsToServicesHotels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quote_service_amounts', function (Blueprint $table) {
            $table->float('single_adults',8,2);
            $table->float('single_childs',8,2);
            $table->float('double_adults',8,2);
            $table->float('double_childs',8,2);
            $table->float('triple_adults',8,2);
            $table->float('triple_childs',8,2);
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
            $table->dropColumn('single_adults');
            $table->dropColumn('single_childs');
            $table->dropColumn('double_adults');
            $table->dropColumn('double_childs');
            $table->dropColumn('triple_adults');
            $table->dropColumn('triple_childs');
        });
    }
}

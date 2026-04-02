<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTableQuoteServiceAmounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quote_service_amounts', function (Blueprint $table) {
            $table->dropColumn('amount');
            $table->dropColumn('single');
            $table->dropColumn('double');
            $table->dropColumn('triple');
            $table->dropColumn('error_in_nights');
            $table->dropColumn('amount_adult');
            $table->dropColumn('amount_child');
            $table->dropColumn('single_adults');
            $table->dropColumn('single_childs');
            $table->dropColumn('double_adults');
            $table->dropColumn('double_childs');
            $table->dropColumn('triple_adults');
            $table->dropColumn('triple_childs');
            $table->string('date_service')->after('quote_service_id');
            $table->float('price_per_night_without_markup',8,2)->default(0)->after('date_service');
            $table->float('price_per_night',8,2)->default(0)->after('price_per_night_without_markup');
            $table->float('price_adult_without_markup',8,2)->default(0)->after('price_per_night');
            $table->float('price_adult',8,2)->default(0)->after('price_adult_without_markup');
            $table->float('price_child_without_markup',8,2)->default(0)->after('price_adult');
            $table->float('price_child',8,2)->default(0)->after('price_child_without_markup');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsQuoteDynamicSaleRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quote_dynamic_sale_rates', function (Blueprint $table) { 
            $table->text('room_types')->nullable()->default('');
            $table->text('rate_meals')->nullable()->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quote_dynamic_sale_rates', function (Blueprint $table) {
            $table->dropColumn('room_types');
            $table->dropColumn('rate_meals');
        });
    }
}

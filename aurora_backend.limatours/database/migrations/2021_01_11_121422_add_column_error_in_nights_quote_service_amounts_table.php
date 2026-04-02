<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnErrorInNightsQuoteServiceAmountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quote_service_amounts', function (Blueprint $table) {
            $table->boolean('error_in_nights')->nullable()->after('triple')->default(0);
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
            $table->dropColumn(['error_in_nights']);
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnQuoteAgeChildIdToQuotePassengersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quote_passengers', function (Blueprint $table) {
            $table->unsignedBigInteger('quote_age_child_id')->nullable();
            $table->foreign('quote_age_child_id')->references('id')->on('quote_age_child');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('quote_passengers', function (Blueprint $table) {
            $table->dropForeign('quote_passengers_quote_age_child_id_foreign');
            $table->dropColumn('quote_age_child_id');
        });

    }
}

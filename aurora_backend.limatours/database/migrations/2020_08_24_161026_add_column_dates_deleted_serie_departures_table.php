<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnDatesDeletedSerieDeparturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('serie_departures', function (Blueprint $table) {
            $table->longText('dates_deleted')->after('dates')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('serie_departures', function (Blueprint $table) {
            $table->dropColumn(['dates_deleted']);
        });
    }
}

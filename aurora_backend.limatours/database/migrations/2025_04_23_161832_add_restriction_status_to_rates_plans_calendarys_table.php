<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRestrictionStatusToRatesPlansCalendarysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rates_plans_calendarys', function (Blueprint $table) {
            $table->addColumn('boolean', 'restriction_status', ['default' => true])->after('max_length_stay');
            $table->addColumn('boolean', 'restriction_arrival', ['default' => true])->after('restriction_status');
            $table->addColumn('boolean', 'restriction_departure', ['default' => true])->after('restriction_arrival');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rates_plans_calendarys', function (Blueprint $table) {
            $table->dropColumn('restriction_status');
            $table->dropColumn('restriction_arrival');
            $table->dropColumn('restriction_departure');
        });
    }
}

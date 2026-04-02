<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultValueColumsRatesPlansCalendarysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rates_plans_calendarys', function (Blueprint $table) {
            $table->unsignedBigInteger('policies_rate_id')->nullable()->change();
            $table->boolean('status')->default(true)->change();
            $table->unsignedInteger('max_ab_offset')->nullable();
            $table->unsignedInteger('min_ab_offset')->nullable();
            $table->unsignedInteger('min_length_stay')->nullable();
            $table->unsignedInteger('max_length_stay')->nullable();
            $table->unsignedInteger('max_occupancy')->nullable();
            $table->unsignedBigInteger('policies_cancelation_id')->nullable();
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
            $table->dropColumn('policies_rate_id')->nullable();
            $table->dropColumn('status')->default(true);
            $table->dropColumn('max_ab_offset')->nullable();
            $table->dropColumn('min_ab_offset')->nullable();
            $table->dropColumn('min_length_stay')->nullable();
            $table->dropColumn('max_length_stay')->nullable();
            $table->dropColumn('max_occupancy')->nullable();
            $table->dropColumn('policies_cancelation_id')->nullable();
        });
    }
}

<?php

use App\RatesPlansCalendarys;
use App\RatesPlansRooms;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class ModifyRatesPlansCalendarys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rates_plans_calendarys', function (Blueprint $table) {
            $table->unsignedBigInteger('rates_plans_room_id')->after('status');
        });

        $ratesPlansCalendarys = RatesPlansCalendarys::all();

        foreach ($ratesPlansCalendarys as $calendary) {
            $ratesPlansRooms = RatesPlansRooms::where('rates_plans_id', $calendary->rates_plan_id)->first();
            $calendary->rates_plans_room_id = $ratesPlansRooms->id;
            $calendary->save();
        }

        Schema::table('rates_plans_calendarys', function (Blueprint $table) {
            $table->dropForeign('rates_plans_calendarys_rates_plan_id_foreign');

            $table->dropColumn('rates_plan_id');

            $table->foreign('rates_plans_room_id')->references('id')->on('rates_plans_rooms');
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
            $table->dropForeign('rates_plans_calendarys_rates_plans_rooms_id_foreign');

            $table->unsignedBigInteger('rates_plan_id')->after('status');
        });

        $ratesPlansCalendarys = RatesPlansCalendarys::all();

        foreach ($ratesPlansCalendarys as $calendary) {
            $ratesPlansRooms = RatesPlansRooms::where('id', $calendary->rates_plans_room_id)->first();
            $calendary->rates_plan_id = $ratesPlansRooms->rates_plans_id;
            $calendary->save();
        }

        Schema::table('rates_plans_calendarys', function (Blueprint $table) {
            $table->dropColumn('rates_plans_room_id');
        });
    }
}

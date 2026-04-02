<?php

use App\RatesPlans;
use App\RatesPlansRooms;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @codingStandardsIgnoreLine
class ModifyRatesPlans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $ratesPlans = RatesPlans::all();

        foreach ($ratesPlans as $ratesPlan) {
            RatesPlansRooms::create([
                'rates_plans_id' => $ratesPlan->id,
                'room_id' => $ratesPlan->room_id,
                'status' => true
            ]);
        }

        Schema::table('rates_plans', function (Blueprint $table) {
            $table->string('code', 45)->after('id');

            $table->dropForeign('rates_plans_room_id_foreign');

            $table->dropColumn('room_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rates_plans', function (Blueprint $table) {
            $table->unsignedBigInteger('room_id')->after('status');

            $table->foreign('room_id')->references('id')->on('rooms');

            $table->dropColumn('code');
        });

        $ratesPlansRooms = RatesPlansRooms::all();

        foreach ($ratesPlansRooms as $ratesPlansRoom) {
            $ratesPlan = RatesPlans::find($ratesPlansRoom->rates_plans_id);
            $ratesPlan->room_id = $ratesPlansRoom->room_id;
            $ratesPlan->save();
        }
    }
}

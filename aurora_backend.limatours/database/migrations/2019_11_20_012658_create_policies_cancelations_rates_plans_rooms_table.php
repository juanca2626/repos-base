<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePoliciesCancelationsRatesPlansRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('policies_cancelations_rates_plans_rooms', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('rates_plans_rooms_id');
            $table->unsignedBigInteger('policies_cancelation_id');

            $table->foreign('rates_plans_rooms_id','rates_plans_rooms_id_foreing')->references('id')->on('rates_plans_rooms');
            $table->foreign('policies_cancelation_id', 'pol_can_pol_rat_pla_room_can_id_foreign')->references('id')->on('policies_cancelations');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('policies_cancelations_rates_plans_rooms');
    }
}

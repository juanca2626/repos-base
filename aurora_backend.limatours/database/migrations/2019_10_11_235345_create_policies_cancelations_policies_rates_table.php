<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePoliciesCancelationsPoliciesRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('policies_cancelations_policies_rates', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('policies_rates_id');
            $table->unsignedBigInteger('policies_cancelation_id');

            $table->foreign('policies_rates_id')->references('id')->on('policies_rates');
            $table->foreign('policies_cancelation_id', 'pol_can_pol_rat_pol_can_id_foreign')->references('id')->on('policies_cancelations');

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
        Schema::dropIfExists('policies_cancelations_policies_rates');
    }
}

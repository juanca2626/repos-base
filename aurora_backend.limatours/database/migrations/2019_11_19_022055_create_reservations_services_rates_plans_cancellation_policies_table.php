<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationsServicesRatesPlansCancellationPoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations_services_rates_plans_cancellation_policies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reservations_services_rates_plans_id');

            $table->unsignedBigInteger('policy_cancelation_id');
            $table->string('name', 150);
            $table->unsignedBigInteger('service_id');

            $table->unsignedBigInteger('policy_cancelations_parameter_id');
            $table->tinyInteger('min_hour');
            $table->tinyInteger('max_hour');
            $table->unsignedInteger('amount');
            $table->tinyInteger('tax');
            $table->tinyInteger('service');
            $table->unsignedBigInteger('penalty_id')->nullable();

            $table->string('penalty_name', 150);

            $table->unsignedBigInteger('create_user_id');
            $table->unsignedBigInteger('update_user_id')->nullable();
            $table->unsignedBigInteger('delete_user_id')->nullable();

            $table->foreign('reservations_services_rates_plans_id','res_ser_rat_can_pol_res_ser_rat_id')->references('id')->on('reservations_services_rates_plans');


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
        Schema::dropIfExists('reservations_services_rates_plans_cancellation_pollicies');
    }
}

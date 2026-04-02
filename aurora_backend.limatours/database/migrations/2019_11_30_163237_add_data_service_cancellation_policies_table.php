<?php

use App\ServiceCancellationPolicies;
use App\ServicePoliticsParameter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddDataServiceCancellationPoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            $service_cancellation = ServiceCancellationPolicies::all();
            if ($service_cancellation->count() == 0) {
                $new_service_cancellation = new ServiceCancellationPolicies();
                $new_service_cancellation->name = 'Política General';
                $new_service_cancellation->status = 1;
                if ($new_service_cancellation->save()) {
                    $new_service_cancellation_parameter = new ServicePoliticsParameter();
                    $new_service_cancellation_parameter->min_hour = 0;
                    $new_service_cancellation_parameter->max_hour = 2;
                    $new_service_cancellation_parameter->unit_duration = 1;
                    $new_service_cancellation_parameter->service_politics_id = $new_service_cancellation->id;
                    $new_service_cancellation_parameter->service_penalty_id = 2;
                    $new_service_cancellation_parameter->amount = 50;
                    $new_service_cancellation_parameter->tax = 0;
                    $new_service_cancellation_parameter->service = 0;
                    $new_service_cancellation_parameter->save();
                }
                DB::table('service_rate_plans')->update(array('service_cancellation_policy_id' => $new_service_cancellation->id));
            }
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

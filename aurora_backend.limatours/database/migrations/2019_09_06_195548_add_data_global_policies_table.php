<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataGlobalPoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('policies_rates', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
        });


        $created_at = date('Y-m-d H:i:s');


        $policie_cancelation_id = DB::table('policies_cancelations')->insertGetId([
            'name' => 'Política tarifa global',
            'status' => 1,
            'created_at' => $created_at
        ]);

        $policy_cancellation_parameters_id = DB::table('policy_cancellation_parameters')->insertGetId([
            'min_day' => 0,
            'max_day' => 0,
            'penalty_id' =>3,
            'created_at' => $created_at,
            'amount' =>NULL,
            'tax' =>0,
            'service' =>0,
            'policy_cancelation_id' => $policie_cancelation_id
        ]);


        $rate_plan_id = DB::table('policies_rates')->insertGetId([
            'name' => 'Política tarifa global',
            'status' => 1,
            'days_apply' => 'all',
            'max_ab_offset' => 0,
            'min_ab_offset' => 0,
            'min_length_stay' => 0,
            'max_length_stay' => 10,
            'max_occupancy' => 3,
            'policies_cancelation_id' => $policie_cancelation_id,
            'created_at' => $created_at
        ]);

        $policie_cancelation_id = DB::table('translations')->insertGetId([
            'type' => 'rate_policies',
            'object_id' => $rate_plan_id,
            'slug' => 'policy_description',
            'value' => 'Política de cancelación global para las tarifas de estela',
            'language_id' => 1,
            'created_at' => $created_at
        ]);

        $policie_cancelation_id = DB::table('translations')->insertGetId([
            'type' => 'rate_policies',
            'object_id' => $rate_plan_id,
            'slug' => 'policy_description',
            'value' => 'Política de cancelación global para las tarifas de estela',
            'language_id' => 2,
            'created_at' => $created_at
        ]);

        $policie_cancelation_id = DB::table('translations')->insertGetId([
            'type' => 'rate_policies',
            'object_id' => $rate_plan_id,
            'slug' => 'policy_description',
            'value' => 'Política de cancelación global para las tarifas de estela',
            'language_id' => 3,
            'created_at' => $created_at
        ]);

        $policie_cancelation_id = DB::table('translations')->insertGetId([
            'type' => 'rate_policies',
            'object_id' => $rate_plan_id,
            'slug' => 'policy_description',
            'value' => 'Política de cancelación global para las tarifas de estela',
            'language_id' => 4,
            'created_at' => $created_at
        ]);
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

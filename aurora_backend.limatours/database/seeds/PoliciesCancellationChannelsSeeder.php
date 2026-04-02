<?php

use App\PoliciesCancelations;
use App\PolicyCancellationParameter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PoliciesCancellationChannelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {
            $created_at = date("Y-m-d H:i:s");

            $policies_cancelations = PoliciesCancelations::where('code', 'CANCELLATION_POLICY_CHANNELS')->first();
            if (!$policies_cancelations) {
                $policies_cancelations = new PoliciesCancelations();
                $policies_cancelations->fill([
                    'name' => 'Política tarifa global de Channels',
                    'status' => 1,
                    'is_channel' => 1,
                    'code' => 'CANCELLATION_POLICY_CHANNELS',
                    'created_at' => $created_at
                ]);

                $policies_cancelations->save();

                $policy_cancellation_parameters = new PolicyCancellationParameter();
                $policy_cancellation_parameters->fill([
                    'min_day' => 0,
                    'max_day' => 0,
                    'penalty_id' => 3,
                    'created_at' => $created_at,
                    'amount' => NULL,
                    'tax' => 0,
                    'service' => 0,
                    'policy_cancelation_id' => $policies_cancelations['id']
                ]);
                $policy_cancellation_parameters->save();
            }
        });
    }
}

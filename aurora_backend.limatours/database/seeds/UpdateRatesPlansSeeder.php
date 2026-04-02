<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateRatesPlansSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('rates_plans')
            ->where('channel_id', 6)
            ->update(['type_channel' => 1]);

        $this->command->info('Se actualizo el rates_plans con el type_channel 1');
    }
}

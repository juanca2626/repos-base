<?php

use App\RatesPlansTypes;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

// @codingStandardsIgnoreLine
class RatesPlansTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (App::environment('local') === true) {
            factory(RatesPlansTypes::class)->times(1)->create();
        }
    }
}

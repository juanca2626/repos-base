<?php

use App\PoliciesCancelations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

// @codingStandardsIgnoreLine
class PoliciesCancelationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        if (App::environment('local') === true) {
            factory(PoliciesCancelations::class)->times(1)->create();
        }
    }
}

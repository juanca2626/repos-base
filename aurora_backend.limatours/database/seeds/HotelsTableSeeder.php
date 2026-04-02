<?php

use App\Hotel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

// @codingStandardsIgnoreLine
class HotelsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (App::environment('local') === true) {
            factory(Hotel::class)->times(10)->create();
        }
    }
}

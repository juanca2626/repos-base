<?php

use App\Facility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class FacilitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (App::environment('local') === true) {
            factory(Facility::class)->times(0)->create();
        }
    }
}

<?php

use App\Chain;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class ChainsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (App::environment('local') === true) {
            factory(Chain::class)->times(10)->create();
        }
    }
}

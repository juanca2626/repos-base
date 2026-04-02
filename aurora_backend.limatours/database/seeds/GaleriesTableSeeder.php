<?php

use App\Galery;
use Illuminate\Database\Seeder;

// @codingStandardsIgnoreLine
class GaleriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Galery::class)->times(10)->create();
    }
}

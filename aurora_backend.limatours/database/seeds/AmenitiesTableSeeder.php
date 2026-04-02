<?php

use App\Amenity;
use Illuminate\Database\Seeder;

// @codingStandardsIgnoreLine
class AmenitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Amenity::class)->times(10)->create();
    }
}

<?php

use App\Tax;
use Illuminate\Database\Seeder;

// @codingStandardsIgnoreLine
class TaxesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Tax::class)->times(10)->create();
    }
}

<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// @codingStandardsIgnoreLine
class PenaltiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('penalties')->insert([
            [
                'id' => 1,
                'name' => 'nigth',
                'status' => 1
            ],
            [
                'id' => 2,
                'name' => 'percentage',
                'status' => 1
            ],
            [
                'id' => 3,
                'name' => 'total_reservation',
                'stats' => 1
            ]
        ]);
    }
}

<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// @codingStandardsIgnoreLine
class ChannelsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('channels')->insert([
            'name' => 'Aurora',
            'status' => 1,
            'code' => 'AURORA'
        ]);
        DB::table('channels')->insert([
            'name' => 'Siteminder',
            'status' => 1,
            'code' => 'SITEMINDER'
        ]);
        DB::table('channels')->insert([
            'name' => 'Erevmax',
            'status' => 1,
            'code' => 'EREVMAX'
        ]);
        DB::table('channels')->insert([
            'name' => 'Travelclick',
            'status' => 1,
            'code' => 'TRAVELCLICK'
        ]);
    }
}

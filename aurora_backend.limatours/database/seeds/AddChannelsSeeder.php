<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddChannelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // DB::table('channels')->insert([
        //     'name' => 'HYPERGUEST PULL',
        //     'code' => 'HYPERGUEST_PULL',
        //     'status' => 1,
        // ]);

        DB::table('channels')
            ->where('id', 6)
            ->update([
                'name' => 'HYPERGUEST'
            ]);
    }
}

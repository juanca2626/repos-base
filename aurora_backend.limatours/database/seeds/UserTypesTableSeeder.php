<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// @codingStandardsIgnoreLine
class UserTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        DB::table('user_types')->insert([
//            [
//                'description' => 'Cliente',
//                'code' => 'C'
//            ],
//            [
//                'description' => 'Proveedor',
//                'code' => 'P'
//            ],
//            [
//                'description' => 'Usuario Interno',
//                'code' => 'U'
//            ]
//        ]);

        DB::table('user_types')->insert([
            [
                'description' => 'Scort',
                'code' => 'S'
            ],
            [
                'description' => 'TC',
                'code' => 'T'
            ]
        ]);
    }
}

<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// @codingStandardsIgnoreLine
class LanguagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        DB::table('languages')->insert([
//            'name' => 'Español',
//            'iso' => 'es'
//        ]);
//        DB::table('languages')->insert([
//            'name' => 'Ingles',
//            'iso' => 'en'
//        ]);
//        DB::table('languages')->insert([
//            'name' => 'Portuguese',
//            'iso' => 'pt'
//        ]);
//        DB::table('languages')->insert([
//            'name' => 'Italian',
//            'iso' => 'it'
//        ]);

        DB::table('languages')->insert([
            'name' => 'Francés',
            'iso' => 'fr',
            'state' => 0
        ]);
        DB::table('languages')->insert([
            'name' => 'ifx',
            'iso' => 'gr',
            'state' => 0
        ]);
        DB::table('languages')->insert([
            'name' => 'ifx',
            'iso' => 'pr',
            'state' => 0
        ]);
        DB::table('languages')
            ->where('iso', 'de')
            ->update([
                'iso' => 'ge'
            ]);
        DB::table('languages')
            ->where('iso', 'ja')
            ->update([
                'iso' => 'jp'
            ]);
    }
}

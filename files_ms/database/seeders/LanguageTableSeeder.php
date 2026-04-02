<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $totalRows = DB::table('languages')->get(['name']);

        if ($totalRows->count() == 0) {
            DB::table('languages')->insert([
                'name' => 'Español',
                'iso' => 'es',
                'active' => 1,
            ]);

            DB::table('languages')->insert([
                'name' => 'English',
                'iso' => 'en',
                'active' => 1,
            ]);

            DB::table('languages')->insert([
                'name' => 'Português',
                'iso' => 'pt',
                'active' => 1,
            ]);

            DB::table('languages')->insert([
                'name' => 'Italiano',
                'iso' => 'it',
                'active' => 1,
            ]);
        }

    }
}

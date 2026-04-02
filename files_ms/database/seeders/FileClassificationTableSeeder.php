<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FileClassificationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $totalRows = DB::table('file_classifications')->get(['name']);

        if ($totalRows->count() == 0) {
            DB::table('file_classifications')->insert([
                'name' => 'Aéreo',
                'iso' => 'A'
            ]);

            DB::table('file_classifications')->insert([
                'name' => 'Hotel',
                'iso' => 'H'
            ]);

            DB::table('file_classifications')->insert([
                'name' => 'N',
                'iso' => 'N'
            ]);

            DB::table('file_classifications')->insert([
                'name' => 'Paquete',
                'iso' => 'P'
            ]);

            DB::table('file_classifications')->insert([
                'name' => 'R',
                'iso' => 'R'
            ]);

            DB::table('file_classifications')->insert([
                'name' => 'S',
                'iso' => 'S'
            ]);

            DB::table('file_classifications')->insert([
                'name' => 'Terrestre',
                'iso' => 'T'
            ]);

            DB::table('file_classifications')->insert([
                'name' => 'X',
                'iso' => 'X'
            ]);
        }

    }
}

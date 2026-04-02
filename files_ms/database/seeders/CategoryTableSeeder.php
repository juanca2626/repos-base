<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $totalRows = DB::table('categories')->get(['name']);
        if ($totalRows->count() == 0) {

            DB::table('categories')->insert([
                'name' => 'Lujo' 
            ]);

            DB::table('categories')->insert([
                'name' => 'Incentivos' 
            ]);

            DB::table('categories')->insert([
                'name' => 'Historia' 
            ]);

            DB::table('categories')->insert([
                'name' => 'Aves' 
            ]);

            DB::table('categories')->insert([
                'name' => 'Sinagoga' 
            ]);

            DB::table('categories')->insert([
                'name' => 'Aventura' 
            ]);

            DB::table('categories')->insert([
                'name' => 'Adicionales' 
            ]);

            DB::table('categories')->insert([
                'name' => 'Gastronomía' 
            ]);

            DB::table('categories')->insert([
                'name' => 'Naturaleza' 
            ]);

            DB::table('categories')->insert([
                'name' => 'Arte' 
            ]);

            DB::table('categories')->insert([
                'name' => 'Familia' 
            ]);
        }
    }
}

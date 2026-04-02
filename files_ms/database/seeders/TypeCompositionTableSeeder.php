<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeCompositionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $totalRows = DB::table('type_compositions')->get(['name']);
        if ($totalRows->count() == 0) {
            DB::table('type_compositions')->insert([
                'name' => 'Servicio Tercero',
                'code' => '1'
            ]);

            DB::table('type_compositions')->insert([
                'name' => '2',
                'code' => '2'
            ]);

            DB::table('type_compositions')->insert([
                'name' => 'Componente',
                'code' => '3'
            ]);

            DB::table('type_compositions')->insert([
                'name' => 'Paquete Propio',
                'code' => '5 '
            ]);

            DB::table('type_compositions')->insert([
                'name' => '6',
                'code' => '6'
            ]);

            DB::table('type_compositions')->insert([
                'name' => '7',
                'code' => '7'
            ]);

            DB::table('type_compositions')->insert([
                'name' => '8',
                'code' => '8'
            ]);

            DB::table('type_compositions')->insert([
                'name' => '9',
                'code' => '9'
            ]);

        }
    }
}

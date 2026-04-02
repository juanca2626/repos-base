<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeComponentServiceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $totalRows = DB::table('type_component_services')->get(['name']);
        if ($totalRows->count() == 0) {

            DB::table('type_component_services')->insert([
                'name' => 'Servicio',
                'code' => 'SVS'
            ]);

            DB::table('type_component_services')->insert([
                'name' => 'Hotel',
                'code' => 'HTL'
            ]);

            DB::table('type_component_services')->insert([
                'name' => 'Transporte',
                'code' => 'TRP'
            ]);

        }
    }
}

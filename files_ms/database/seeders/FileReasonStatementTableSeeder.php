<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FileReasonStatementTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $totalRows = DB::table('file_reason_statement')->get(['name']);
        if ($totalRows->count() == 0) {

            DB::table('file_reason_statement')->insert([
                'name' => 'Motivo 1' 
            ]);

            DB::table('file_reason_statement')->insert([
                'name' => 'Motivo 2' 
            ]);

            DB::table('file_reason_statement')->insert([
                'name' => 'Motivo 3' 
            ]);

        }
    }
}

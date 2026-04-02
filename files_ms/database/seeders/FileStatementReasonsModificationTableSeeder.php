<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FileStatementReasonsModificationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $totalRows = DB::table('file_statement_reason_modifications')->get(['name']);
        if ($totalRows->count() == 0) {

            DB::table('file_statement_reason_modifications')->insert([
                'name' => 'Cálculo inadecuado del sistema' 
            ]); 

            DB::table('file_statement_reason_modifications')->insert([
                'name' => 'Otros' 
            ]); 
        }
    }
}

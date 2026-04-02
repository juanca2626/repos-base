<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WaitingReasonTableSeeder extends Seeder
{
  /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $totalRows = DB::table('waiting_reason')->get(['name']);
        if ($totalRows->count() == 0) {

            DB::table('waiting_reason')->insert([
                'name' => 'Waiting reason 1' 
            ]);

            DB::table('waiting_reason')->insert([
                'name' => 'Otros' 
            ]);
 

        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        DB::table('file_service_time')->insert([
            [
                'name' => 'Full Day',
                'code' => 'full day',
                'status' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Half Day',
                'code' => 'half Day',
                'status' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

    }
}

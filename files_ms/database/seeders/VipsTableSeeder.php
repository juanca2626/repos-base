<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VipsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $totalRows = DB::table('vips')->get(['name']);
        if($totalRows->count() == 0)
        {
            $vips = [
                [
                    "iso" => "A",
                    "name" => "Nuevo cliente",
                    "entity" => "file",
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ],
                [
                    "iso" => "B",
                    "name" => "Funcionario de una cuenta",
                    "entity" => "file",
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ],
                [
                    "iso" => "C",
                    "name" => "Pax recomendado",
                    "entity" => "file",
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ],
            ];
    
            foreach($vips as $vip)
            {
                DB::table('vips')->insert($vip);
            }
        }
    }
}

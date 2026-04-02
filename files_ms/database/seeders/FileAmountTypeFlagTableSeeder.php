<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FileAmountTypeFlagTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $totalRows = DB::table('file_amount_type_flags')->get(['name']);
        if($totalRows->count() == 0)
        {
            $vips = [
                [ 
                    "name" => "Abierto",
                    "description" => "Costo abierto, puede cambiar por negociaciones",
                    "icon" => "",
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ],
                [
                    "name" => "Cerrado",
                    "description" => "Costo cerrado, sin cambios por negociaciones",
                    "icon" => "",
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ],
                [
                    "name" => "Bloqueado",
                    "description" => "Costo modificado por especialista",
                    "icon" => "",
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ],
            ];
    
            foreach($vips as $vip)
            {
                DB::table('file_amount_type_flags')->insert($vip);
            }
        }
    }
}

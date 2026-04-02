<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FileAmountReasonTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $totalRows = DB::table('file_amount_reasons')->get(['name']);
        if($totalRows->count() == 0)
        {
            $file_amount_reasons = [
                [
                    "name" => "Backend",
                    "influences_sale" => false,
                    "area" => "COMERCIAL",
                    "visible" => true,
                    "process" => "modificar_costo",
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ],
                [
                    "name" => "Descuento para cliente",
                    "influences_sale" => true,
                    "area" => "COMERCIAL",
                    "visible" => true,
                    "process" => "modificar_costo",
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ],
                [
                    "name" => "Cartas premiun",
                    "influences_sale" => false,
                    "area" => "COMERCIAL",
                    "visible" => true,
                    "process" => "modificar_costo",
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ],
                [
                    "name" => "Migración",
                    "influences_sale" => false,
                    "area" => "COMERCIAL",
                    "visible" => true,
                    "process" => "modificar_costo",
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ],
                [
                    "name" => "Descuento por mal servicio",
                    "influences_sale" => false,
                    "area" => "OPE",
                    "visible" => true,
                    "process" => "modificar_costo",
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ],
                [
                    "name" => "Negociación en Ope",
                    "influences_sale" => false,
                    "area" => "OPE",
                    "visible" => true,
                    "process" => "modificar_costo",
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ],
                [
                    "name" => "Error Ope",
                    "influences_sale" => false,
                    "area" => "OPE",
                    "visible" => true,
                    "process" => "modificar_costo",
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ],
                [
                    "name" => "Candado Abierto",
                    "influences_sale" => false,
                    "area" => "NEG",
                    "visible" => false,
                    "process" => "",
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ],
                [
                    "name" => "Candado Cerrado",
                    "influences_sale" => false,
                    "area" => "NEG",
                    "visible" => false,
                    "process" => "",
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ],
                [
                    "name" => "Hotel exonera penalidad",
                    "influences_sale" => true,
                    "area" => "COMERCIAL",
                    "visible" => true,
                    "process" => "exonerar_penalidad",
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ],
                [
                    "name" => "Reserva migra a otro file",
                    "influences_sale" => true,
                    "area" => "COMERCIAL",
                    "visible" => true,
                    "process" => "exonerar_penalidad",
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ],
                [
                    "name" => "File asume penalidad",
                    "influences_sale" => true,
                    "area" => "COMERCIAL",
                    "visible" => true,
                    "process" => "exonerar_penalidad",
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ],
                [
                    "name" => "EC asume penalidad",
                    "influences_sale" => true,
                    "area" => "COMERCIAL",
                    "visible" => true,
                    "process" => "exonerar_penalidad",
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ],
                [
                    "name" => "Otro",
                    "influences_sale" => true,
                    "area" => "COMERCIAL",
                    "visible" => true,
                    "process" => "exonerar_penalidad",
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ],
                [
                    "name" => "Cancelación",
                    "influences_sale" => true,
                    "area" => "COMERCIAL",
                    "visible" => false,
                    "process" => "",
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ],
            ];
    
            foreach($file_amount_reasons as $reason)
            {
                DB::table('file_amount_reasons')->insert($reason);
            }
        }
    }
}

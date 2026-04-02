<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusReasonTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $totalRows = DB::table('status_reasons')->get(['name']);
        if($totalRows->count() == 0)
        {
            DB::table('status_reasons')->insert([
                'status_iso' => 'OK',
                'user_id' => 1,
                'name' => 'Primera apertura del File',
                'visible' => 1,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);

            DB::table('status_reasons')->insert([
                'status_iso' => 'OK',
                'user_id' => 1,
                'name' => 'Reaperturado - Devoluciones al cliente',
                'visible' => 1,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);

            DB::table('status_reasons')->insert([
                'status_iso' => 'OK',
                'user_id' => 1,
                'name' => 'Reaperturado - Cobro adicional',
                'visible' => 1,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);
            
            DB::table('status_reasons')->insert([
                'status_iso' => 'OK',
                'user_id' => 1,
                'name' => 'Reaperturado - Reversión total del File',
                'visible' => 1,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);
            
            DB::table('status_reasons')->insert([
                'status_iso' => 'CE',
                'user_id' => 1,
                'name' => 'Cerrado ',
                'visible' => 1,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);

            DB::table('status_reasons')->insert([
                'status_iso' => 'XL',
                'user_id' => 1,
                'name' => 'Anulado',
                'visible' => 1,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);

            DB::table('status_reasons')->insert([
                'status_iso' => 'XL',
                'user_id' => 1,
                'name' => 'Anulado - CAMBIO DE FECHA (NO TIENE FECHA DEFINIDA)',
                'visible' => 1,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);

            DB::table('status_reasons')->insert([
                'status_iso' => 'XL',
                'user_id' => 1,
                'name' => 'Anulado - CAMBIO DE DESTINO',
                'visible' => 1,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);

            DB::table('status_reasons')->insert([
                'status_iso' => 'XL',
                'user_id' => 1,
                'name' => 'Anulado - SALUD',
                'visible' => 1,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);

            DB::table('status_reasons')->insert([
                'status_iso' => 'XL',
                'user_id' => 1,
                'name' => 'Anulado - CLIENTE DEJO DE TRABAJAR CON LITO',
                'visible' => 1,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);

            DB::table('status_reasons')->insert([
                'status_iso' => 'XL',
                'user_id' => 1,
                'name' => 'Anulado - PRECIO',
                'visible' => 1,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);

            DB::table('status_reasons')->insert([
                'status_iso' => 'XL',
                'user_id' => 1,
                'name' => 'Anulado - SITUACION POLITICA/SOCIAL EN PAIS DE ORIGEN',
                'visible' => 1,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);

            DB::table('status_reasons')->insert([
                'status_iso' => 'XL',
                'user_id' => 1,
                'name' => 'Anulado - SITUACION POLITICA/SOCIAL EN PAIS DE DESTINO PERU',
                'visible' => 1,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);

            DB::table('status_reasons')->insert([
                'status_iso' => 'PF',
                'user_id' => 1,
                'name' => 'Por Facturar',
                'visible' => 1,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);
            
            DB::table('status_reasons')->insert([
                'status_iso' => 'BL',
                'user_id' => 1,
                'name' => 'Bloqueado',
                'visible' => 1,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);
            
            DB::table('status_reasons')->insert([
                'status_iso' => '',
                'user_id' => 1,
                'name' => 'Otros',
                'visible' => 1,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);             
            
        }
    }
}

<?php

use Illuminate\Database\Seeder;

class EndPointWebServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'description' => 'Creacion de File SpeRez',
                'endpoint' => 'http://genero.limatours.com.pe:8097/AllotWebSpeRez?wsdl',
                'status' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],
            [
                'description' => 'Servicios BreakDrown Precios',
                'endpoint' => 'http://genero.limatours.com.pe:8093/ServiceBreakDownPrecios?wsdl',
                'status' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],
            [
                'description' => 'Service Tabla Precios',
                'endpoint' => 'http://genero.limatours.com.pe:8098/ServiceTablaPrecios?wsdl',
                'status' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],
            [
                'description' => 'Presupuesto Pedido',
                'endpoint' => 'http://genero.limatours.com.pe:8092/WS_CCPresupuestoPedido?wsdl',
                'status' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],
            [
                'description' => 'Registro de Pasajeros',
                'endpoint' => 'http://genero.limatours.com.pe:8203/WS_RegistroPasajero?wsdl',
                'status' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],
            [
                'description' => 'Creacion de File (Aurora 1)',
                'endpoint' => 'http://genero.limatours.com.pe:8095/AllocationWeb?wsdl',
                'status' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],
            [
                'description' => 'Creacion de File (Aurora 2)',
                'endpoint' => 'http://genero.limatours.com.pe:8199/WS_NuevoFile?wsdl',
                'status' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],
            [
                'description' => 'Cancelación de File',
                'endpoint' => 'http://genero.limatours.com.pe:8205/WS_CancelFile?wsdl',
                'status' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],
            [
                'description' => 'WS Tablero File',
                'endpoint' => 'http://genero.limatours.com.pe:8201/WS_TableroFile?wsdl',
                'status' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ],
        ];

        \Illuminate\Support\Facades\DB::table('endpoint_web_services')->insert($data);
    }
}

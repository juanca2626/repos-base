<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTranslationManageHotelSelerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'clientsmanageclienthotel.clients_locked','value'=>'Clientes Bloqueados','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'clientsmanageclienthotel.clients_locked','value'=>'Locked customers','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'clientsmanageclienthotel.clients_locked','value'=>'Locked customers','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'clientsmanageclienthotel.clients_locked','value'=>'Locked customers','language_id'=>4]
        );

        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'clientsmanageclienthotel.available_clients','value'=>'Clientes Disponibles','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'clientsmanageclienthotel.available_clients','value'=>'Available customers','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'clientsmanageclienthotel.available_clients','value'=>'Available customers','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'clientsmanageclienthotel.available_clients','value'=>'Available customers','language_id'=>4]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

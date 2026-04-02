<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTranslationFieldsManageclient extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'clientsmanageclienthotel.available_hotels_rates','value'=>'Tarifas Disponibles','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'clientsmanageclienthotel.available_hotels_rates','value'=>'Available Rates','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'clientsmanageclienthotel.available_hotels_rates','value'=>'Available Rates','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'clientsmanageclienthotel.available_hotels_rates','value'=>'Available Rates','language_id'=>4]
        );

        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'clientsmanageclienthotel.hotels_rates_blocked','value'=>'Tarifas Bloqueadas','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'clientsmanageclienthotel.hotels_rates_blocked','value'=>'Locked Rates','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'clientsmanageclienthotel.hotels_rates_blocked','value'=>'Locked Rates','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'clientsmanageclienthotel.hotels_rates_blocked','value'=>'Locked Rates','language_id'=>4]
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

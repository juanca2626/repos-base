<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTranslationPackageQuotes2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.quote','value'=>'Cotizar','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.quote','value'=>'Quote','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.quote','value'=>'Quote','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.quote','value'=>'Quote','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.add_hotel','value'=>'Agregar Hotel','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.add_hotel','value'=>'Add Hotel','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.add_hotel','value'=>'Add Hotel','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.add_hotel','value'=>'Add Hotel','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.add_service','value'=>'Agregar Servicio','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.add_service','value'=>'Add Service','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.add_service','value'=>'Add Service','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.add_service','value'=>'Add Service','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.general_data','value'=>'Datos Generales','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.general_data','value'=>'General Data','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.general_data','value'=>'General Data','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.general_data','value'=>'General Data','language_id'=>4]
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

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTranslationListPackage extends Migration
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
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.error.messages.select_type_package','value'=>'Por favor seleccione al menos un tipo de paquete para filtrar','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.error.messages.select_type_package','value'=>'Please select at least one type of package to filter','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.error.messages.select_type_package','value'=>'Please select at least one type of package to filter','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.error.messages.select_type_package','value'=>'Please select at least one type of package to filter','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.packages','value'=>'Paquetes','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.packages','value'=>'Packages','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.packages','value'=>'Packages','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.packages','value'=>'Packages','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.extensions','value'=>'Extensiones','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.extensions','value'=>'Extensions','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.extensions','value'=>'Extensions','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.extensions','value'=>'Extensions','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.package','value'=>'Paquete','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.package','value'=>'Package','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.package','value'=>'Package','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.package','value'=>'Package','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.extension','value'=>'Extension','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.extension','value'=>'Extension','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.extension','value'=>'Extension','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.extension','value'=>'Extension','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.price_from','value'=>'Precio desde','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.price_from','value'=>'Price from','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.price_from','value'=>'Price from','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.price_from','value'=>'Price from','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.type','value'=>'Tipo','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.type','value'=>'Type','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.type','value'=>'Type','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.type','value'=>'Type','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.rates_updated','value'=>'Tarifas actualizadas','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.rates_updated','value'=>'Rates updated','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.rates_updated','value'=>'Rates updated','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.rates_updated','value'=>'Rates updated','language_id'=>4]
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

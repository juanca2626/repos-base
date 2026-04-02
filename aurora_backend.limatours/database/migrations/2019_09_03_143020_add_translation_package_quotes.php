<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTranslationPackageQuotes extends Migration
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
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.quotes','value'=>'Cotizaciones','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.quotes','value'=>'Quotes','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.quotes','value'=>'Quotes','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packages.quotes','value'=>'Quotes','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.cost','value'=>'Costo','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.cost','value'=>'Cost','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.cost','value'=>'Cost','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.cost','value'=>'Cost','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.sale','value'=>'Venta','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.sale','value'=>'Sale','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.sale','value'=>'Sale','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.sale','value'=>'Sale','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.blocking','value'=>'Disponibilidad','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.blocking','value'=>'Availability','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.blocking','value'=>'Availability','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.blocking','value'=>'Availability','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.rates','value'=>'Tarifas','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.rates','value'=>'Rates','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.rates','value'=>'Rates','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.rates','value'=>'Rates','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.categories','value'=>'Categorías','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.categories','value'=>'Categories','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.categories','value'=>'Categories','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.categories','value'=>'Categories','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.period','value'=>'Periodo','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.period','value'=>'Period','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.period','value'=>'Period','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.period','value'=>'Period','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.namerate','value'=>'Nombre de Tarifa','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.namerate','value'=>'Name Rate','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.namerate','value'=>'Name Rate','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.namerate','value'=>'Name Rate','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.validitydate','value'=>'Fecha de validez','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.validitydate','value'=>'Validity Date','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.validitydate','value'=>'Validity Date','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.validitydate','value'=>'Validity Date','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.quantitypax','value'=>'Cantidad de PAX','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.quantitypax','value'=>'Quantity Pax','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.quantitypax','value'=>'Quantity Pax','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.quantitypax','value'=>'Quantity Pax','language_id'=>4]
        );

        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.adult','value'=>'Adulto','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.adult','value'=>'Adult','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.adult','value'=>'Adult','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.adult','value'=>'Adult','language_id'=>4]
        );

        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.child','value'=>'Niño','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.child','value'=>'Child','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.child','value'=>'Child','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.child','value'=>'Child','language_id'=>4]
        );

        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.infant','value'=>'Infante','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.infant','value'=>'Infant','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.infant','value'=>'Infant','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.infant','value'=>'Infant','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.agerange','value'=>'Rango de edad','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.agerange','value'=>'Age range','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.agerange','value'=>'Age range','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.agerange','value'=>'Age range','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.selectcategory','value'=>'Seleccionar categoría','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.selectcategory','value'=>'Select Category','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.selectcategory','value'=>'Select Category','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.selectcategory','value'=>'Select Category','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.category','value'=>'Categoría','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.category','value'=>'Category','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.category','value'=>'Category','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.category','value'=>'Category','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.package_physical_intensity','value'=>'Intensidad Física','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.package_physical_intensity','value'=>'Physical Intensity','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.package_physical_intensity','value'=>'Physical Intensity','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.package_physical_intensity','value'=>'Physical Intensity','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.package_recommended','value'=>'Recomendado','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.package_recommended','value'=>'Recommended','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.package_recommended','value'=>'Recommended','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.package_recommended','value'=>'Recommended','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.copy_from_category','value'=>'Copiar de categoría','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.copy_from_category','value'=>'Copy from category','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.copy_from_category','value'=>'Copy from category','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.copy_from_category','value'=>'Copy from category','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.configurations','value'=>'Configuraciones','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.configurations','value'=>'Configurations','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.configurations','value'=>'Configurations','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.configurations','value'=>'Configurations','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.gallery','value'=>'Galería','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.gallery','value'=>'Gallery','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.gallery','value'=>'Gallery','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.gallery','value'=>'Gallery','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.outputs','value'=>'Salidas','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.outputs','value'=>'Outputs','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.outputs','value'=>'Outputs','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.outputs','value'=>'Outputs','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.texts','value'=>'Textos','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.texts','value'=>'Texts','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.texts','value'=>'Texts','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'package.texts','value'=>'Texts','language_id'=>4]
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

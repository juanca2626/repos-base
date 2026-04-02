<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTranslationsToDoctypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE translations CHANGE COLUMN type type ENUM(
                    'account',
                    'amenity',
                    'channel',
                    'chain',
                    'city',
                    'client',
                    'country',
                    'currency',
                    'district',
                    'facility',
                    'hotel',
                    'hotelcategory',
                    'hoteltype',
                    'meal',
                    'room',
                    'roomtype',
                    'state',
                    'suplement',
                    'typeclass',
                    'zone',
                    'tag',
                    'physicalintensity',
                    'requirement',
                    'experience',
                    'classification',
                    'servicetype',
                    'servicealert',
                    'servicecategory',
                    'servicesubcategory',
                    'unitduration',
                    'servicetypeactivity',
                    'inclusion',
                    'unit',
                    'restriction',
                    'rates_plan',
                    'servicerate',
                    'label',
                    'taggroup',
                    'rate_policies',
                    'rate_plan_room',
                    'docs'
        ) NOT NULL");

        DB::table('translations')->insert(
            ['type' => 'docs', 'object_id' => 1,'slug'=>'CEX','value'=>'Carnet de Extranjería','language_id'=>1]
        );

        DB::table('translations')->insert(
            ['type' => 'docs', 'object_id' => 2,'slug'=>'DNI','value'=>'Documento Nacional de Identidad','language_id'=>1]
        );

        DB::table('translations')->insert(
            ['type' => 'docs', 'object_id' => 3,'slug'=>'PAS','value'=>'Pasaporte','language_id'=>1]
        );

        DB::table('translations')->insert(
            ['type' => 'docs', 'object_id' => 4,'slug'=>'RUC','value'=>'Registro Único de Contribuyente','language_id'=>1]
        );

        DB::table('translations')->insert(
            ['type' => 'docs', 'object_id' => 5,'slug'=>'OTR','value'=>'Otros tipos de documentos','language_id'=>1]
        );

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('doctypes', function (Blueprint $table) {
            //
        });
    }
}

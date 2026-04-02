<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTranslationsExportExcelHotel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('translations')->insert(
            ['type' => 'th', 'object_id' => 0,'slug'=>'export_excel_hotel.hotel_code','value'=>'Codigo','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'th', 'object_id' => 0,'slug'=>'export_excel_hotel.hotel_code','value'=>'Code','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'th', 'object_id' => 0,'slug'=>'export_excel_hotel.hotel_code','value'=>'Code','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'th', 'object_id' => 0,'slug'=>'export_excel_hotel.hotel_code','value'=>'Code','language_id'=>4]
        );

        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'export_excel_hotel.hotel','value'=>'Hotel','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'export_excel_hotel.hotel','value'=>'Hotel','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'export_excel_hotel.hotel','value'=>'Hotel','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'export_excel_hotel.hotel','value'=>'Hotel','language_id'=>4]
        );

        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'export_excel_hotel.rate','value'=>'Hotel','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'export_excel_hotel.rate','value'=>'Hotel','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'export_excel_hotel.rate','value'=>'Hotel','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'export_excel_hotel.rate','value'=>'Hotel','language_id'=>4]
        );

        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'export_excel_hotel.from','value'=>'Desde','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'export_excel_hotel.from','value'=>'From','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'export_excel_hotel.from','value'=>'From','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'export_excel_hotel.from','value'=>'From','language_id'=>4]
        );

        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'export_excel_hotel.to','value'=>'Hasta','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'export_excel_hotel.to','value'=>'To','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'export_excel_hotel.to','value'=>'To','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'export_excel_hotel.to','value'=>'To','language_id'=>4]
        );

        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'export_excel_hotel.percentage_adult','value'=>'$./Adulto','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'export_excel_hotel.percentage_adult','value'=>'$./Adult','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'export_excel_hotel.percentage_adult','value'=>'$./Adult','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'export_excel_hotel.percentage_adult','value'=>'$./Adult','language_id'=>4]
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

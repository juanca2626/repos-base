<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTranslationRateSupplementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_details','value'=>'Detalles','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_details','value'=>'Details','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_details','value'=>'Details','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_details','value'=>'Details','language_id'=>4]
        );

        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement','value'=>'Suplemento','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement','value'=>'Supplement','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement','value'=>'Supplement','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement','value'=>'Supplement','language_id'=>4]
        );

        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_from','value'=>'Desde','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_from','value'=>'From','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_from','value'=>'From','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_from','value'=>'From','language_id'=>4]
        );

        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_to','value'=>'Hasta','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_to','value'=>'To','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_to','value'=>'To','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_to','value'=>'To','language_id'=>4]
        );

        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_price_per_room','value'=>'Precio por habitación','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_price_per_room','value'=>'Price per room','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_price_per_room','value'=>'Price per room','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_price_per_room','value'=>'Price per room','language_id'=>4]
        );

        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_price_per_person','value'=>'Precio por persona','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_price_per_person','value'=>'Price per person','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_price_per_person','value'=>'Price per person','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_price_per_person','value'=>'Price per person','language_id'=>4]
        );

        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_min_age','value'=>'Edad minima','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_min_age','value'=>'Min Age','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_min_age','value'=>'Min Age','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_min_age','value'=>'Min Age','language_id'=>4]
        );

        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_max_age','value'=>'Edad maxima','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_max_age','value'=>'Max Age','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_max_age','value'=>'Max Age','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotel.supplement_max_age','value'=>'Max Age','language_id'=>4]
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

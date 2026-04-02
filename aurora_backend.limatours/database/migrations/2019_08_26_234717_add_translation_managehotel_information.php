<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTranslationManagehotelInformation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelinformation.principal','value'=>'Principal','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelinformation.principal','value'=>'Principal','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelinformation.principal','value'=>'Principal','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelinformation.principal','value'=>'Principal','language_id'=>4]
        );

        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelinformation.principal','value'=>'Principal','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelinformation.principal','value'=>'Principal','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelinformation.principal','value'=>'Principal','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelinformation.principal','value'=>'Principal','language_id'=>4]
        );  
        
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelinformation.password','value'=>'Clave','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelinformation.password','value'=>'Clave','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelinformation.password','value'=>'Clave','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelinformation.password','value'=>'Clave','language_id'=>4]
        );          

        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelinformation.password','value'=>'Clave','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelinformation.password','value'=>'Password','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelinformation.password','value'=>'Password','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelinformation.password','value'=>'Password','language_id'=>4]
        );          

        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelinformation.confirm_password','value'=>'Confirmar Clave','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelinformation.confirm_password','value'=>'Confirm Password','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelinformation.confirm_password','value'=>'Confirm Password','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelinformation.confirm_password','value'=>'Confirm Password','language_id'=>4]
        ); 
        
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelconfiguration.days_before','value'=>'días antes del check-in','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelconfiguration.days_before','value'=>'days before check-in','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelconfiguration.days_before','value'=>'days before check-in','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelconfiguration.days_before','value'=>'days before check-in','language_id'=>4]
        );        

        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelconfiguration.penality','value'=>'Penalidad','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelconfiguration.penality','value'=>'Penality','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelconfiguration.penality','value'=>'Penality','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelconfiguration.penality','value'=>'Penality','language_id'=>4]
        );            

        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelratesratescost.add','value'=>'Agregar','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelratesratescost.add','value'=>'Add','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelratesratescost.add','value'=>'Add','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'hotelsmanagehotelratesratescost.add','value'=>'Add','language_id'=>4]
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

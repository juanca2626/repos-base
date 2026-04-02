<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTranslationPackageQuotesAvailability extends Migration
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
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.from','value'=>'Desde','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.from','value'=>'From','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.from','value'=>'From','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.from','value'=>'From','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.to','value'=>'Hasta','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.to','value'=>'To','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.to','value'=>'To','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesquote.to','value'=>'To','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.avail','value'=>'Disponible','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.avail','value'=>'Avail','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.avail','value'=>'Avail','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.avail','value'=>'Avail','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.assign','value'=>'Asignar','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.assign','value'=>'Assign','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.assign','value'=>'Assign','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.assign','value'=>'Assign','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.unblock','value'=>'Desbloquear','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.unblock','value'=>'Unblock','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.unblock','value'=>'Unblock','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.unblock','value'=>'Unblock','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.block','value'=>'Bloquear','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.block','value'=>'Block','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.block','value'=>'Block','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.block','value'=>'Block','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.advancedoptions','value'=>'Opciones Avanzadas','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.advancedoptions','value'=>'Advanced Options','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.advancedoptions','value'=>'Advanced Options','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.advancedoptions','value'=>'Advanced Options','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.assignbydates','value'=>'Asignar por fechas','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.assignbydates','value'=>'Assign by Dates','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.assignbydates','value'=>'Assign by Dates','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.assignbydates','value'=>'Assign by Dates','language_id'=>4]
        );
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.blockbydates','value'=>'Bloquear por fechas','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.blockbydates','value'=>'Block by Dates','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.blockbydates','value'=>'Block by Dates','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'packagesmanageavailability.blockbydates','value'=>'Block by Dates','language_id'=>4]
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

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTranslationFieldsRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'roles.active-all','value'=>'Activar todos','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'roles.active-all','value'=>'Activate all','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'roles.active-all','value'=>'Activate all','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'roles.active-all','value'=>'Activate all','language_id'=>4]
        );

        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'roles.apply','value'=>'Aplicar','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'roles.apply','value'=>'Apply','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'roles.apply','value'=>'Apply','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'roles.apply','value'=>'Apply','language_id'=>4]
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

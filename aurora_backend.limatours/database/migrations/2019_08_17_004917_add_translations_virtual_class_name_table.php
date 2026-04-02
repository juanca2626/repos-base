<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTranslationsVirtualClassNameTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'virtualclass.title','value'=>'Clases Virtuales','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'virtualclass.title','value'=>'Virtual Classes','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'virtualclass.title','value'=>'Virtual Classes','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'virtualclass.title','value'=>'Virtual Classes','language_id'=>4]
        );

        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'virtualclass.name','value'=>'Clase Virtual','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'virtualclass.name','value'=>'Virtual Class','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'virtualclass.name','value'=>'Virtual Class','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'virtualclass.name','value'=>'Virtual Class','language_id'=>4]
        );

        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'virtualclass.type_class_id','value'=>'Clase','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'virtualclass.type_class_id','value'=>'Class','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'virtualclass.type_class_id','value'=>'Class','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'virtualclass.type_class_id','value'=>'Class','language_id'=>4]
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

<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'clientsmanageclientseller.firstname','value'=>'Nombre','language_id'=>1]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'clientsmanageclientseller.firstname','value'=>'First Name','language_id'=>2]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'clientsmanageclientseller.firstname','value'=>'First Name','language_id'=>3]
        );
        DB::table('translations')->insert(
            ['type' => 'label', 'object_id' => 0,'slug'=>'clientsmanageclientseller.firstname','value'=>'First Name','language_id'=>4]
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

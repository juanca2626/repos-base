<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDoctypesEnglishToTranslation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('translations')->insert(
            ['type' => 'docs', 'object_id' => 1,'slug'=>'CEX','value'=>'Immigration Card','language_id'=>2]
        );

        DB::table('translations')->insert(
            ['type' => 'docs', 'object_id' => 2,'slug'=>'DNI','value'=>'National Identity Document','language_id'=>2]
        );

        DB::table('translations')->insert(
            ['type' => 'docs', 'object_id' => 3,'slug'=>'PAS','value'=>'Passport','language_id'=>2]
        );

        DB::table('translations')->insert(
            ['type' => 'docs', 'object_id' => 4,'slug'=>'RUC','value'=>'Single Taxpayer Registration','language_id'=>2]
        );

        DB::table('translations')->insert(
            ['type' => 'docs', 'object_id' => 5,'slug'=>'OTR','value'=>'Other types of documents','language_id'=>2]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('translation', function (Blueprint $table) {
            //
        });
    }
}

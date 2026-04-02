<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataTypeClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $file_type_classes = File::get("database/data/type_classes.json");
        $data_json_type_classes = json_decode($file_type_classes, true);

        $created_at = date("Y-m-d H:i:s");

        DB::transaction(function () use ($data_json_type_classes,$created_at) {
            foreach ($data_json_type_classes as $data_type_class) {

                $idTypeClass = DB::table('type_classes')->insertGetId([
                    'code' => $data_type_class['code'],
                    'created_at' => $created_at,
                    'updated_at' => $created_at
                ]);
                foreach ($data_type_class['translations'] as $data_translations) {
                    DB::table('translations')->insert([
                        'type' => "typeclass",
                        'object_id' => $idTypeClass,
                        'slug'=>"typeclass_name",
                        'value' => $data_translations['value'],
                        'language_id' =>  $data_translations['language_id'],
                        'created_at' => $created_at,
                        'updated_at' => $created_at
                    ]);
                }
            }
        });
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

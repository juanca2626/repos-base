<?php

use App\Language;
use App\Http\Traits\Translations;
use App\TypeClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

// @codingStandardsIgnoreLine
class TypesClassTableSeeder extends Seeder
{
    use Translations;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        if (App::environment('local') === true) {
//            $languages = Language::select('id')->get();
//
//            $newTypeClass = new TypeClass();
//            $newTypeClass->save();
//
//            $translations = [];
//
//            foreach ($languages as $language) {
//                $translations[$language->id] = ['id' => '', 'typeclass_name' => 'Basico'];
//            }
//
//            $this->saveTranslation($translations, 'typeclass', $newTypeClass->id);
//        }

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
}

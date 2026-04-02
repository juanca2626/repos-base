<?php

use App\Classification;
use App\Language;
use App\Http\Traits\Translations;
use App\Translation;
use Illuminate\Database\Seeder;

class ClassificationsTableSeeder extends Seeder
{
    use Translations;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file_classifications = File::get("database/data/classifications.json");
        $data_json_classifications = json_decode($file_classifications, true);
        foreach ($data_json_classifications as $data_classifications) {
            $newClass = new Classification();
            $newClass->id = $data_classifications['id'];
            $newClass->save();
            foreach ($data_classifications['translations'] as $data_translations) {
                $newTranslation = new Translation();
                $newTranslation->type = "classification";
                $newTranslation->object_id = $newClass->id;
                $newTranslation->slug = "classification_name";
                $newTranslation->value = $data_translations['value'];
                $newTranslation->language_id = $data_translations['language_id'];
                $newTranslation->save();
            }
        }
    }
}

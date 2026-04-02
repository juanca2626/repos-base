<?php

use App\Experience;
use App\Language;
use App\Http\Traits\Translations;
use App\Translation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExperiencesTableSeeder extends Seeder
{
    use Translations;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file_experiences = File::get("database/data/experiences.json");
        $file_experience_translations = File::get("database/data/experience_translations.json");
        $experiences = json_decode($file_experiences, true);
        $experience_translations = json_decode($file_experience_translations, true);
        $created_at = date("Y-m-d H:i:s");
        DB::transaction(function () use ($experiences, $experience_translations, $created_at) {
            foreach ($experiences as $experience) {
                $newExperiences = new Experience();
                $newExperiences->id = $experience['id'];
                $newExperiences->created_at = $created_at;
                $newExperiences->updated_at = $created_at;
                $newExperiences->save();
            }
            //translations
            foreach ($experience_translations as $translation) {
                $newTranslation = new Translation();
                $newTranslation->type = $translation['type'];
                $newTranslation->object_id = $translation['object_id'];
                $newTranslation->slug = $translation['slug'];
                $newTranslation->value = $translation['value'];
                $newTranslation->language_id = $translation['language_id'];
                $newTranslation->created_at = $created_at;
                $newTranslation->updated_at = $created_at;
                $newTranslation->save();
            }
        });
    }
}

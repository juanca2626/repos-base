<?php

use App\Language;
use App\ServiceCategory;
use App\Http\Traits\Translations;
use App\Translation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceCategoriesTableSeeder extends Seeder
{
    use Translations;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file_categories = File::get("database/data/service_categories.json");
        $file_category_translations = File::get("database/data/service_category_translations.json");
        $categories = json_decode($file_categories, true);
        $category_translations = json_decode($file_category_translations, true);
        $created_at = date("Y-m-d H:i:s");
        DB::transaction(function () use ($categories, $category_translations, $created_at) {
            foreach ($categories as $category) {
                $newExperiences = new ServiceCategory();
                $newExperiences->id = $category['id'];
                $newExperiences->created_at = $created_at;
                $newExperiences->updated_at = $created_at;
                $newExperiences->save();
            }
            //translations
            foreach ($category_translations as $translation) {
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

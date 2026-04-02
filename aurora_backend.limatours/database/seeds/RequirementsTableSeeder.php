<?php

use App\Language;
use App\Requirement;
use App\Http\Traits\Translations;
use Illuminate\Database\Seeder;

class RequirementsTableSeeder extends Seeder
{
    use Translations;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $languages = Language::select('id')->get();
        $newRequeriment = new Requirement();
        $newRequeriment->save();
        $translations = [];
        foreach ($languages as $language) {
            $translations[$language->id] = ['id' => '', 'requirement_name' => 'Guia'];
        }
        $this->saveTranslation($translations, 'requirement', $newRequeriment->id);
    }
}

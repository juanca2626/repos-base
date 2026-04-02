<?php

use App\Language;
use App\Http\Traits\Translations;
use App\UnitDuration;
use Illuminate\Database\Seeder;

class UnitDurationsTableSeeder extends Seeder
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
        $newDuration = new UnitDuration();
        $newDuration->save();
        $translations = [];
        foreach ($languages as $language) {
            $translations[$language->id] = ['id' => '', 'unitduration_name' => 'Días'];
        }
        $this->saveTranslation($translations, 'unitduration', $newDuration->id);
    }
}

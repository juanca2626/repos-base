<?php

use App\Language;
use App\Http\Traits\Translations;
use App\Unit;
use Illuminate\Database\Seeder;

class UnitsTableSeeder extends Seeder
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
        $newUnit = new Unit();
        $newUnit->save();
        $translations = [];
        foreach ($languages as $language) {
            $translations[$language->id] = ['id' => '', 'unit_name' => 'Pax'];
        }
        $this->saveTranslation($translations, 'unit', $newUnit->id);
    }
}

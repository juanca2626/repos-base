<?php

use App\Language;
use App\Http\Traits\Translations;
use App\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

// @codingStandardsIgnoreLine
class ZonesTableSeeder extends Seeder
{
    use Translations;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (App::environment('local') === true) {
            $languages = Language::select('id')->get();

            $newZone = new Zone();
            $newZone->city_id = 128;
            $newZone->save();

            $translations = [];

            foreach ($languages as $language) {
                $translations[$language->id] = ['id' => '', 'zone_name' => 'Parque Kennedy'];
            }

            $this->saveTranslation($translations, 'zone', $newZone->id);
        }
    }
}

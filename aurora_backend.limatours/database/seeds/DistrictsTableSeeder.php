<?php

use App\District;
use App\Language;
use App\Http\Traits\Translations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

// @codingStandardsIgnoreLine
class DistrictsTableSeeder extends Seeder
{
    use Translations;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file_districts_peru = File::get("database/data/districts_peru.json");
        $districts_peru = json_decode($file_districts_peru, true);
        $languages = Language::select('id')->get();

        foreach ($districts_peru as $district) {
            if (App::environment('local') === true) {
                if ($district['city_id'] === 128) {
                    $newDistrict = new District();
                    $newDistrict->city_id = $district['city_id'];
                    $newDistrict->save();

                    $translations = [];

                    foreach ($languages as $language) {
                        $translations[$language->id] = ['id' => '', 'district_name' => $district['name']];
                    }

                    $this->saveTranslation($translations, 'district', $newDistrict->id);
                }
            } else {
                $newDistrict = new District();
                $newDistrict->city_id = $district['city_id'];
                $newDistrict->save();

                $translations = [];

                foreach ($languages as $language) {
                    $translations[$language->id] = ['id' => '', 'district_name' => $district['name']];
                }

                $this->saveTranslation($translations, 'district', $newDistrict->id);
            }
        }
    }
}

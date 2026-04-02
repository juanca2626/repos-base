<?php

use App\City;
use App\Language;
use App\Http\Traits\Translations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

// @codingStandardsIgnoreLine
class CitiesTableSeeder extends Seeder
{
    use Translations;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $file_cities_peru = File::get("database/data/provinces_peru.json");
        $cities_peru = json_decode($file_cities_peru, true);
        $languages = Language::select('id')->get();

        foreach ($cities_peru as $city) {
            if (App::environment('local') === true) {
                if ($city['id'] === 128) {
                    $newCity = new City();
                    $newCity->id = $city['id'];
                    $newCity->state_id = $city['state_id'];
                    $newCity->save();

                    $translations = [];

                    foreach ($languages as $language) {
                        $translations[$language->id] = ['id' => '', 'city_name' => $city['name']];
                    }

                    $this->saveTranslation($translations, 'city', $newCity->id);
                }
            } else {
                $newCity = new City();
                $newCity->id = $city['id'];
                $newCity->state_id = $city['state_id'];
                $newCity->save();

                $translations = [];

                foreach ($languages as $language) {
                    $translations[$language->id] = ['id' => '', 'city_name' => $city['name']];
                }

                $this->saveTranslation($translations, 'city', $newCity->id);
            }
        }
    }
}

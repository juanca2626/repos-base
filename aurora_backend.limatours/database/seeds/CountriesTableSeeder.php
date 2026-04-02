<?php

use App\Country;
use App\Language;
use App\Http\Traits\Translations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

// @codingStandardsIgnoreLine
class CountriesTableSeeder extends Seeder
{
    use Translations;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file_countries = File::get("database/data/countries.json");
        $countries = json_decode($file_countries, true);

        $languages = Language::select('id')->get();

        foreach ($countries as $country) {
            if (App::environment('local') === true) {
                if ($country['id'] === '89') {
                    $newCountry = new Country();
                    $newCountry->id = $country['id'];
                    $newCountry->local_tax = true;
                    $newCountry->local_service = true;
                    $newCountry->foreign_tax = true;
                    $newCountry->foreign_service = true;
                    $newCountry->save();

                    $translations = [];

                    foreach ($languages as $language) {
                        $translations[$language->id] = ['id' => '', 'country_name' => $country['name']];
                    }

                    $this->saveTranslation($translations, 'country', $newCountry->id);
                }
            } else {
                $newCountry = new Country();
                $newCountry->id = $country['id'];
                $newCountry->local_tax = true;
                $newCountry->local_service = true;
                $newCountry->foreign_tax = true;
                $newCountry->foreign_service = true;
                $newCountry->save();

                $translations = [];

                foreach ($languages as $language) {
                    $translations[$language->id] = ['id' => '', 'country_name' => $country['name']];
                }

                $this->saveTranslation($translations, 'country', $newCountry->id);
            }
        }
    }
}

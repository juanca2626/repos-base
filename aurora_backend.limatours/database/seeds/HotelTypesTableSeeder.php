<?php

use App\HotelType;
use App\Language;
use App\Http\Traits\Translations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class HotelTypesTableSeeder extends Seeder
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

            $newHotelType = new HotelType();
            $newHotelType->save();

            $translations = [];

            foreach ($languages as $language) {
                $translations[$language->id] = ['id' => '', 'hoteltype_name' => 'Basico'];
            }

            $this->saveTranslation($translations, 'hoteltype', $newHotelType->id);
        }
    }
}

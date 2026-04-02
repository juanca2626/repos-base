<?php

use App\HotelCategory;
use App\Language;
use App\Http\Traits\Translations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

// @codingStandardsIgnoreLine
class HotelCategoriesTableSeeder extends Seeder
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

            $newHotelCategories = new HotelCategory();
            $newHotelCategories->save();

            $translations = [];

            foreach ($languages as $language) {
                $translations[$language->id] = ['id' => '', 'hotelcategory_name' => 'Resort'];
            }

            $this->saveTranslation($translations, 'hotelcategory', $newHotelCategories->id);
        }
    }
}

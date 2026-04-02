<?php

use App\Currency;
use App\Language;
use App\Http\Traits\Translations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

// @codingStandardsIgnoreLine
class CurrenciesTableSeeder extends Seeder
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

            $newCurrency = new Currency();
            $newCurrency->symbol = 'S/.';
            $newCurrency->iso = 'PEN';
            $newCurrency->save();

            $translations = [];

            foreach ($languages as $language) {
                $translations[$language->id] = ['id' => '', 'currency_name' => 'Soles'];
            }

            $this->saveTranslation($translations, 'currency', $newCurrency->id);
        }
    }
}

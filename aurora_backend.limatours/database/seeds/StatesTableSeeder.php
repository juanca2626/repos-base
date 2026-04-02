<?php

use App\Language;
use App\State;
use App\Http\Traits\Translations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

// @codingStandardsIgnoreLine
class StatesTableSeeder extends Seeder
{
    use Translations;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file_state = File::get("database/data/states.json");
        $states = json_decode($file_state, true);

        $languages = Language::select('id')->get();

        foreach ($states as $state) {
            if (App::environment('local') === true) {
                if ($state['id'] === '1610') {
                    $newState = new State();
                    $newState->id = $state['id'];
                    $newState->country_id = $state['country_id'];
                    $newState->save();

                    $translations = [];

                    foreach ($languages as $language) {
                        $translations[$language->id] = ['id' => '', 'state_name' => $state['name']];
                    }

                    $this->saveTranslation($translations, 'state', $newState->id);
                }
            } else {
                $newState = new State();
                $newState->id = $state['id'];
                $newState->country_id = $state['country_id'];
                $newState->save();

                $translations = [];

                foreach ($languages as $language) {
                    $translations[$language->id] = ['id' => '', 'state_name' => $state['name']];
                }

                $this->saveTranslation($translations, 'state', $newState->id);
            }
        }
    }
}

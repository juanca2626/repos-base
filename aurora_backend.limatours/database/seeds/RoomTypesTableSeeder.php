<?php

use App\Language;
use App\RoomType;
use App\Http\Traits\Translations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

// @codingStandardsIgnoreLine
class RoomTypesTableSeeder extends Seeder
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

            $newRoomType = new RoomType();
            $newRoomType->save();

            $translations = [];

            foreach ($languages as $language) {
                $translations[$language->id] = ['id' => '', 'roomtype_name' => 'Basico'];
            }
            $this->saveTranslation($translations, 'roomtype', $newRoomType->id);
        }
    }
}

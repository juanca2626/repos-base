<?php

use App\Language;
use App\Room;
use App\Http\Traits\Translations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

// @codingStandardsIgnoreLine
class RoomsTableSeeder extends Seeder
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
            factory(Room::class)->times(40)->create();

            $languages = Language::select('id')->get();

            $translations = [];
            $rooms = Room::select('id', 'hotel_id')->get();

            foreach ($rooms as $room) {
                foreach ($languages as $language) {
                    $translations[$language->id] = [
                        [
                            'id' => '',
                            'room_name' => 'Habitación ' . $room->id . ' Hotel ' . $room->hotel_id
                        ],
                        [
                            'id' => '',
                            'room_description' => 'Descripción Habitación ' . $room->id . ' Hotel ' . $room->hotel_id
                        ]
                    ];
                }
                $this->saveMultipleTranslation($translations, 'room', $room->id);
            }
        }
    }
}

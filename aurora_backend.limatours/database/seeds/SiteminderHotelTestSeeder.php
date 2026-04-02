<?php

use App\Hotel;
use App\Language;
use App\Room;
use App\Http\Traits\Translations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

// @codingStandardsIgnoreLine
class SiteminderHotelTestSeeder extends Seeder
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
            $hotel = new Hotel([
                'name' => 'Siteminder Hotel',
                'aurora_code' => 'DDF45C353',
                'web_site' => null,
                'status' => 1,
                'latitude' => null,
                'longitude' => null,
                'check_in_time' => null,
                'check_out_time' => null,
                'percentage_completion' => 0,
                'typeclass_id' => 1,
                'chain_id' => 1,
                'currency_id' => 1,
                'hotel_type_id' => 1,
                'hotelcategory_id' => 1,
                'country_id' => 89,
                'state_id' => 1610,
                'city_id' => 128,
                'district_id' => 1,
            ]);
            $hotel->save();
            $languages = Language::select('id')->get();

            //CHANNELS
            $hotel->channels()->attach([2 => ['code' => 'SITEMINDER', 'state' => true]]);

            $rooms = [
                [10, 1, 10, 3, 0, 1, 1, $hotel->id, 1, 'SGL'],
                [05, 2, 05, 2, 0, 1, 1, $hotel->id, 1, 'DBX'],
                [01, 1, 01, 0, 0, 1, 1, $hotel->id, 1, 'DBL'],
            ];

            $roomsDEscriptions = [
                [
                    ['id' => '', 'room_name' => 'Single Room'],
                    [
                        'id' => '',
                        'room_description' => 'Bedding is Queen sized bed. Continental breakfast for each guest.'
                    ]
                ],
                [
                    ['id' => '', 'room_name' => 'Deluxe Double Room'],
                    [
                        'id' => '',
                        'room_description' => 'Bedding is 1 x King sized bed and 1 x Single bed. Continental breakfast for each guest.'
                    ]
                ],
                [
                    ['id' => '', 'room_name' => 'Standard Double Room'],
                    [
                        'id' => '',
                        'room_description' => 'Bedding is 2 x Queen sized bed. Continental breakfast for each guest.'
                    ]
                ],
            ];

            $keys = [
                'max_capacity',
                'min_adults',
                'max_adults',
                'max_child',
                'max_infants',
                'min_inventory',
                'state',
                'hotel_id',
                'room_type_id',
                'aurora_code'
            ];
            foreach ($rooms as $ind => $room) {
                $newRoom = new Room(array_combine($keys, $room));

                $translations = [];
                foreach ($languages as $language) {
                    $translations[$language->id] = $roomsDEscriptions[$ind];
                }
                $this->saveMultipleTranslation($translations, 'room', $newRoom->id);
            }
        }
    }
}

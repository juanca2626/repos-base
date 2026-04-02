<?php

use App\RateOrderCity;
use App\State;
use Illuminate\Database\Seeder;

class CitiesOrderRateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $states = State::where('country_id', 89)->with([
            'translations' => function ($query){
                $query->where('type', 'state');
                $query->where('language_id', 1);
            }
        ])->with([
            'cities.translations' => function ($query){
                $query->where('type', 'city');
                $query->where('language_id', 1);
            }
        ])->get();

        foreach ($states as $state) {
            foreach ($state["cities"] as $city) {

               $rate_order_city = new RateOrderCity();
               $rate_order_city->city_id = $city["id"];
               $rate_order_city->order = $city["id"];
               $rate_order_city->save();
            }
        }

    }
}

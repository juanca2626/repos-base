<?php

use App\RatesPlans;
use App\RatesPlansRooms;
use App\Room;
use Illuminate\Database\Seeder;

// @codingStandardsIgnoreLine
class RatesPlansFreeSaleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rooms = Room::select('id')->where('hotel_id', 1)->get();

        foreach ($rooms as $key => $room) {
            $ratesPlan = new RatesPlans();
            $ratesPlan->name = 'Tarifa ' . ($key + 1);
            $ratesPlan->code = 'Tarifa ' . ($key + 1);
            $ratesPlan->allotment = 0;
            $ratesPlan->taxes = 0;
            $ratesPlan->services = 0;
            $ratesPlan->advance_sales = 0;
            $ratesPlan->promotions = 0;
            $ratesPlan->status = true;
            $ratesPlan->meal_id = 1;
            $ratesPlan->rates_plans_type_id = 1;
            $ratesPlan->charge_type_id = 1;
            $ratesPlan->hotel_id = 1;
            $ratesPlan->save();

            $ratesPlan = new RatesPlansRooms();
            $ratesPlan->rates_plans_id = ($key + 1);
            $ratesPlan->room_id = (int)$room['id'];
            $ratesPlan->status = true;
            $ratesPlan->bag = 0;
            $ratesPlan->save();
        }
    }
}

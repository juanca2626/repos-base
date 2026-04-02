<?php

use App\RatesPlans;
use App\RatesPlansRooms;
use Illuminate\Database\Seeder;

// @codingStandardsIgnoreLine
class RatesPlansAllotmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
            $ratesPlan = new RatesPlans();
        $ratesPlan->name = 'Tarifa  4';
        $ratesPlan->code = 'Tarifa  4';
            $ratesPlan->allotment = 1;
            $ratesPlan->taxes = 0;
            $ratesPlan->services = 0;
            $ratesPlan->advance_sales = 0;
            $ratesPlan->promotions = 0;
            $ratesPlan->status = true;
            $ratesPlan->meal_id = 1;
            $ratesPlan->rates_plans_type_id = 1;
            $ratesPlan->charge_type_id = 1;
            $ratesPlan->save();

            $ratesPlan = new RatesPlansRooms();
            $ratesPlan->rates_plans_id = 4;
            $ratesPlan->room_id = 7;
            $ratesPlan->status = true;
            $ratesPlan->save();

            $ratesPlan = new RatesPlansRooms();
            $ratesPlan->rates_plans_id = 4;
            $ratesPlan->room_id = 20;
            $ratesPlan->status = true;
            $ratesPlan->save();
    }
}

<?php

namespace App\Http\Traits;

use App\Markup;
use App\MarkupHotel;
use App\MarkupRatePlan;

trait CalculateMarkup
{

    /**
     * @param $client_id
     * @param $period
     * @param $hotel_id
     * @param $rate_plan_id
     * @return int
     */
    public function markup($client_id , $period , $hotel_id ,$rate_plan_id)
    {
        $markupRatePlan = MarkupRatePlan::select('markup')->where(['client_id' => $client_id ,  'period' => $period  , 'rate_plan_id' => $rate_plan_id ])->first();
        if($markupRatePlan->markup){
            return $markupRatePlan->markup;
        }

        $markupHotel = MarkupHotel::select('markup')->where(['client_id' => $client_id ,  'period' => $period  , 'hotel_id' => $hotel_id ])->first();
        if($markupHotel->markup){
            return $markupHotel->markup;
        }

        $markup = Markup::select('hotel')->where(['client_id' => $client_id ,  'period' => $period ])->first();

        return $markup->hotel;

    }

    /**
     * @param $client_id
     * @param $period
     * @param $hotel_id
     * @param $array_rate_plan_id
     * @return array
     */
    public function getListRateMarkup($client_id , $period , $hotel_id ,$array_rate_plan_id)
    {
        $listMarkup = [];

        foreach ($array_rate_plan_id as $rate_plan_id) {


            $markupRatePlan = MarkupRatePlan::select('markup')->where(['client_id' => $client_id, 'period' => $period, 'rate_plan_id' => $rate_plan_id])->first();
            if (is_object($markupRatePlan) and $markupRatePlan->markup) {
                $listMarkup[$rate_plan_id] = $markupRatePlan->markup;
                continue;
            }

            $markupHotel = MarkupHotel::select('markup')->where(['client_id' => $client_id, 'period' => $period, 'hotel_id' => $hotel_id])->first();
            if (is_object($markupHotel) and $markupHotel->markup) {
                $listMarkup[$rate_plan_id] = $markupHotel->markup;
                continue;
            }

            $markup = Markup::select('hotel')->where(['client_id' => $client_id, 'period' => $period])->first();
            if(is_object($markup) and $markup->hotel) {
                $listMarkup[$rate_plan_id] = $markup->hotel;
                continue;
            }

            $listMarkup[$rate_plan_id] = 0;

        }

        return $listMarkup;

    }

}

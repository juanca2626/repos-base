<?php

namespace App\Http\Traits;
set_time_limit(0);

use App\ClientRatePlan;
use App\Client;
use App\Hotel;
use App\HotelClient;
use App\RatesPlans;
use App\Markup;
use App\MarkupHotel;
use App\MarkupRatePlan;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use jeremykenedy\LaravelRoles\Models\Role;

trait ClientHotels
{
    private function insertRatePlans____________($hotel_id, $client_id, $markup, $period)
    {
        $rate_plans = RatesPlans::where('hotel_id', $hotel_id)->get();
        $client_rate_plan_save = [];
        $result = [];
        foreach ($rate_plans as $key => $value) {
            $client_rate_plan_save[] = [
                'rate_plan_id' => $value->id,
                'client_id' => $client_id,
                'markup' => $markup,
                'period' => $period,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ];
        }
        if (!empty($client_rate_plan_save)) {
            $result = ClientRatePlan::insert($client_rate_plan_save);
        }
        return $result;
    }

    public function storeAllHotel($client_id, $markup, $period, $region_id)
    {
        $hotel_client_database = HotelClient::select(['hotel_id'])->where(['client_id' => $client_id, 'period' => $period , 'business_region_id' => $region_id]);

        $hotels_database = Hotel::select(['id']);

        if ($hotel_client_database->count() > 0) {
            $hotels_database->whereNotIn('id', $hotel_client_database);
        }

        $hotels_database = $hotels_database->get();

//        foreach ($hotels_database as $key => $hotel) {
////            $store_hotel_client = $this->storeHotelClient($markup, $period, $client_id, $hotel['id']);
//            /*
//            if (!empty($store_hotel_client) && $store_hotel_client->count() > 0) {
//                $this->insertRatePlans($hotel['id'], $client_id, $markup, $period);
//            }
//            */
//        }
//        return  $store_hotel_client;
    }

    public function storeAllClient($hotel_id, $markup, $period , $market, $region_id)
    {
        $hotel_client_database = HotelClient::select(['client_id'])->where(['hotel_id' => $hotel_id, 'period' => $period, 'business_region_id' => $region_id ]);

        $client_database = Client::select(['id'])->where(['status' => 1]);

        if($market){
            $client_database->where('market_id' , $market);
        }

        if ($hotel_client_database->count() > 0) {
            $client_database->whereNotIn('id', $hotel_client_database);
        }

        $client_database = $client_database->get();

        foreach ($client_database as $key => $client) {
            $store_hotel_client = $this->storeHotelClient($markup, $period, $client['id'] , $hotel_id, $region_id);
            /*
            if (!empty($store_hotel_client) && $store_hotel_client->count() > 0) {
                $this->insertRatePlans($hotel['id'], $client_id, $markup, $period);
            }
            */
        }
        return  $store_hotel_client;
    }

 
    public function storeHotelClient($markup, $period, $client_id, $hotel_id, $region_id)
    {
        $hotel_client = new HotelClient();
        $hotel_client->period = $period;
        $hotel_client->client_id = $client_id;
        $hotel_client->hotel_id = $hotel_id;
        $hotel_client->business_region_id = $region_id;
        $hotel_client->save();

        $this->deleteMarkupHotel($client_id,$hotel_id,$period);
        $this->deleteMarkupRatePlans($client_id,$hotel_id,$period);
//        $this->deleteClientRatePlans($client_id, $period, $hotel_id);

        return $hotel_client;
    }

    public function deleteMarkupHotel($client_id,$hotel_id,$period){

        $hotelMarkup = MarkupHotel::where('client_id',$client_id)->where('hotel_id' , $hotel_id)->where('period' , $period)->first();
        if(is_object($hotelMarkup)){
            $hotelMarkup->delete();
        }
    }


    public function deleteMarkupRatePlans($client_id,$hotel_id,$period){


        $client_rate_ids = MarkupRatePlan::where([
            'client_id' => $client_id,
            'period' => $period
        ])->pluck('rate_plan_id');

        $rate_plans = RatesPlans::select('id','name')->where('hotel_id', $hotel_id)->whereIn('id', $client_rate_ids)->get();

        foreach($rate_plans as $rate){
            $ratesMarkup = MarkupRatePlan::where('client_id',$client_id)->where('rate_plan_id' , $rate->id)->where('period' , $period)->first();
            if(is_object($ratesMarkup)){
               $ratesMarkup->delete();
            }
        }
    }

    public function deleteClientRatePlans($client_id, $period, $hotel_id){

        ClientRatePlan::where(['client_id' => $client_id, 'period' => $period,'deleted_at' => null])
        ->whereHas('ratePlan', function ($query) use ($hotel_id) {
            $query->where('hotel_id', $hotel_id);
        })->delete();


    }


}

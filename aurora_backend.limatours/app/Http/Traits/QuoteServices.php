<?php

namespace App\Http\Traits;

use App\Quote;
use App\QuoteCategory;
use App\QuoteDistribution;
use App\QuotePassenger;
use App\QuoteServiceAmount;
use App\QuoteServicePassenger;
use App\QuoteServiceRoom;
use App\RatesPlansCalendarys;
use App\RatesPlansRooms;
use App\Room;
use App\RoomType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait QuoteServices
{
    use Quotes;

    private function newService(
        $quote_category_id,
        $type,
        $object_id,
        $date_in,
        $date_out,
        $adult_quote,
        $child_quote,
        $infant,
        $single_general,
        $double_general,
        $triple_general,
        $triple_active,
        $service_rate_ids,
        $client_id,
        $on_request,
        $quote_id = null,
        $new_extension_id = null
    ) {

        DB::transaction(function () use (
            $quote_category_id,
            $type,
            $object_id,
            $date_in,
            $date_out,
            $adult_quote,
            $child_quote,
            $infant,
            $single_general,
            $double_general,
            $triple_general,
            $triple_active,
            $service_rate_ids,
            $client_id,
            $on_request,
            $quote_id,
            $new_extension_id
        ) {

            $quoteDistributions = QuoteDistribution::with('passengers')->where("quote_id", $quote_id)->orderBy('occupation')->get();

            $today = Carbon::now();
            $_date_in = Carbon::parse($date_in);
            $_date_out = Carbon::parse($date_out);
            $nights = $_date_in->diffInDays($_date_out);
            $order = DB::table('quote_services')
                    ->where('quote_category_id', $quote_category_id)
                    ->where('date_in', $date_in)->count() + 1;
            if ($type == 'hotel') {
                $this->deleteRoomDisabledByOccupationRoom($service_rate_ids);
                $quote_people = DB::table('quote_people')->where('quote_id', $quote_id)->first();
                if ($quote_people) {
                    $total_people = $quote_people->adults + $quote_people->child;
                } else {
                    $total_people = 0;
                }
                
                $ratePlanRomos = DB::table('rates_plans_rooms')->leftJoin('rooms', 'rates_plans_rooms.room_id', '=', 'rooms.id')->leftJoin('room_types', 'rooms.room_type_id', '=', 'room_types.id')
                    ->select('rates_plans_rooms.id', 'rates_plans_rooms.room_id', 'room_types.occupation')
                    ->whereIn('rates_plans_rooms.id',$service_rate_ids)
                    ->orderBy('room_types.occupation')->get();

                // foreach ($service_rate_ids as $s_r_id) {
                $totalPaxAdultoTomado = 0;
                $totalPaxChildrenTomado = 0;
                $allAduls = false;
                foreach ($ratePlanRomos as $index => $rate_plan_room) {
                    // hotel
                    $adult = 0;
                    $child = 0;
                    $single = 0;
                    $double = 0;
                    $triple = 0;

                    $totals = 1;

                    // $rate_plan_room = DB::table('rates_plans_rooms')->where('id', $s_r_id)->first();
                    // $room = DB::table('rooms')->where('id', $rate_plan_room->room_id)->first();
                    // $room_type = DB::table('room_types')->where('id', $room->room_type_id)->first();

                    if ($total_people == 0) // En caso que la cotización se encuentre en rangos..
                    {
                        if ($rate_plan_room->occupation == 1) {
                            $adult = 1;
                            $single = 1;
                        }
                        if ($rate_plan_room->occupation == 2) {

                            if ($quote_people and $quote_people->child > 0) {
                                $adult = 1;
                                $child = 1;
                            } else {
                                $adult = 2;
                                $child = 0;
                            }

                            $double = 1;
                        }
                        if ($rate_plan_room->occupation == 3) {

                            if ($quote_people and $quote_people->child > 0) {
                                $adult = 2;
                                $child = 1;
                            } else {
                                $adult = 3;
                                $child = 0;
                            }

                            $triple = 1;
                        }
                    }
                    
                    //////////////////////////////////////////////////////////
                    // if ($rate_plan_room->occupation == 1) {
                    //     $single = 1;
                    //     $totals = ($single_general == 0) ? 1 : $single_general;
                    // }

                    // if ($rate_plan_room->occupation == 2) {
                    //     $totals = ($double_general == 0) ? 1 : $double_general;
                    //     $double = 1;
                    // }
                    
                    // if ($rate_plan_room->occupation == 3) {
                    //     $totals = ($triple_general == 0) ? 1 : $triple_general;
                    //     $triple = 1;
                    // }                    
                    //////////////////////////////////////////////////////////


                    if ($rate_plan_room->occupation < $total_people) {
                        if ($rate_plan_room->occupation == 1) {
                            $adult = 1;
                            $single = 1;

                            $totals = ($single_general == 0) ? 1 : $single_general;
                        }
                        if ($rate_plan_room->occupation == 2) {

                            if ($quote_people and $quote_people->child > 0) {
                                $adult = 1;
                                $child = 1;
                            } else {
                                $adult = 2;
                                $child = 0;
                            }

                            $double = 1;

                            $totals = ($double_general == 0) ? 1 : $double_general;
                        }
                        if ($rate_plan_room->occupation == 3) {

                            if ($quote_people and $quote_people->child > 0) {
                                $adult = 2;
                                $child = 1;
                            } else {
                                $adult = 3;
                                $child = 0;
                            }

                            $triple = 1;

                            $totals = ($triple_general == 0) ? 1 : $triple_general;
                        }
                    }

                    if ($rate_plan_room->occupation == $total_people) {
                        if ($rate_plan_room->occupation == 1) {
                            $adult = 1;
                            $single = 1;

                            $totals = ($single_general == 0) ? 1 : $single_general;
                        }
                        if ($rate_plan_room->occupation == 2) {

                            if ($quote_people and $quote_people->child > 0) {
                                $adult = 1;
                                $child = 1;
                            } else {
                                $adult = 2;
                                $child = 0;
                            }

                            $double = 1;

                            $totals = ($double_general == 0) ? 1 : $double_general;
                        }
                        if ($rate_plan_room->occupation == 3) {

                            if ($quote_people and $quote_people->child > 0) {
                                $adult = 2;
                                $child = 1;
                            } else {
                                $adult = 3;
                                $child = 0;
                            }

                            $triple = 1;

                            $totals = ($triple_general == 0) ? 1 : $triple_general;
                        }
                    }

                    for ($i = 0; $i < $totals; $i++) {

                        $quote_service_id = DB::table('quote_services')->insertGetId([
                            'quote_category_id' => $quote_category_id,
                            'type' => $type,
                            'object_id' => $object_id,
                            'order' => $order,
                            'date_in' => $date_in,
                            'date_out' => $date_out,
                            'nights' => $nights,
                            'adult' => $adult,
                            'child' => $child,
                            'infant' => $infant,
                            'single' => $single,
                            'double' => $double,
                            'triple' => $triple,
                            'triple_active' => $triple_active,
                            'on_request' => $on_request,
                            'created_at' => $today,
                            'updated_at' => $today,
                            'new_extension_id' => $new_extension_id
                        ]);
                        DB::table('quote_service_rooms')->insert([
                            'quote_service_id' => $quote_service_id,
                            'rate_plan_room_id' => $rate_plan_room->id,
                            'created_at' => $today
                        ]);

                       
                        // $this->updateListPassengersRoomsHotel($quote_service_id, $quote_id);
                        // $this->updateAmountServiceNewService($quote_service_id, $client_id);
                    }


                }

                $this->setAccommodationInHotelsForQuoteService($quote_category_id,$object_id,$date_in, $adult_quote, $child_quote, $quoteDistributions);
                $this->setUpdatePassengerInHotelsForQuoteService($quote_id, $client_id, $quote_category_id,$object_id,$date_in, $adult_quote, $child_quote);

            }

            if ($type == 'service') {
                foreach ($service_rate_ids as $s_r_id) {
                    $quote_service_id = DB::table('quote_services')->insertGetId([
                        'quote_category_id' => $quote_category_id,
                        'type' => $type,
                        'object_id' => $object_id,
                        'order' => $order,
                        'date_in' => $date_in,
                        'date_out' => $date_out,
                        'nights' => $nights,
                        'adult' => $adult_quote,
                        'child' => $child_quote,
                        'infant' => $infant,
                        'single' => $single_general,
                        'double' => $double_general,
                        'triple' => $triple_general,
                        'triple_active' => $triple_active,
                        'on_request' => $on_request,
                        'created_at' => $today,
                        'updated_at' => $today,
                        'new_extension_id' => $new_extension_id
                    ]);

                    DB::table('quote_service_rates')->insert([
                        'quote_service_id' => $quote_service_id,
                        'service_rate_id' => $s_r_id,
                        'created_at' => $today
                    ]);

                    if (count($service_rate_ids) > 0 and $client_id != '') {
                        $this->updateAssignPassengerService($quote_id, $quote_service_id, (int)$adult_quote, (int)$child_quote);
                        $this->updateAmountServiceNewService($quote_service_id, $client_id);
                    }
                }
            }

        }, 5);
    }

    private function newFlight(
        $quote_category_id,
        $type,
        $date_flight,
        $client_id,
        $adult,
        $child,
        $origin,
        $destiny,
        $code_flight
    ) {
        DB::transaction(function () use (
            $quote_category_id,
            $type,
            $date_flight,
            $client_id,
            $adult,
            $child,
            $origin,
            $destiny,
            $code_flight
        ) {
            $today = Carbon::now();

            $quote_service_id = DB::table('quote_services')->insertGetId([
                'quote_category_id' => $quote_category_id,
                'type' => $type,
                'date_in' => $date_flight,
                'date_out' => $date_flight,
                'origin' => $origin,
                'destiny' => $destiny,
                'adult' => $adult,
                'child' => $child,
                'code_flight' => $code_flight,
                'created_at' => $today
            ]);

            /*
            if (count($service_rate_ids) > 0 and $client_id != '') {
                $this->updateAmountServiceNewService($quote_service_id, $client_id);
            }
            */
        });
    }

    private function updateAmountServiceNewService($service_id, $client_id)
    {
        DB::transaction(function () use ($service_id, $client_id) {
            $service = DB::table('quote_services')->where('id', $service_id)->first();

            if ($service->type == 'service') {
                $this->calculateAmountServiceServiceNewService($service_id, $client_id, $service->adult,
                    $service->child, $service->date_in, $service->object_id);
            }
            if ($service->type == 'hotel') {
                
                $quote_id = QuoteCategory::where('id', $service->quote_category_id)->first()->quote_id;

                $quote_service_passengers_quantity = QuoteServicePassenger::where('quote_service_id',
                    $service_id)->get()->count();

                $service_rate_plan_room = QuoteServiceRoom::where('quote_service_id',
                    $service_id)->first();

                $rate_plan_room = RatesPlansRooms::find($service_rate_plan_room->rate_plan_room_id);

                $markup = Quote::where('id', $quote_id)->first()->markup;

                $room_type_id = Room::where('id', $rate_plan_room->room_id)->first()->room_type_id;

                $occupation = RoomType::where('id', $room_type_id)->first()->occupation;

                $rate_plan_calendars = RatesPlansCalendarys::where('rates_plans_room_id', $rate_plan_room->id)
                    ->with('rate')
                    ->where('date', '>=', $service->date_in)
                    ->where('date', '<=', $service->date_out)
                    ->get()->toArray();
                if (count($rate_plan_calendars) == $service->nights) {
                    foreach ($rate_plan_calendars as $rate_plan_calendar) {

                        // QuoteServiceAmount::insert([
                        //     'quote_service_id' => $service->id,
                        //     'date_service' => Carbon::parse($rate_plan_calendar["date"])->format('d/m/Y'),
                        //     'price_per_night_without_markup' => $rate_plan_calendar["rate"][0]["price_adult"],
                        //     'price_per_night' => ($rate_plan_calendar["rate"][0]["price_adult"] * ($markup / 100)) + $rate_plan_calendar["rate"][0]["price_adult"],
                        //     'price_adult_without_markup' => $rate_plan_calendar["rate"][0]["price_adult"] / $quote_service_passengers_quantity,
                        //     'price_adult' => (($rate_plan_calendar["rate"][0]["price_adult"] * ($markup / 100)) + $rate_plan_calendar["rate"][0]["price_adult"]) / $quote_service_passengers_quantity,
                        //     'price_child_without_markup' => $rate_plan_calendar["rate"][0]["price_child"],
                        //     'price_child' => $rate_plan_calendar["rate"][0]["price_child"] + ($rate_plan_calendar["rate"][0]["price_child"] * ($markup / 100)),
                        //     'created_at' => Carbon::now()
                        // ]);
                    }
                }
                /* $service_rate_plan_rooms_ids = DB::table('quote_service_rooms')->where('quote_service_id',
                     $service_id)->pluck('rate_plan_room_id');

                 $rate_plan_rooms = DB::table('rates_plans_rooms')
                     ->whereIn('rates_plans_rooms.id', $service_rate_plan_rooms_ids)
                     ->get();

                 foreach ($rate_plan_rooms as $index => $rate_plan_room) {
                     $markup = 0;
                     $rate_markup = DB::table('markup_rate_plans')
                         ->where('rate_plan_id', $rate_plan_room->rates_plans_id)
                         ->where('client_id', $client_id)
                         ->where('period', Carbon::parse($service->date_in)->year)
                         ->whereNull('deleted_at')
                         ->first();

                     if ($rate_markup != null) {
                         $markup = $rate_markup->markup;
                     } else {
                         $hotel_id = DB::table('rooms')->where('id', $rate_plan_room->room_id)->first()->hotel_id;

                         $markup_hotel = DB::table('markup_hotels')
                             ->where('hotel_id', $hotel_id)
                             ->where('client_id', $client_id)
                             ->where('period', Carbon::parse($service->date_in)->year)
                             ->whereNull('deleted_at')
                             ->first();

                         if ($markup_hotel != null) {
                             $markup = $markup_hotel->markup;
                         } else {
                             $markup = DB::table('markups')
                                 ->where('client_id', $client_id)
                                 ->where('period', Carbon::parse($service->date_in)->year)
                                 ->first();
                             if ($markup) {
                                 $markup = $markup->hotel;
                             } else {
                                 throw new \Exception('El Cliente seleccionado no cuenta con markup para el periodo '.Carbon::parse($service->date_in)->year,
                                     404);
                             }
                         }
                     }
                     $rate_plan_rooms[$index]->markup = $markup;
                 }
                 $simple = 0;
                 $double = 0;
                 $triple = 0;

                 $error_nights = 0;

                 //Calculo para Simple
                 foreach ($rate_plan_rooms as $rate_plan_room) {
                     $room_type_id = DB::table('rooms')->where('id', $rate_plan_room->room_id)->first()->room_type_id;

                     $occupation = DB::table('room_types')->where('id', $room_type_id)->first()->occupation;

                     if ($occupation == 1) {
                         $rate_plan_room_calendars = DB::table('rates_plans_calendarys')
                             ->whereNull('deleted_at')
                             ->where('rates_plans_room_id', $rate_plan_room->id)
                             ->where('date', '>=', $service->date_in)
                             ->where('date', '<=', Carbon::parse($service->date_out)->subDays(1)->format('Y-m-d'))
                             ->get();
                         foreach ($rate_plan_room_calendars as $calendar) {
                             $rate = DB::table('rates')
                                 ->whereNull('deleted_at')
                                 ->where('rates_plans_calendarys_id', $calendar->id)->first();

                             $simple += $rate->price_adult;
                         }
                         $simple = $simple + ($simple * ($rate_plan_room->markup / 100));

                         if (count($rate_plan_room_calendars) != $service->nights) {
                             $error_nights++;
                         }

                     }
                 }
                 //Calc para Double
                 foreach ($rate_plan_rooms as $rate_plan_room) {
                     $room_type_id = DB::table('rooms')->where('id', $rate_plan_room->room_id)->first()->room_type_id;

                     $occupation = DB::table('room_types')->where('id', $room_type_id)->first()->occupation;

                     if ($occupation == 2) {
                         $rate_plan_room_calendars = DB::table('rates_plans_calendarys')
                             ->whereNull('deleted_at')
                             ->where('rates_plans_room_id', $rate_plan_room->id)
                             ->where('date', '>=', $service->date_in)
                             ->where('date', '<=', Carbon::parse($service->date_out)->subDays(1)->format('Y-m-d'))
                             ->get();


                         foreach ($rate_plan_room_calendars as $calendar) {
                             $rate = DB::table('rates')
                                 ->whereNull('deleted_at')
                                 ->where('rates_plans_calendarys_id', $calendar->id)->first();

                             $double += $rate->price_adult;
                         }
                         $double = $double + ($double * ($rate_plan_room->markup / 100));

                         if (count($rate_plan_room_calendars) != $service->nights) {
                             $error_nights++;
                         }

                     }
                 }
                 //Calc para Triple
                 $exist_triple = false;
                 foreach ($rate_plan_rooms as $rate_plan_room) {
                     $room_type_id = DB::table('rooms')->where('id', $rate_plan_room->room_id)->first()->room_type_id;

                     $occupation = DB::table('room_types')->where('id', $room_type_id)->first()->occupation;

                     if ($occupation == 3) {
                         $exist_triple = true;
                         $rate_plan_room_calendars = DB::table('rates_plans_calendarys')
                             ->whereNull('deleted_at')
                             ->where('rates_plans_room_id', $rate_plan_room->id)
                             ->where('date', '>=', $service->date_in)
                             ->where('date', '<=', Carbon::parse($service->date_out)->subDays(1)->format('Y-m-d'))
                             ->get();

                         foreach ($rate_plan_room_calendars as $calendar) {
                             $rate = DB::table('rates')
                                 ->whereNull('deleted_at')
                                 ->where('rates_plans_calendarys_id', $calendar->id)->first();

                             $triple += $rate->price_adult + $rate->price_extra;
                         }
                         $triple = $triple + ($triple * ($rate_plan_room->markup / 100));

                         if (count($rate_plan_room_calendars) != $service->nights) {
                             $error_nights++;
                         }
                     }
                 }

                 if (!$exist_triple) {
                     $triple = ($simple + $double);
                 }

                 $total_amount = ($simple * $service->single) + ($double * $service->double) + ($triple * $service->triple);
                 DB::table('quote_service_amounts')->where('quote_service_id', $service_id)->delete();

                 DB::table('quote_service_amounts')->insert([
                     'quote_service_id' => $service_id,
                     'amount' => $total_amount,
                     'error_in_nights' => ($error_nights > 0) ? 1 : 0,
                     'created_at' => Carbon::now(),
                     'updated_at' => Carbon::now()
                 ]);*/
            }

        }, 5);
    }

    /**
     * @param $service_id
     * @param $client_id
     * @param $adult
     * @param $child
     * @param $date_in
     */
    private function calculateAmountServiceServiceNewService(
        $quote_service_id,
        $client_id,
        $adult,
        $child,
        $date_in,
        $object_id
    ) {
        DB::transaction(function () use ($quote_service_id, $client_id, $adult, $child, $date_in, $object_id) {

            $service_rate_id = DB::table('quote_service_rates')
                ->where('quote_service_id', $quote_service_id)
                ->first()->service_rate_id;

            $quantity_adults = $adult;
            $quantity_child = $child;

            $markup = 0;
            //Todo Verifico si tiene el markup por tarifa
            $markup_rate_client = DB::table('service_markup_rate_plans')
                ->where('client_id', $client_id)
                ->where('service_rate_id', $service_rate_id)
                ->where('period', Carbon::parse($date_in)->year)
                ->get();
            if ($markup_rate_client->count() == 0) { // Todo si no tiene markup en la tarifa
                //Todo busco el markup por el servicio
                $markup_service = DB::table('markup_services')
                    ->where('client_id', $client_id)
                    ->where('service_id', $object_id)
                    ->where('period', Carbon::parse($date_in)->year)
                    ->first();
                if ($markup_service) {
                    $markup = $markup_service->markup;
                } else {
                    $markup_general = DB::table('markups')
                        ->where('client_id', $client_id)
                        ->where('period', Carbon::parse($date_in)->year)
                        ->first();
                    if (isset($markup_general->service)) {
                        $markup = $markup_general->service;
                    } else {
                        Cache::put('quote_markup_errors', 'the client does not have markup', 3600);
                        return;
                    }
                }
            } else {
                $markup = $markup_rate_client->markup;
            }


            $pax_amount = DB::table('service_rate_plans')
                ->where('service_rate_id', $service_rate_id)
                ->where('date_from', '<=', $date_in)
                ->where('date_to', '>=', $date_in)
                ->where('pax_from', '<=', (int)$quantity_adults + (int)$quantity_child)
                ->where('pax_to', '>=', (int)$quantity_adults + (int)$quantity_child)
                ->whereNull('deleted_at')
                ->first();

            if ($pax_amount) {
                QuoteServiceAmount::insert([
                    'quote_service_id' => $quote_service_id,
                    'date_service' => $date_in,
                    'price_per_night' => 0,
                    'price_per_night_without_markup' => 0,
                    'price_adult_without_markup' => $pax_amount->price_adult,
                    'price_adult' => $pax_amount->price_adult + ($pax_amount->price_adult * ($markup / 100)),
                    'price_child_without_markup' => $pax_amount->price_child,
                    'price_child' => $pax_amount->price_child + ($pax_amount->price_child * ($markup / 100))
                ]);
            } else {
                QuoteServiceAmount::insert([
                    'quote_service_id' => $quote_service_id,
                    'date_service' => $date_in,
                    'price_per_night' => 0,
                    'price_per_night_without_markup' => 0,
                    'price_adult_without_markup' => 0,
                    'price_adult' => 0,
                    'price_child_without_markup' => 0,
                    'price_child' => 0
                ]);
            }
        });
    }

    /*
     * Todo Metodo que agrega todos los pasajeros para el servicio nuevo
     */
    private function addPassengerToNewService($quote_service_id, $quote_category_id, $type = 'service')
    {
        $quote_category = QuoteCategory::find($quote_category_id);
        if ($type === 'service') {
            if ($quote_category) {
                $quote_passengers = QuotePassenger::where('quote_id', $quote_category->quote_id)
                    ->orderBy('id')
                    ->get(['id', 'type']);
                foreach ($quote_passengers as $passenger) {
                    $quote_service_passenger = new QuoteServicePassenger();
                    $quote_service_passenger->quote_service_id = $quote_service_id;
                    $quote_service_passenger->quote_passenger_id = $passenger['id'];
                    $quote_service_passenger->save();
                }
            }
        } else {

        }


    }

    public function deleteRoomDisabledByOccupationRoom($service_rate_ids)
    {

    }

}

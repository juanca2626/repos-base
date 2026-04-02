<?php

namespace App\Http\Controllers;

use App\Language;
use App\Quote;
use App\QuoteService;
use App\QuoteServiceRoom;
use App\Http\Traits\Quotes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class QuoteServiceRoomsController extends Controller
{
    use Quotes;

    public function replace(Request $request, $quote_service_id)
    {
        try {
            $lang = $request->input('lang');
            $language_id = Language::where('iso', $lang)->first()->id;
            $rate_plan_rooms_choose = $request->input('rate_plan_rooms_choose');
            $quote_id = $request->input('quote_id');
            if (count($rate_plan_rooms_choose) > 0) {
                DB::transaction(function () use ($quote_service_id, $rate_plan_rooms_choose, $quote_id) {
                    $quote_people = DB::table('quote_people')->where('quote_id', $quote_id)->first();
                    $total_people = $quote_people->adults + $quote_people->child;
                    $adult = 0;
                    $child = 0;
                    $single = 0;
                    $double = 0;
                    $triple = 0;
                    // if (!isset($rate_plan_rooms_choose[1]["occupation"])) {
                    //     DB::table('quote_service_rooms')->where('quote_service_id', $quote_service_id)->delete();
                    //     DB::table('quote_service_rooms')->insert([
                    //         'quote_service_id' => $quote_service_id,
                    //         'rate_plan_room_id' => $rate_plan_rooms_choose[0]["rate_plan_room_id"],
                    //         'created_at' => date("Y-m-d H:i:s"),
                    //         'updated_at' => date("Y-m-d H:i:s")
                    //     ]);
                    // } else {

                        
                        // $quote_service = DB::table('quote_services')->where('id', $quote_service_id)->first();

                        $quote_service = QuoteService::where('id', $quote_service_id)->with([                 
                            'service_rooms.rate_plan_room.room.room_type'
                        ])->get()->first();
            
                        if ($rate_plan_rooms_choose[0]["occupation"] == 1) {
                            $single = 1;
                        }
                        if ($rate_plan_rooms_choose[0]["occupation"] == 2) {
                            $double = 1;
                        }
                        if ($rate_plan_rooms_choose[0]["occupation"] == 3) { 
                            $triple = 1;
                        }

                        $quote_service->single = $single;
                        $quote_service->double = $double;
                        $quote_service->triple = $triple;
                        $quote_service->save();
                        
                        DB::table('quote_service_rooms')->where('quote_service_id', $quote_service_id)->delete();
                        DB::table('quote_service_rooms')->insert([
                            'quote_service_id' => $quote_service_id,
                            'rate_plan_room_id' => $rate_plan_rooms_choose[0]["rate_plan_room_id"],
                            'created_at' => date("Y-m-d H:i:s"),
                            'updated_at' => date("Y-m-d H:i:s")
                        ]);


                        // if ($quote_service->service_rooms[0]->rate_plan_room->room->room_type->occupation != $rate_plan_rooms_choose[0]["occupation"]) {
                        //     // $quote_service = DB::table('quote_services')->where('id', $quote_service_id)->first();
                        //     $this->destroyRoomsDisabledByOccupations($quote_service,
                        //         $rate_plan_rooms_choose[0]["occupation"]);

                        //     $quote_service_id_new = DB::table('quote_services')->insertGetId([
                        //         'quote_category_id' => $quote_service->quote_category_id,
                        //         'type' => $quote_service->type,
                        //         'object_id' => $quote_service->object_id,
                        //         'order' => $quote_service->order,
                        //         'date_in' => $quote_service->date_in,
                        //         'date_out' => $quote_service->date_out,
                        //         'hour_in' => $quote_service->hour_in,
                        //         'nights' => $quote_service->nights,
                        //         'adult' => $adult,
                        //         'child' => $child,
                        //         'infant' => 0,
                        //         'single' => $single,
                        //         'double' => $double,
                        //         'double_child' => 0,
                        //         'triple' => $triple,
                        //         'triple_child' => 0,
                        //         'triple_active' => 0,
                        //         'locked' => $quote_service->locked,
                        //         'on_request' => $rate_plan_rooms_choose[0]["on_request"],
                        //         'extension_id' => $quote_service->extension_id,
                        //         'parent_service_id' => $quote_service->parent_service_id,
                        //         'optional' => $quote_service->optional,
                        //         'code_flight' => $quote_service->code_flight,
                        //         'origin' => $quote_service->origin,
                        //         'destiny' => $quote_service->destiny,
                        //         'date_flight' => $quote_service->date_flight,
                        //         'notes' => $quote_service->notes,
                        //         'schedule_id' => $quote_service->schedule_id,
                        //         'created_at' => $quote_service->created_at,
                        //         'updated_at' => $quote_service->updated_at
                        //     ]);
                        //     DB::table('quote_service_rooms')->insert([
                        //         'quote_service_id' => $quote_service_id_new,
                        //         'rate_plan_room_id' => $rate_plan_rooms_choose[0]["rate_plan_room_id"],
                        //         'created_at' => date("Y-m-d H:i:s"),
                        //         'updated_at' => date("Y-m-d H:i:s")
                        //     ]);
                        //     $this->updateOccupationRoomHotelByQuote($quote_id);
                        // } else {
                        //     DB::table('quote_service_rooms')->where('quote_service_id', $quote_service_id)->delete();
                        //     DB::table('quote_service_rooms')->insert([
                        //         'quote_service_id' => $quote_service_id,
                        //         'rate_plan_room_id' => $rate_plan_rooms_choose[0]["rate_plan_room_id"],
                        //         'created_at' => date("Y-m-d H:i:s"),
                        //         'updated_at' => date("Y-m-d H:i:s")
                        //     ]);
                        // }
                    // }
    
                    $this->updateListPassengersRoomsHotel($quote_service_id, $quote_id);
                });

            }

            $service_rooms = QuoteServiceRoom::where('quote_service_id', $quote_service_id)
                ->with([
                    'rate_plan_room.rate_plan',
                    'rate_plan_room.calendarys' => function ($query) {
                        $query->with('rate');
                    },
                    'rate_plan_room.room.room_type',
                    'rate_plan_room.room.translations' => function ($query) use ($language_id) {
                        $query->where('language_id', $language_id);
                    }
                ])
                ->get();
                
            $client_id = $this->getClientId($request);
            $this->updateAmountAllServices($quote_id, $client_id);

            $response = ['success' => true, 'service_rooms' => $service_rooms];
        } catch (\Exception $exception) {
            $response = ['success' => false, 'error' => $exception->getMessage()];

        }
        return Response::json($response);
    }

    public function addFromHeader(Request $request, $quote_service_id)
    {
        // return Response::json("se proceso todo");
        try {

            $lang = $request->input('lang');
            $language_id = Language::where('iso', $lang)->first()->id;
            $rate_plan_rooms_choose = $request->input('rate_plan_rooms_choose');
            $quote_id = $request->input('quote_id');
            $cant = $request->input('cant'); // este parametro lo enviamos cuando estamos agregando habitacione dinamicamente
            $quote_service_add = $request->input('quote_service');

            if (count($rate_plan_rooms_choose) > 0) {
                // DB::transaction(function () use ($quote_service_id, $rate_plan_rooms_choose, $quote_id, $cant) {
                    $quote = Quote::find($quote_id, ['id','operation']);
                    if ($quote) {
                        $quote_people = DB::table('quote_people')->where('quote_id', $quote_id)->first();
                        $quote_accommodations = DB::table('quote_accommodations')->where('quote_id', $quote_id)->first();
                        $accommodations_count = $cant ? $cant : 1;
                        $adult = 1;
                        $child = 0;
                        $single = 0;
                        $double = 0;
                        $triple = 0;
                        foreach ($rate_plan_rooms_choose as $index => $room) {

                            if (($index != 0 and $room['choose']) || ($index == 0 and $room['choose'])) {

                                if ($rate_plan_rooms_choose[$index]["occupation"] == 1) {
                                    $adult = 1;
                                    $child = 0;
                                    $single = 1;
                                    if ($quote_accommodations and $quote_accommodations->single > 0) {
                                        // $accommodations_count = $quote_accommodations->single;
                                    }
                                }
                                if ($rate_plan_rooms_choose[$index]["occupation"] == 2) {
                                    if ($quote_accommodations and $quote_accommodations->double > 0) {
                                        // $accommodations_count = $quote_accommodations->double;
                                    }
                                    if ($quote_people and $quote_people->child > 0) {
                                        $adult = 1;
                                        $child = 1;
                                    } else {
                                        $adult = 2;
                                        $child = 0;
                                    }
                                    $double = 1;
                                }
                                if ($rate_plan_rooms_choose[$index]["occupation"] == 3) {
                                    if ($quote_accommodations and $quote_accommodations->triple > 0) {
                                        // $accommodations_count = $quote_accommodations->triple;
                                    }
                                    if ($quote_people and $quote_people->child > 0) {
                                        $adult = 2;
                                        $child = 1;
                                    } else {
                                        $adult = 3;
                                        $child = 0;
                                    }
                                    $triple = 1;
                                }

                                $quote_service = DB::table('quote_services')
                                    ->where('id', $quote_service_id)
                                    ->first();

                                if($quote_service){    

                                    $this->destroyRoomsDisabledByOccupationsAddFromHeader($quote_service,$rate_plan_rooms_choose[$index]["occupation"]);
                                    for ($i = 0; $i < $accommodations_count; $i++) {
                                        $quote_service_id_new = DB::table('quote_services')->insertGetId([
                                            'quote_category_id' => $quote_service->quote_category_id,
                                            'type' => $quote_service->type,
                                            'object_id' => $quote_service->object_id,
                                            'order' => $quote_service->order,
                                            'date_in' => $quote_service->date_in,
                                            'date_out' => $quote_service->date_out,
                                            'hour_in' => $quote_service->hour_in,
                                            'nights' => $quote_service->nights,
                                            'adult' => $adult,
                                            'child' => $child,
                                            'infant' => 0,
                                            'single' => $single,
                                            'double' => $double,
                                            'double_child' => 0,
                                            'triple' => $triple,
                                            'triple_child' => 0,
                                            'triple_active' => 0,
                                            'locked' => $quote_service->locked,
                                            'on_request' => $rate_plan_rooms_choose[$index]["on_request"],
                                            'new_extension_id' => $quote_service->new_extension_id,
                                            'extension_id' => $quote_service->extension_id,
                                            'parent_service_id' => $quote_service->parent_service_id,
                                            'optional' => $quote_service->optional,
                                            'code_flight' => $quote_service->code_flight,
                                            'origin' => $quote_service->origin,
                                            'destiny' => $quote_service->destiny,
                                            'date_flight' => $quote_service->date_flight,
                                            'notes' => $quote_service->notes,
                                            'schedule_id' => $quote_service->schedule_id,
                                            'created_at' => $quote_service->created_at,
                                            'updated_at' => $quote_service->updated_at
                                        ]);
                                        DB::table('quote_service_rooms')->insert([
                                            'quote_service_id' => $quote_service_id_new,
                                            'rate_plan_room_id' => $rate_plan_rooms_choose[$index]["rate_plan_room_id"],
                                            'created_at' => date("Y-m-d H:i:s"),
                                            'updated_at' => date("Y-m-d H:i:s")
                                        ]);
    
                                        $this->updateListPassengersRoomsHotel($quote_service_id_new, $quote_id);
                                    }                                    

                                }else{
                                    // Este proceso se lanza cuando lo llamamos desde el modulo de ocupacion, y pasa cuando se esta agregando hoteles que se ha eliminado porque no tenia un quote_service_rooms.
                                    if($quote_service_add){  
                                        
                                        for ($i = 0; $i < $accommodations_count; $i++) {
                                            $quote_service_id_new = DB::table('quote_services')->insertGetId([
                                                'quote_category_id' => $quote_service_add['quote_category_id'],
                                                'type' => $quote_service_add['type'],
                                                'object_id' => $quote_service_add['object_id'],
                                                'order' => $quote_service_add['order'],
                                                'date_in' => $quote_service_add['date_in'],
                                                'date_out' => $quote_service_add['date_out'],
                                                'hour_in' => $quote_service_add['hour_in'],
                                                'nights' => $quote_service_add['nights'],
                                                'adult' => $adult,
                                                'child' => $child,
                                                'infant' => 0,
                                                'single' => $single,
                                                'double' => $double,
                                                'double_child' => 0,
                                                'triple' => $triple,
                                                'triple_child' => 0,
                                                'triple_active' => 0,
                                                'locked' => $quote_service_add['locked'],
                                                'on_request' => $rate_plan_rooms_choose[$index]["on_request"],
                                                'new_extension_id' => $quote_service_add['new_extension_id'],
                                                'extension_id' => $quote_service_add['extension_id'],
                                                'parent_service_id' => $quote_service_add['parent_service_id'],
                                                'optional' => $quote_service_add['optional'],
                                                'code_flight' => $quote_service_add['code_flight'],
                                                'origin' => $quote_service_add['origin'],
                                                'destiny' => $quote_service_add['destiny'],
                                                'date_flight' => $quote_service_add['date_flight'],
                                                'notes' => $quote_service_add['notes'],
                                                'schedule_id' => $quote_service_add['schedule_id'],
                                                'created_at' => $quote_service_add['created_at'],
                                                'updated_at' => $quote_service_add['updated_at']
                                            ]);
                                            DB::table('quote_service_rooms')->insert([
                                                'quote_service_id' => $quote_service_id_new,
                                                'rate_plan_room_id' => $rate_plan_rooms_choose[$index]["rate_plan_room_id"],
                                                'created_at' => date("Y-m-d H:i:s"),
                                                'updated_at' => date("Y-m-d H:i:s")
                                            ]);
        
                                            $this->updateListPassengersRoomsHotel($quote_service_id_new, $quote_id);
                                        }

                                    }
                                }

                            }

                        }

                        if ($quote->operation != 'ranges') {
                            // Ya no debemos de actalizar la cabecera principal
                            // $this->updateOccupationRoomHotelByQuote($quote_id);  
                        }
                    }

                // });

            }
            
            // $client_id = $this->getClientId($request);
            // $this->updateAmountAllServices($quote_id, $client_id);  -- esto ya lo hacemos cuando cargamos la cotizacion

            $service_rooms = QuoteServiceRoom::where('quote_service_id', $quote_service_id)
                ->with([
                    'rate_plan_room.rate_plan',
                    'rate_plan_room.calendarys' => function ($query) {
                        $query->with('rate');
                    },
                    'rate_plan_room.room.room_type',
                    'rate_plan_room.room.translations' => function ($query) use ($language_id) {
                        $query->where('language_id', $language_id);
                    }
                ])
                ->get();

            $response = ['success' => true, 'service_rooms' => $service_rooms];
        } catch (\Exception $exception) {
            $response = ['success' => false, 'error' => $exception->getMessage() . ' - ' . $exception->getLine()];
        }
        return Response::json($response);
    }

    public function destroy($id)
    {
        $quote_service_room = QuoteServiceRoom::with([
            'rate_plan_room' => function ($query) {
                $query->with(['room.room_type']);
            }
        ])->with([
            'service' => function ($query) {
                $query->select(['id', 'single', 'double', 'triple']);
            }
        ])->find($id);

        if ($quote_service_room) {
            if ($quote_service_room->rate_plan_room->room->room_type->occupation === 1) {
                $service = QuoteService::find($quote_service_room->service->id);
                $service->single = 0;
                $service->save();
            }
            if ($quote_service_room->rate_plan_room->room->room_type->occupation === 2) {
                $service = QuoteService::find($quote_service_room->service->id);
                $service->double = 0;
                $service->save();
            }
            if ($quote_service_room->rate_plan_room->room->room_type->occupation === 3) {
                $service = QuoteService::find($quote_service_room->service->id);
                $service->triple = 0;
                $service->save();
            }
            $quote_service_room->delete();
            $response = ['success' => true];
        } else {
            $response = ['success' => false];
        }

        return Response::json($response);
    }
}

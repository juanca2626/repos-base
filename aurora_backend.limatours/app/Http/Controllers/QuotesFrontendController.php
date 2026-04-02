<?php

namespace App\Http\Controllers;

use App\QuoteDynamicSaleRate;
use App\QuoteRange;
use Illuminate\Http\Request;
use App\Client;
use Carbon\Carbon;
use App\Language;
use App\Quote;
use App\QuoteCategory;
use App\QuotePassenger;
use App\QuotePeople;
use App\QuoteService;
use App\QuoteServiceAmount;
use App\QuoteServicePassenger;
use Illuminate\Support\Facades\DB;

class QuotesFrontendController extends Controller
{
    public $quote_id = 0;
    public $lang = 'en';
    public $quote_category_id = 0;

    public function passengers(Request $request)
    {
        $this->quote_id = $request->__get('quote_id');
        $this->lang = $request->__get('lang');

        $data = [
            'quote_name' => "",
            'client_code' => "",
            'client_name' => "",
            'lang' => "",
            'passengers' => [],
            'categories' => []
        ];
        $client = (object) ['code' => '', 'name' => ''];

        try
        {
            $quote = Quote::where('id', $this->quote_id)->first();

            $data['quote_name'] = $quote->name;
            $data['client_code'] = $client->code;
            $data['client_name'] = $client->name;
            $data['lang'] = $this->lang;
            $occupation_name = "";
            $multiplePassengers = false;
            $language_id = Language::where('iso', $this->lang)->first()->id;
            $quote_categories = DB::table('quote_categories')->where('quote_id', '=', $this->quote_id)
                ->get();

            $data_quotes_categories = [];

            foreach($quote_categories as $key => $value)
            {
                $this->quote_category_id = $value->id;

                $category = QuoteCategory::where('id', $this->quote_category_id)
                    ->where('quote_id', $this->quote_id)
                    ->with('type_class.translations')->first();
                $data['passengers'] = QuotePassenger::where('quote_id', $this->quote_id)->get()->toArray();

                // dd($category);

                array_push($data["categories"], [
                    'category' => $category['type_class']["translations"][0]['value'],
                    'services' => []
                ]);
                $quote_services = QuoteService::where('quote_category_id', $category["id"])
                    ->with(['service'=>function($query)use($language_id){
                        $query->with(['service_translations'=>function($query)use($language_id){
                            $query->where('language_id',$language_id);
                        }]);
                    }])
                    ->with(['service_rooms.rate_plan_room.room.translations' => function ($query) use ($language_id) {
                        $query->where('language_id', $language_id);
                    }, 'service_rooms.rate_plan_room.room.room_type'])
                    ->with('hotel.channel')
                    ->orderBy('date_in')->get();
                $quote_people = QuotePeople::where('quote_id', $this->quote_id)->first();
                foreach ($data["passengers"] as $index_passenger => $passenger) {
                    $data["passengers"][$index_passenger]["total"] = 0;
                }

                $_general_single = 0;
                $_general_double = 0;
                $_general_triple = 0;
                $count_hotels = 0;
                foreach ($quote_services as $quote_service) {
                    if ($quote_service["type"] == "hotel") {
                        $count_hotels++;
                        if ($quote_service["single"]> 0 || $quote_service["double"]>0 || $quote_service["triple"]>0)
                        {
                            $_general_single = $quote_service["single"];
                            $_general_double = $quote_service["double"];
                            $_general_triple = $quote_service["triple"];
                            break;
                        }
                    }
                }

                if ($_general_single==0 && $_general_double==0 && $_general_triple==0 && $count_hotels>0)
                {
                    return var_export("Error, ningún hotel tiene acomodación");
                }

                foreach ($quote_services as $quote_service) {
                    if ($quote_service["type"] == "service" OR $quote_service['type'] == 'hotel') {
                        $service_amount = QuoteServiceAmount::where('quote_service_id', $quote_service["id"]);

                        if ($service_amount->count() > 0) {
                            $service_amount_adult = $service_amount->first()->price_adult;
                            $service_amount_child = $service_amount->first()->price_child;
                        } else{
                            $service_amount_adult = 0;
                            $service_amount_child = 0;
                        }

                        $passengers = [];
                        foreach ($data["passengers"] as $index_passenger => $passenger) {

                                if($passenger['type'] == 'ADL')
                                {
                                    array_push($passengers, number_format($service_amount_adult, 2, '.', ''));
                                }

                                if($passenger['type'] == 'CHD')
                                {
                                    if($$quote_service['child'] > 0)
                                    {
                                        array_push($passengers, number_format($service_amount_child, 2, '.', ''));
                                    }
                                }
//                        $data["passengers"][$index_passenger]["total"] += number_format(($service_amount / count($data["passengers"])),2, '.', '');
                        }

                        array_push($data["categories"][count($data["categories"]) - 1]["services"], [
                            'date_service' => $quote_service["date_in"],
                            // 'service_code' => $quote_service["service"]["aurora_code"],
                            // 'service_name' => $quote_service["service"]["service_translations"][0]["name_commercial"],
                            'passengers' => $passengers
                        ]);
                    }

                    /*
                    if ($quote_service["type"] == "hotel") {
                        $service_amount = QuoteServiceAmount::where('quote_service_id', $quote_service["id"])->first();
                        // Comentado xq sino ignora hoteles que deberían verse por acomodacion propia
//                        if ($quote_service["single"]== 0 && $quote_service["double"]==0 || $quote_service["triple"]==0)
//                        {
//                            $quote_service["single"] = $_general_single;
//                            $quote_service["double"] = $_general_double;
//                            $quote_service["triple"] = $_general_triple;
//                        }
                        if ($quote_service["single"]> 0 && $quote_service["double"]>0 || $quote_service["triple"]>0)
                        {
                            $multiplePassengers = true;
                        }
                        if ($quote_service["double"]> 0 && $quote_service["single"]>0 || $quote_service["triple"]>0)
                        {
                            $multiplePassengers = true;
                        }
                        if ($quote_service["triple"]> 0 && $quote_service["double"]>0 || $quote_service["single"]>0)
                        {
                            $multiplePassengers = true;
                        }
                        $passengers = [];
                        $_type_rooms = [];
                        $pivot_index_passenger = null;
                        $quantity_people = $quote_service["adult"] + $quote_service["child"];
                        if ($quote_service["single"]>0)
                        {
                            $amount_for_room_single = number_format(($service_amount["single"] / $quote_service["single"]),2, '.', '');
                            $quantity_rooms_single  = $quote_service["single"];
                            foreach ($data["passengers"] as $index_passenger => $passenger) {
                                array_push($passengers, number_format(($amount_for_room_single / $quote_service["nights"]),2, '.', ''));
                                array_push($_type_rooms,1);
//                        $data["passengers"][$index_passenger]["total"] += number_format($amount_for_room_single,2, '.', '');
                                $quantity_people-=1;
                                $quantity_rooms_single-=1;
                                $occupation_name = " - SGL";
                                if (strpos($data["passengers"][$index_passenger]["last_name"], 'SGL') === false) {
                                    $data["passengers"][$index_passenger]["last_name"] = $data["passengers"][$index_passenger]["last_name"].' - SGL';
                                }
                                if ($quantity_people==0 || $quantity_rooms_single == 0)
                                {
                                    $pivot_index_passenger = $index_passenger;
                                    break;
                                }
                            }
                        }
                        if ($quote_service["double"]>0)
                        {
                            $amount_for_room_double_for_person = number_format((($service_amount["double"] / $quote_service["double"]) / 2),2, '.', '');
                            $quantity_rooms_double  = $quote_service["double"];
                            $quantity_persons = 2 * $quantity_rooms_double;
                            foreach ($data["passengers"] as $index_passenger2 => $passenger) {
                                if ($index_passenger2 > $pivot_index_passenger || is_null($pivot_index_passenger))
                                {
                                    // TODO array_push($passengers, number_format(($amount_for_room_double_for_person / $quote_service["nights"]),2, '.', ''));
                                    array_push($passengers, number_format(($amount_for_room_double_for_person),2, '.', ''));
                                    array_push($_type_rooms,2);
//                            $data["passengers"][$index_passenger]["total"] += number_format($amount_for_room_double_for_person,2, '.', '');
                                    $quantity_people-=1;
                                    $quantity_persons-=1;
                                    $occupation_name = " - DBL";
                                    if (strpos($data["passengers"][$index_passenger2]["last_name"], 'DBL') === false) {
                                        $data["passengers"][$index_passenger2]["last_name"] = $data["passengers"][$index_passenger2]["last_name"].' - DBL';
                                    }

                                    if ($quantity_persons == 0)
                                    {
                                        $quantity_rooms_double = 0;
                                    }
                                    if ($quantity_people==0 || $quantity_rooms_double == 0)
                                    {

                                        $pivot_index_passenger = $index_passenger2;
                                        break;
                                    }
                                }
                            }
                        }
                        if ($quote_service["triple"]>0)
                        {
                            $amount_for_room_triple_for_person = ($service_amount["triple"] / $quote_service["triple"]) / 3;
                            $quantity_rooms_triple = $quote_service["triple"];
                            $quantity_persons = 0;
                            foreach ($data["passengers"] as $index_passenger3 => $passenger) {

                                if ($index_passenger3 > $pivot_index_passenger || is_null($pivot_index_passenger))
                                {
//                                TODO    array_push($passengers,number_format(($amount_for_room_triple_for_person / $quote_service["nights"]), 2, '.', ''));
                                    array_push($passengers,number_format(($amount_for_room_triple_for_person), 2, '.', ''));
                                    array_push($_type_rooms,3);
//                            $data["passengers"][$index_passenger]["total"] += number_format($amount_for_room_triple_for_person, 2, '.', '');
                                    $quantity_people-=1;
                                    $quantity_persons+=1;
                                    $occupation_name = " - TPL";
                                    if (strpos($data["passengers"][$index_passenger3]["last_name"], 'TPL') === false) {
                                        $data["passengers"][$index_passenger3]["last_name"] = $data["passengers"][$index_passenger3]["last_name"].' - TPL';
                                    }
                                    if ($quantity_persons == 3 || $quantity_people == 0)
                                    {
                                        $quantity_rooms_triple-=1;
                                        $quantity_persons = 0;
                                    }
                                    if ($quantity_people==0 || $quantity_rooms_triple == 0)
                                    {
                                        break;
                                    }
                                }
                            }
                        }

//                $name_room = '';
//                if( count( $quote_service["service_rooms"] ) > 0 ){
//                    $name_room = " - " . $quote_service["service_rooms"][0]['rate_plan_room']['room']['translations'][0]['value'];
//                }
                        for ($i = 0; $i < $quote_service["nights"]; $i++) {
                            array_push($data["categories"][count($data["categories"]) - 1]["services"], [
                                'date_service' => Carbon::parse(Carbon::createFromFormat('d/m/Y', $quote_service["date_in"])->format('Y-m-d'))->addDays($i)->format('d/m/Y'),
                                'service_code' => $quote_service["hotel"]["channel"][0]["code"],
                                'service_name' => $quote_service["hotel"]["name"],
                                'passengers' => $passengers,
                                '_type_rooms' => $_type_rooms,
                                '_service_rooms' => $quote_service["service_rooms"]
                            ]);
//                    dd($data["passengers"]);
                        }
                    }
                    */
                }

                // dd($data['passengers']);

                if (!$multiplePassengers) {
                    $total = $data["passengers"][0]["total"];
                    $data["passengers"] = [
                        [
                            "first_name" => "PAX".$occupation_name,
                            "last_name" => "",
                            "total" => $total
                        ]
                    ];

                    foreach ($data["categories"][0]["services"] as $index_service => $service) {
                        array_splice($data["categories"][0]["services"][$index_service]["passengers"], 1);
                    }
                }

                foreach ($data["passengers"] as $index_passenger => $passenger)
                {
                    foreach ($data["categories"][0]["services"] as $service)
                    {
                        $data["passengers"][$index_passenger]["total"] += number_format(@$service["passengers"][$index_passenger],2, '.', '');
                    }
                }

//        var_export(json_encode($data)); die;
                // Nombres de Habitaciones
                $_new_data = $data;
                $_new_data["categories"][0]["services"] = [];
                $_i_new_services = 0;
                foreach ($data["categories"][0]["services"] as $service)
                {
                    /*
                    if( isset( $service['_service_rooms'] ) ){ // es H
                        $already_render_type = [];
                        foreach( $service['passengers'] as $index_passenger => $passenger ){ // 0.00 | 5.58
                            if( (double)$passenger > 0 && !isset($already_render_type[$service['_type_rooms'][$index_passenger]]) ){
                                $already_render_type[$service['_type_rooms'][$index_passenger]] = true;
                                $_new_data["categories"][0]["services"][$_i_new_services]['date_service'] = $service['date_service'];
                                $_new_data["categories"][0]["services"][$_i_new_services]['passengers'] = [];
                                foreach( $service['passengers'] as $index_passenger_2 => $passenger_2 ){

                                    if( $service['_type_rooms'][$index_passenger] == $service['_type_rooms'][$index_passenger_2] ){
                                        array_push( $_new_data["categories"][0]["services"][$_i_new_services]['passengers'], $passenger_2 );
                                    } else {
                                        array_push( $_new_data["categories"][0]["services"][$_i_new_services]['passengers'], "0.00" );
                                    }
                                }
                                $_new_data["categories"][0]["services"][$_i_new_services]['service_code'] = $service['service_code'];

                                $name_room = '';
                                foreach( $service["_service_rooms"] as $s_r ){
                                    if( $service['_type_rooms'][$index_passenger] == $s_r['rate_plan_room']['room']['room_type']['occupation'] ){
                                        $name_room = " - " . $s_r['rate_plan_room']['room']['translations'][0]['value'];
                                        break;
                                    }
                                }

                                $_new_data["categories"][0]["services"][$_i_new_services]['service_name'] = $service['service_name'] . $name_room;
                                $_i_new_services++;
                            }
                        }
                    } else {
                        */
                        $_new_data["categories"][0]["services"][$_i_new_services] = $service;
                        $_i_new_services++;
                    // }
                }

                $data_quotes_categories[] = $_new_data;
            }

            // Nombres de Habitaciones

//        var_export( json_encode( $_new_data ) ); die;

            $categories = []; $passengers = count($data_quotes_categories[(count($data_quotes_categories) - 1)]['passengers']);

            foreach($data_quotes_categories[(count($data_quotes_categories) - 1)]['categories'] as $key => $value)
            {
                $mount_total = 0;

                foreach($value['services'] as $k => $v)
                {
                    if(isset($v['passengers'][$key]))
                    {
                        $mount_total += $v['passengers'][$key];
                    }
                }

                $categories[] = ['category' => $value['category'], 'mount' => $mount_total * $passengers, 'count' => count($value['services'])];
            }

            $response = [
                'categories' => $categories,
                'passengers' => $passengers,
                'data' => $data_quotes_categories,
                'discount' => $quote->discount,
                'discount_detail' => $quote->discount_detail
            ];

            return response()->json($response);
        }
        catch(\Exception $ex)
        {
            return response()->json(['message' => $ex->getMessage()]);
        }
    }

    public function ranges(Request $request)
    {
        $this->quote_id = $request->__get('quote_id');
        $this->lang = $request->__get('lang');

        $data = [
            'quote_name'=>"",
            'client_code' => "",
            'client_name' => "",
            'lang' => "",
            'ranges_quote' => [],
            'categories' => []
        ];
        $client = (object) ['code' => '', 'name' => ''];

        try
        {
            $quote = Quote::where('id',$this->quote_id)->first();

            $data['quote_name']  = $quote->name;
            $data['client_code'] = $client->code;
            $data['client_name'] = $client->name;
            $data['lang'] = $this->lang;
            $language_id = Language::where('iso', $this->lang)->first()->id;
            // = "";
            $quote_categories = QuoteCategory::where('quote_id', $this->quote_id)->with('type_class.translations')->get();
            $data_quotes_categories = [];

            // dd($quote_categories);

            foreach($quote_categories as $key => $value)
            {
                $this->quote_category_id = $value->id;

                $category = QuoteCategory::where('id', $this->quote_category_id)->where('quote_id', $this->quote_id)->with('type_class.translations')->first();
                $ranges_quote = QuoteRange::where('quote_id', $this->quote_id)->get();

                foreach ($ranges_quote as $range_quote)
                {
                    $amount_range = QuoteDynamicSaleRate::where('quote_category_id', $category["id"])->where('pax_from',$range_quote["from"])->where('pax_to',$range_quote["to"])->sum('simple');

                    array_push($data['ranges_quote'],[
                        'from'=>$range_quote["from"],
                        'to'=>$range_quote["to"],
                        'amount'=>$amount_range
                    ]);
                }

                array_push($data["categories"], [
                    'category' => $category['type_class']["translations"][0]['value'],
                    'services' => []
                ]);
                $quote_services = QuoteService::where('quote_category_id', $category["id"])
                    ->with('service')->with('hotel.channel')
                    ->with(['service_rooms.rate_plan_room.room.translations' => function ($query) use ($language_id) {
                        $query->where('language_id', $language_id);
                    }])
                    ->orderBy('date_in')->get();

                foreach ($quote_services as $quote_service) {
                    $ranges = QuoteDynamicSaleRate::where('quote_category_id', $category["id"])->where('quote_service_id', $quote_service["id"])->orderBy('date_service')->get();

                    if(count( $ranges )>0){
                        if ($quote_service["type"] == "service") {
                            array_push($data["categories"][count($data["categories"]) - 1]["services"], [
                                'date_service' => $ranges[0]["date_service"],
                                'service_code'=> $quote_service["service"]["aurora_code"],
                                'service_name' => $quote_service["service"]["name"],
                                'ranges' => $ranges
                            ]);
                        }
                        if ($quote_service["type"] == "hotel") {
                            $date_services = $ranges->groupBy('date_service');

                            $name_room = '';
//                    if( count( $quote_service["service_rooms"] ) > 0 ){
//                        $name_room = " - " . $quote_service["service_rooms"][0]['rate_plan_room']['room']['translations'][0]['value'];
//                    }
                            foreach ($date_services as $date_service => $ranges) {
                                array_push($data["categories"][count($data["categories"]) - 1]["services"], [
                                    'date_service' => $date_service,
                                    'service_code'=>  $quote_service["hotel"]["channel"][0]["code"],
                                    'service_name' => $quote_service["hotel"]["name"] . $name_room,
                                    'ranges' => $ranges
                                ]);
                            }
                        }
                    }
                }

                $data_quotes_categories[] = $data;
            }

            $ranges = []; $categories = $data_quotes_categories[(count($data_quotes_categories) - 1)]['categories'];
            $cantidad = count($data_quotes_categories[(count($data_quotes_categories) - 1)]['ranges_quote']) / count($categories);

            $item = 0; $_key = 0;

            foreach($data_quotes_categories[(count($data_quotes_categories) - 1)]['ranges_quote'] as $key => $value)
            {
                if($item == $cantidad)
                {
                    $item = 0; $_key++;
                }

                $ranges[$_key][] = ['category' => $categories[$_key]['category'], 'range' => $value['from'] . ' - ' . $value['to'], 'mount' => $value['amount'] * $value['from']];

                $item++;
            }

            foreach($ranges as $key => $value)
            {
                $mount_total = 0;
                foreach($value as $k => $v)
                {
                    $mount_total += $v['mount'];
                }

                $ranges[$key]['promedio'] = $mount_total / count($value);
            }

            return response()->json([
                'ranges' => $ranges,
                'data' => $data_quotes_categories,
                'discount' => $quote->discount,
                'discount_detail' => $quote->discount_detail
            ]);
        }
        catch(\Exception $ex)
        {
            return response()->json(['message' => $ex->getMessage()]);
        }
    }
}

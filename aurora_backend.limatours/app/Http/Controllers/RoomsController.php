<?php

namespace App\Http\Controllers;

use App\DeactivatableEntity;
use App\Language;
use App\ProgressBar;
use App\Release;
use App\Room;
use App\Http\Traits\Package;
use App\Http\Traits\Translations;
use App\RoomType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use JD\Cloudder\Facades\Cloudder;

class RoomsController extends Controller
{
    use Translations, Package;

    public function __construct()
    {
        $this->middleware('permission:rooms.read')->only('index');
        $this->middleware('permission:rooms.create')->only('store');
        $this->middleware('permission:rooms.update')->only('update');
        $this->middleware('permission:rooms.delete')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'max_capacity' => 'required|integer|numeric|min:1',
            'min_adults' => 'required|integer|numeric|min:0',
            'max_adults' => 'required|integer|numeric|min:1',
            'max_child' => 'required|integer|numeric|min:0',
            'max_infants' => 'required|integer|numeric',
            'min_inventory' => 'required|integer|numeric',
            'state' => 'required|boolean',
            'hotel_id' => 'required|exists:hotels,id',
            //'translations.*.0.room_name' => 'unique:translations,value,NULL,id,type,room,slug,room_name',
            //'translations.*.1.room_description' => 'unique:translations,value,NULL,id,type,room,slug,room_description'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                array_push($arrayErrors, $error);
            }

            $countErrors++;
        }
        if ($countErrors > 0) {
            return Response::json(['success' => false, 'errors' => $arrayErrors]);
        } else {

            $room_type = RoomType::find($request->input("room_type_id"));
            if($request->input("max_adults") != $room_type->occupation ){
                array_push($arrayErrors, "El máximo de adultos tiene que ser igual a la ocupación del tipo de la habitación seleccionada");
            }

            if($request->input("max_child") > $request->input("max_capacity") ){
                array_push($arrayErrors, "El máximo de niños tiene que ser igual o menor a la capacidad maxima de la habitación");
            }

            if(count($arrayErrors)>0){
               return Response::json(['success' => false, 'errors' => $arrayErrors]);
            }

            $room = new Room();
            $room->room_type_id = $request->input("room_type_id");
            $room->max_capacity = $request->input("max_capacity");
            $room->min_adults = $request->input("min_adults");
            $room->max_adults = $request->input("max_adults");
            $room->max_child = $request->input("max_child");
            $room->max_infants = $request->input("max_infants");
            $room->min_inventory = $request->input("min_inventory");
            $room->state = $request->input("state");
            $room->hotel_id = $request->input("hotel_id");
            $room->ignore_rate_child = $request->input("ignore_rate_child");
            $room->save();

            $check_channels = 0;
            foreach ($request->input("channels") as $clave => $valor) {

                if ($valor['state'] == "true") {
                    $codeParaChannel = trim($valor['code']) ? trim($valor['code']) : $room->id;
                    $room->channels()->attach([
                        $clave => [
                            'code' => $codeParaChannel,
                            'state' => $valor['state'],
                            'type' => $valor['type'] ?? null
                        ]
                    ]);
                    $check_channels += 1;
                }

            }
            $this->saveMultipleTranslation($request->input("translations"), 'room', $room->id);
            if ($room->room_type_id != "" && $room->max_capacity != "" &&
                $room->min_adults && $room->max_adults != "" &&
                $room->max_child != "" && $room->max_infants != "" &&
                $room->min_inventory != "") {
                ProgressBar::updateOrCreate(
                    [
                        'slug' => 'hotel_progress_rooms_details',
                        'value' => 10,
                        'type' => 'room',
                        'object_id' => $room->id
                    ]
                );
            }
            ProgressBar::updateOrCreate(
                [
                    'slug' => 'hotel_progress_rooms_create',
                    'value' => 10,
                    'type' => 'hotel',
                    'object_id' => $room->hotel_id
                ]
            );

            $check_name = 0;
            $check_description = 0;

            foreach ($request->input("translations") as $translate_language) {
                foreach ($translate_language as $translate) {
                    if (array_key_exists('room_description', $translate)) {
                        if ($translate['room_description'] != "") {
                            $check_description += 1;
                        }
                    }
                    if (array_key_exists('room_name', $translate)) {
                        if ($translate['room_name'] != "") {
                            $check_name += 1;
                        }
                    }
                }
            }
            if ($check_description > 0) {
                ProgressBar::updateOrCreate(
                    [
                        'slug' => 'hotel_progress_rooms_descriptions',
                        'value' => 10,
                        'type' => 'hotel',
                        'object_id' => $room->hotel_id
                    ]
                );
            }
            if ($check_name > 0 && $check_description > 0) {
                ProgressBar::updateOrCreate(
                    [
                        'slug' => 'room_progress_descriptions',
                        'value' => 20,
                        'type' => 'room',
                        'object_id' => $room->id
                    ]
                );
            }
            if ($check_channels > 0) {
                ProgressBar::updateOrCreate(
                    [
                        'slug' => 'room_progress_channels',
                        'value' => 20,
                        'type' => 'room',
                        'object_id' => $room->id
                    ]
                );
            }
            return Response::json(['success' => true, 'object_id' => $room->id]);
        }
    }

    public function show($id, Request $request)
    {
        $lang = $request->input("lang");

        $room = Room::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'room');
            }
        ])->with([
            'room_type.translations' => function ($query) use ($lang) {
                $query->where('type', 'roomtype');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'galeries' => function ($query) use ($lang) {
                $query->where('type', 'room');
                $query->orderBy('position');
            }
        ])->with('channels')->where('id', $id)->first();

        return Response::json(['success' => true, 'data' => $room]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'max_capacity' => 'required|integer|numeric|min:1',
            'min_adults' => 'required|integer|numeric|min:0',
            'max_adults' => 'required|integer|numeric|min:1',
            'max_child' => 'required|integer|numeric|min:0',
            'max_infants' => 'required|integer|numeric',
            'min_inventory' => 'required|integer|numeric',
            'state' => 'required|boolean',
            //'translations.*.0.room_name' => 'unique:translations,value,' . $id . ',object_id,type,room,slug,room_name',
            //'translations.*.1.room_description' => 'unique:translations,value,' . $id . ',object_id,type,room,slug,room_description'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                array_push($arrayErrors, $error);
            }

            $countErrors++;
        }
        if ($countErrors > 0) {
            return Response::json(['success' => false]);
        } else {

            $room_type = RoomType::find($request->input("room_type_id"));
            if($request->input("max_adults") != $room_type->occupation ){
                array_push($arrayErrors, "El máximo de adultos tiene que ser igual a la ocupación del tipo de la habitación seleccionada");
            }

            if($request->input("max_child") > $request->input("max_capacity") ){
                array_push($arrayErrors, "El máximo de niños tiene que ser igual o menor a la capacidad maxima de la habitación");
            }

            if(count($arrayErrors)>0){
               return Response::json(['success' => false, 'errors' => $arrayErrors]);
            }


            $room = Room::find($id);
            $room->room_type_id = $request->input("room_type_id");
            $room->max_capacity = $request->input("max_capacity");
            $room->min_adults = $request->input("min_adults");
            $room->max_adults = $request->input("max_adults");
            $room->max_child = $request->input("max_child");
            $room->max_infants = $request->input("max_infants");
            $room->min_inventory = $request->input("min_inventory");
            $room->state = $request->input("state");
            $room->ignore_rate_child = $request->input("ignore_rate_child");
            $room->save();

            $check_channels = 0;
            /*foreach ($request->input("channels") as $clave => $valor) {
                if ($valor['state'] == "true") {
                    $codeParaChannel = trim($valor['code']) ? trim($valor['code']) : $room->id;
                    $room->channels()->sync([
                        $clave => [
                            'code' => $codeParaChannel,
                            'state' => $valor['state'],
                            'type' => $valor['type'] ?? null
                        ]
                    ], false);
                    $check_channels += 1;
                }
            }*/
            foreach ($request->input("channels") as $clave => $valor) {
                $codeParaChannel = trim($valor['code']) !== '' ? trim($valor['code']) : $room->id;
                $room->channels()->sync([
                    $clave => [
                        'code'  => $codeParaChannel,
                        'state' => $valor['state'] ? 1 : 0,
                        'type' => $valor['type'] ?? null
                    ]
                ], false);
            }

            $this->saveMultipleTranslation($request->input("translations"), 'room', $room->id);
            if ($room->room_type_id != "" && $room->max_capacity != "" &&
                $room->min_adults && $room->max_adults != "" &&
                $room->max_child != "" && $room->max_infants != "" &&
                $room->min_inventory != "") {
                ProgressBar::updateOrCreate(
                    [
                        'slug' => 'hotel_progress_rooms_details',
                        'value' => 10,
                        'type' => 'room',
                        'object_id' => $room->id
                    ]
                );
            }
            ProgressBar::updateOrCreate(
                [
                    'slug' => 'hotel_progress_rooms_create',
                    'value' => 10,
                    'type' => 'hotel',
                    'object_id' => $room->hotel_id
                ]
            );
            $check_name = 0;
            $check_description = 0;

            foreach ($request->input("translations") as $translate_language) {
                foreach ($translate_language as $translate) {
                    if (array_key_exists('room_description', $translate)) {
                        if ($translate['room_description'] != "") {
                            $check_description += 1;
                        }
                    }
                    if (array_key_exists('room_name', $translate)) {
                        if ($translate['room_name'] != "") {
                            $check_name += 1;
                        }
                    }
                }
            }
            if ($check_description > 0) {
                ProgressBar::updateOrCreate(
                    [
                        'slug' => 'hotel_progress_rooms_descriptions',
                        'value' => 10,
                        'type' => 'hotel',
                        'object_id' => $room->hotel_id
                    ]
                );
            }
            if ($check_name > 0 && $check_description > 0) {
                ProgressBar::updateOrCreate(
                    [
                        'slug' => 'room_progress_descriptions',
                        'value' => 20,
                        'type' => 'room',
                        'object_id' => $room->id
                    ]
                );
            }
            if ($check_channels > 0) {
                ProgressBar::updateOrCreate(
                    [
                        'slug' => 'room_progress_channels',
                        'value' => 20,
                        'type' => 'room',
                        'object_id' => $room->id
                    ]
                );
            }
            return Response::json(['success' => true, 'object_id' => $room->id]);
        }
    }

    public function destroy($id)
    {

        $room = Room::find($id);

        $uses = $this->get_room_uses($id);

        if( count($uses) > 0 ){
            return Response::json(['success' => false, 'uses'=>$uses]);
        }

        $rate_plan_room_ids = DB::table('rates_plans_rooms')->where('room_id', $room->id)->pluck('id')->toArray();

        DB::table('date_range_hotels')->whereIn('rate_plan_room_id',$rate_plan_room_ids)->delete();

        DB::table('rates_plans_calendarys')->whereIn('rates_plans_room_id', $rate_plan_room_ids)
            ->orderBy('created_at', 'asc')->chunk(300, function ($calendars) {

            DB::table('rates')->whereIn('rates_plans_calendarys_id', $calendars->pluck('id')->toArray())->delete();
            DB::table('rates_plans_calendarys')->whereIn('id', $calendars->pluck('id')->toArray())->delete();

        });
//        DB::table('package_service_rooms')->whereIn('rate_plan_room_id', $rate_plan_room_ids)->delete();
        DB::table('package_service_optional_rooms')->whereIn('rate_plan_room_id', $rate_plan_room_ids)->delete();
        DB::table('quote_service_rooms')->whereIn('rate_plan_room_id', $rate_plan_room_ids)->delete();
        DB::table('policies_cancelations_rates_plans_rooms')->whereIn('rates_plans_rooms_id', $rate_plan_room_ids)->delete();
        DB::table('inventories')->whereIn('rate_plan_rooms_id', $rate_plan_room_ids)->delete();
        DB::table('bag_rates')->whereIn('rate_plan_rooms_id', $rate_plan_room_ids)->delete();
        DB::table('channel_room')->where('room_id',$room->id)->delete();
        DB::table('channel_room')->where('room_id',$room->id)->delete();

        $bag_room_ids = DB::table('bag_rooms')->where('room_id',$room->id)->pluck('id');
        DB::table('bag_rates')->whereIn('bag_room_id',$bag_room_ids)->delete();
        DB::table('inventory_bags')->whereIn('bag_room_id',$bag_room_ids)->delete();
        DB::table('bag_rooms')->where('room_id',$room->id)->delete();
        DB::table('rates_plans_rooms')->whereIn('id', $rate_plan_room_ids)->delete();
        DB::table('rooms')->where('id',$room->id)->delete();

        return Response::json(['success' => true]);
    }

    public function updateState($id, Request $request)
    {
        $room = Room::find($id);

        if ($request->input("state")) {
            $room->state = false;
        } else {
            $room->state = true;
        }

        if ($request->input('state'))
        {
            $uses = $this->get_room_uses($room->id);
            if( count($uses) > 0 ){
                return Response::json(['success' => false, 'uses'=>$uses]);
            }

            $room->save();

            $rate_plan_room_ids = DB::table('rates_plans_rooms')->where('room_id', $room->id)->pluck('id')->toArray();

            DB::table('date_range_hotels')->whereIn('rate_plan_room_id',$rate_plan_room_ids)->delete();

            DB::table('rates_plans_calendarys')->whereIn('rates_plans_room_id', $rate_plan_room_ids)
                ->orderBy('created_at', 'asc')->chunk(300, function ($calendars) {

                DB::table('rates')->whereIn('rates_plans_calendarys_id', $calendars->pluck('id')->toArray())->delete();
                DB::table('rates_plans_calendarys')->whereIn('id', $calendars->pluck('id')->toArray())->delete();

            });
//            DB::table('package_service_rooms')->whereIn('rate_plan_room_id', $rate_plan_room_ids)->delete();
            DB::table('package_service_optional_rooms')->whereIn('rate_plan_room_id', $rate_plan_room_ids)->delete();
            DB::table('quote_service_rooms')->whereIn('rate_plan_room_id', $rate_plan_room_ids)->delete();
            DB::table('policies_cancelations_rates_plans_rooms')->whereIn('rates_plans_rooms_id', $rate_plan_room_ids)->delete();
            DB::table('inventories')->whereIn('rate_plan_rooms_id', $rate_plan_room_ids)->delete();
            DB::table('bag_rates')->whereIn('rate_plan_rooms_id', $rate_plan_room_ids)->delete();
            DB::table('channel_room')->where('room_id',$room->id)->delete();
            DB::table('channel_room')->where('room_id',$room->id)->delete();

            $bag_room_ids = DB::table('bag_rooms')->where('room_id',$room->id)->pluck('id');
            DB::table('bag_rates')->whereIn('bag_room_id',$bag_room_ids)->delete();
            DB::table('inventory_bags')->whereIn('bag_room_id',$bag_room_ids)->delete();
            DB::table('bag_rooms')->where('room_id',$room->id)->delete();
            DB::table('rates_plans_rooms')->whereIn('id', $rate_plan_room_ids)->delete();
        } else {
            $room->save();
        }

        return Response::json(['success' => true]);
    }

    public function updateSeeInRates($id, Request $request)
    {
        $room = Room::find($id);
        if ($request->input("see_in_rates")) {
            $room->see_in_rates = false;
        } else {
            $room->see_in_rates = true;
        }
        $room->save();
        return Response::json(['success' => true]);
    }

    public function roomsHotel($id, Request $request)
    {
//        $limit = $request->input('limit');
        $limit = 25;
        $lang = $request->input('lang');
        $language_id = Language::where('iso',$lang)->first()->id;
        $rooms = Room::with([
            'translations' => function ($query) use ($language_id) {
                $query->where('type', 'room');
                $query->where('language_id',$language_id);
            }
        ])->with([
            'room_type.translations' => function ($query) use ($language_id) {
                $query->where('type', 'roomtype');
                $query->where('language_id',$language_id);

            }
        ])->with('channels')
        ->with(['hotel.channel' => function ($query) {
            $query->where('channel_id', '=', 1);
        }])
        ->where('hotel_id', $id)
            ->orderBy('state', 'desc')
            ->orderBy('order', 'asc')
            ->get();

        foreach ($rooms as $room)
        {
            $images = $this->searchGalleryCloudinary('room', $room->id);

            if(config('app.env') === 'production')
            {
                if(!empty($images))
                {
                    if($room->created_cloudinary === 0)
                    {
                        $filename = 'https://backend.limatours.com.pe/images/logo.png';
                        $aurora_code = $room->hotel->channel[0]->code; $room_id = $room->id;
                        $folder = sprintf('hotels/%s/rooms/%s', $aurora_code, $room_id);

                        try
                        {
                            Cloudder::upload($filename, null, [
                                'folder' => $folder,
                                'public_id' => 'temp_placeholder'
                            ]);

                            // Borrar el archivo si solo quieres dejar la carpeta
                            Cloudder::destroyImage(sprintf('%s/temp_placeholder', $folder));

                            $room->created_cloudinary = 1;
                            $room->save();
                        }
                        catch(\Exception $ex)
                        {
                            app('sentry')->captureException($ex);
                        }
                    }

                }
                else
                {
                    if($room->created_cloudinary === 0)
                    {
                        $room->created_cloudinary = 1;
                        $room->save();
                    }
                }
            }

            $room->progress_bar_value = $room->progress_bars->sum('value');
        }

        return Response::json($rooms);
    }

    public function roomsByHotel(Request $request)
    {
        $hotel_id = $request->input('hotel_id');
        $lang = $request->input('lang');

        $rooms = Room::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'room');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->where('hotel_id', $hotel_id)->where('state',1)->get();

        return Response::json(['success' => true,'data'=>$rooms]);
    }
    public function roomsByHotelRelease(Request $request)
    {
        $hotel_id = $request->input('hotel_id');
        $lang = $request->input('lang');

        $rooms_releases_ids = Release::where('hotel_id',$hotel_id)->pluck('room_id');
        $rooms = Room::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'room');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->where('hotel_id', $hotel_id)->where('state',1)->whereNotIn('id',$rooms_releases_ids)->get();

        return Response::json(['success' => true,'data'=>$rooms]);
    }

    public function selectBox(Request $request)
    {
        $lang = $request->input('lang');
        $hotelID = $request->input('hotel_id');
        $rooms = Room::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'room');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->where('rooms.state', 1);

        if ($hotelID) {
            $rooms->where('rooms.hotel_id', (int)$hotelID);
        }

        $rooms = $rooms->orderBy('order')->get();

        return Response::json(['success' => true, 'data' => $rooms]);
    }

    public function roomsWithRates(Request $request)
    {
        $hotel_id = $request->input("hotel_id");
        $lang = $request->input("lang");
        $allotment = $request->input("allotment");
        $rooms_array = [];
        $rates_array = [];

        $rooms = Room::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'room');
                $query->where('slug', 'room_name');
                $query->where('language_id',1);
            }
        ])
            ->whereHas('rates_plan_room', function ($query) use ($allotment) {
                $query->where('channel_id',1);
                $query->whereHas('rate_plan', function ($query) use ($allotment) {
                    $query->where('allotment', 1);
                });
            })
            ->with('rates_plan_room.rate_plan')
            ->with(['rates_plan_room.bag_rates.bag_room.bag'=>function($query){
                $query->where('bags.status',1);
            }])
            ->with(['rates_plan_room'=>function($query){
                $query->where('channel_id',1);
            }])
            ->where('inventory',1)
            ->where('hotel_id', $hotel_id)
            ->get();

        $sum = 0;

        for ($i = 0; $i < count($rooms); $i++) {
            $rooms_array[$rooms[$i]["id"]]["room_index"] = $i;
            $rooms_array[$rooms[$i]["id"]]["room_id"] = $rooms[$i]["id"];
            $rooms_array[$rooms[$i]["id"]]["room_name"] = $rooms[$i]["translations"][0]["value"];

            $rooms_array[$rooms[$i]["id"]]["selected"] = false;

            if ($i === 0) {
                $rooms_array[$rooms[$i]["id"]]["selected"] = true;
            }
            $bags_name = [];

            foreach ($rooms[$i]["rates_plan_room"] as $rate_plan_room) {

                if ($rate_plan_room["rate_plan"]["allotment"] == true) {
                    //la tarifa seleccionada esta incluida en una bolsa

                    if (count($rate_plan_room["bag_rates"]) > 0) {

                        if (!$this->checkExistBagRoom($bags_name,$rate_plan_room["bag_rates"][0]["bag_room"]["bag"]["name"]))
                        {
                            array_push($bags_name,$rate_plan_room["bag_rates"][0]["bag_room"]["bag"]["name"]);

                            $rates_array[$sum]["room_id"] = $rooms[$i]["id"];
                            $rates_array[$sum]["rate_name"] = $rate_plan_room["bag_rates"][0]["bag_room"]["bag"]["name"];
                            $rates_array[$sum]["bag_room_id"] = $rate_plan_room["bag_rates"][0]["bag_room"]["id"];
                            $rates_array[$sum]["rate_plan_rooms_id"] = "";
                            $rates_array[$sum]["selected"] = false;
                            $rooms_array[$rooms[$i]["id"]]["rate_index"] = $sum;
                        }
                    }
                    else {
                        //la tarifa NO esta incluida en una bolsa
                        $rates_array[$sum]["room_id"] = $rooms[$i]["id"];
                        $rates_array[$sum]["rate_name"] = $rate_plan_room["rate_plan"]["name"];
                        $rates_array[$sum]["bag_room_id"] = "";
                        $rates_array[$sum]["rate_plan_rooms_id"] = $rate_plan_room["id"];
                        $rates_array[$sum]["selected"] = false;
                        $rooms_array[$rooms[$i]["id"]]["rate_index"] = $sum;
                    }
                    if ($sum === 0) {
                        $rates_array[$sum]["selected"] = true;
                    }
                    $sum++;
                }
            }
        }

        return Response::json(['success' => true, 'rooms_array' => $rooms_array, 'rates_array' => $rates_array]);
    }

    private function checkExistBagRoom($bags_name,$bag_search)
    {
        foreach ($bags_name as $bag_name)
        {
            if ($bag_name == $bag_search)
            {
                return true;
            }
        }
        return false;
    }

    public function updateCheckInventory(Request $request)
    {
        $room_id = $request->post('room_id');

        $room = Room::find($room_id);

        if ($request->input('check_inventory')) {
            $room->inventory = false;
        } else {
            $room->inventory = true;
        }
        $room->save();

        return \response()->json("Visualizacion de inventario actualizada");
    }

    public function update_orders($room_id, Request $request)
    {
        $order = (int)$request->input('order');

        $room = Room::find($room_id);
        $room->order = ( $room->order < $order ) ? $order + 1 : $order;

        $hotel_id = $room->hotel_id;

        if( $room->save() ){

            $rooms = Room::where("hotel_id", $hotel_id)->orderBy('order')->orderBy('updated_at', 'desc')->get();

            foreach ( $rooms as $k => $room ){
                $room->order = $k+1;
                $room->save();
            }

            return \response()->json(["success" => true]);
        } else {
            return \response()->json(["success" => false]);
        }
    }

    public function report_uses($id, Request $request){

        try{

            $data = $request->input('data');

            $data["user"] = Auth::user()->name . ' (' . Auth::user()->code . ')';

            $room = Room::where('id', $id)->with('hotel.channels')->first();
            $data["hotel"] = $room->hotel->channels[0]->pivot->code . ' - ' . $room->hotel->name;

            $mail = mail::to("producto@limatours.com.pe");
            $mail->cc(["neg@limatours.com.pe","kams@limatours.com.pe", "qr@limatours.com.pe"]);

            $mail->send(new \App\Mail\NotificationRoomStatus($data));

            $new_deactivatable_entity = new DeactivatableEntity();
            $new_deactivatable_entity->entity = "App\Room";
            $new_deactivatable_entity->object_id = $id;
            $new_deactivatable_entity->after_hours = 48;
            $new_deactivatable_entity->param = "state";
            $new_deactivatable_entity->value = "0";
            $new_deactivatable_entity->save();

            return Response::json(['success' => true]);
        } catch(\Exception $e){
            return Response::json(['success' => false, 'error' => $e->getMessage()]);
        }


    }

}

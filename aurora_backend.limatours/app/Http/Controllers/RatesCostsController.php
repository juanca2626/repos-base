<?php

namespace App\Http\Controllers;

use App\BagRate;
use App\BagRoom;
use App\Channel;
use App\Client;
use App\Country;
use App\DateRangeHotel;
use App\DeactivatableEntity;
use App\GenerateRatesInCalendar;
use App\Hotel;
use App\HotelTax;
use App\Http\Requests\HotelRatePlanRoomRequest;
use App\Inventory;
use App\InventoryBag;
use App\Jobs\StoreClientsAssociations;
use App\Language;
use App\LogNotification;
use App\Market;
use App\Meal;
use App\PackageServiceRoom;
use App\PoliciesRates;
use App\RatePlanAssociation;
use App\Rates;
use App\RatesHistory;
use App\RatesPlans;
use App\RatesPlansCalendarys;
use App\RatesPlansPromotions;
use App\RatesPlansRooms;
use App\RatesPlansTypes;
use App\Room;
use App\Http\Traits\JobStatusRegister;
use App\Http\Traits\Package;
use App\Http\Traits\Translations;
use App\RatePlanRoomDateRange;
use App\Translation;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use App\Mail\NotificationRateNotesMarketing;

class RatesCostsController extends Controller
{
    use Translations, Package, JobStatusRegister;


    public function __construct()
    {
        $this->middleware('permission:ratescosts.read')->only('index');
        $this->middleware('permission:ratescosts.create')->only('store');
        $this->middleware('permission:ratescosts.update')->only('update');
        $this->middleware('permission:ratescosts.delete')->only('delete');

    }

    /**
     * Display a listing of the resource.
     *
     * @param $hotelID
     * @param Request $request
     * @return JsonResponse
     */
    public function index($hotelID, Request $request)
    {
        $lang = $request->input('lang');
        $status = $request->input('status');

        $rates = RatesPlans::select(
            'id',
            'code',
            'name',
            'meal_id',
            'hotel_id',
            'rates_plans_type_id',
            'charge_type_id',
            'status',
            'promotions',
            'flag_process_markup',
            'channel_id',
            'type_channel'
        )
            ->with([
                'translations' => function ($query) {
                    $query->where('slug', '=', 'commercial_name');
                },
                'meal.translations' => function ($query) use ($lang) {
                    $query->where('type', 'meal');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])
            ->with(['ratesPlanType', 'chargeType', 'promotionsData','channel'])
            ->where('hotel_id', $hotelID);
        if ($status != '') {
            $rates = $rates->where('status', 1);
        }
        $rates = $rates->get()->toArray();

        return Response::json(['success' => true, 'data' => $rates]);
    }

    /**
     * Store a advance_salesnewly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store($hotelID, Request $request)
    {
        $ratesPlan = new RatesPlans();
        $ratesPlan->code = $request->input('code');
        $ratesPlan->name = $request->input('name');
        $ratesPlan->allotment = $request->input('allotment');
        $ratesPlan->taxes = $request->input('taxes');
        $ratesPlan->services = $request->input('services');
        $ratesPlan->timeshares = $request->input('timeshares');
        $ratesPlan->promotions = $request->input('promotions');
        $ratesPlan->flag_process_markup = $request->input('flag_process_markup');
        $ratesPlan->status = true;
        $ratesPlan->meal_id = (int)$request->input('meal')['id'];
        $ratesPlan->price_dynamic = ($request->input('type') == 3 && $request->input('channel_id') == 1) ? 1 : 0;
        $ratesPlan->rates_plans_type_id = $request->input('type');
        $ratesPlan->channel_id = $request->input('channel_id');
        $ratesPlan->charge_type_id = 1;
        $ratesPlan->hotel_id = $hotelID;
        $ratesPlan->type_channel = $request->input('type_channel') ?? null;
        $ratesPlan->save();


        $this->saveTranslation($request->input("translations"), 'rates_plan', $ratesPlan->id);

        $this->saveTranslation($request->input("translations_no_show"), 'rates_plan', $ratesPlan->id);

        $this->saveTranslation($request->input("translations_day_use"), 'rates_plan', $ratesPlan->id);

        $this->saveTranslation($request->input("translations_notes"), 'rates_plan', $ratesPlan->id);

        if ($request->input('promotions')) {
            DB::transaction(function () use ($request, $ratesPlan) {
                foreach ($request->input('promotionsData') as $promotion) {
                    $ratesPlansPromotion = new RatesPlansPromotions();
                    $ratesPlansPromotion->rates_plans_id = $ratesPlan->id;
                    $ratesPlansPromotion->promotion_from = Carbon::createFromFormat('d/m/Y', $promotion['from'])
                        ->toDateString();
                    $ratesPlansPromotion->promotion_to = Carbon::createFromFormat('d/m/Y', $promotion['to'])
                        ->toDateString();
                    $ratesPlansPromotion->save();
                }
            });
        }

        return Response::json(['success' => true, 'rate_plan' => $ratesPlan->id]);
    }

    public function storeRooms(Request $request, $hotelID, $ratesPlanID, $fromUpdate = false)
    {
        foreach ($request->input('rooms') as $room) {
            DB::transaction(function () use ($room, $fromUpdate, $ratesPlanID) {
                if ($room['adult'] > 0) {
                    $ratesPlansRoom = RatesPlansRooms::firstOrCreate([
                        'rates_plans_id' => $ratesPlanID,
                        'room_id' => $room['id'],
                        'status' => true,
                        'channel_id' => 1
                    ]);

                    $ratesPlansRoomID = $ratesPlansRoom->id;

                    $availableDays = explode(
                        "|",
                        PoliciesRates::select('days_apply')->where('id', $room['policy_id'])->first()->days_apply
                    );

                    $dateFrom = Carbon::createFromFormat('d/m/Y', $room['dates_from']);
                    $dateTo = Carbon::createFromFormat('d/m/Y', $room['dates_to']);

                    $currentDate = $dateFrom->copy();

                    do {
                        if ($availableDays[0] === 'all' || in_array($currentDate->dayOfWeekIso, $availableDays)) {
                            $ratesPlanCalendarys = RatesPlansCalendarys::updateOrCreate([
                                'date' => $currentDate->toDateString(),
                                'rates_plans_room_id' => $ratesPlansRoomID,
                            ], [
                                'policies_rate_id' => $room['policy_id'],
                                'status' => true
                            ]);

                            Rates::updateOrCreate([
                                'rates_plans_calendarys_id' => $ratesPlanCalendarys->id
                            ], [
                                'num_adult' => 0,
                                'num_child' => 0,
                                'num_infant' => 0,
                                'price_adult' => $room['adult'],
                                'price_child' => $room['child'],
                                'price_infant' => $room['infant'],
                                'price_extra' => $room['extra'],
                                'price_total' => 0
                            ]);
                        }

                        $currentDate = $currentDate->addDay();
                    } while ($currentDate->lessThanOrEqualTo($dateTo));
                }
            });
        }

        $ratesHistory = RatesHistory::where('rates_plan_id', $ratesPlanID)
            ->orderBy('created_at', 'desc')
            ->first();

        $data = json_decode($request->getContent(), true);

        $roomsData = Room::where('hotel_id', $hotelID)->with('translations')->get()->keyBy('id')->toArray();

        foreach ($data['rooms'] as $keyTmp => $itemTmp) {
            $data['rooms'][$keyTmp]['translations'] = $roomsData[$itemTmp['id']]['translations'];
        }

        if ($request->input('continue')) {
            $dataRooms = json_decode($ratesHistory->dataRooms, true);

            foreach ($data['rooms'] as $room) {
                $dataRooms[] = $room;
            }
            $ratesHistory->dataRooms = json_encode($dataRooms);
        } else {
            $ratesHistory->dataRooms = json_encode($data['rooms']);
        }

        $ratesHistory->save();

        return Response::json(['success' => true]);
    }

    /**
     * Display the specified resource.
     *
     * @param $hotelID
     * @param $rateID
     * @param Request $request
     * @return JsonResponse
     */
    public function show($hotelID, $rateID, Request $request)
    {
        $lang = $request->input('lang');

        $ratesPlans = RatesPlans::with([
            'translations',
            'translations_no_show',
            'translations_day_use',
            'translations_notes',
            'meal',
            'promotionsData',
            'associations',
        ])
            ->with([
                'meal.translations' => function ($query) use ($lang) {
                    $query->where('type', 'meal');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])
            ->where('id', $rateID)->first();

        $result = [
            'name' => $ratesPlans->name,
            'translations' => [],
            'code' => $ratesPlans->code,
            'meal' => [
                'id' => $ratesPlans->meal->id,
                'text' => $ratesPlans->meal->translations[0]->value
            ],
            'type' => $ratesPlans->rates_plans_type_id,
            'rate' => $ratesPlans->rate,
            'allotment' => $ratesPlans->allotment,
            'price_dynamic' => $ratesPlans->price_dynamic,
            'taxes' => $ratesPlans->taxes,
            'services' => $ratesPlans->services,
            'timeshares' => $ratesPlans->timeshares,
            'promotions' => $ratesPlans->promotions,
            'channel_id' => $ratesPlans->channel_id,
            'flag_process_markup' => (($ratesPlans->flag_process_markup == 1) ? true : false),
            'promotionsData' => [],
            'translations_no_show' => [],
            'translations_day_use' => [],
            'translations_notes' => [],
            'type_channel' => $ratesPlans->type_channel
        ];

        foreach ($ratesPlans['translations'] as $translation) {
            $result['translations'][$translation->language_id] = [
                'id' => $translation->id,
                'commercial_name' => $translation->value
            ];
        }
        foreach ($ratesPlans['translations_no_show'] as $translation) {
            $result['translations_no_show'][$translation->language_id] = [
                'id' => $translation->id,
                'no_show' => $translation->value
            ];
        }
        foreach ($ratesPlans['translations_day_use'] as $translation) {
            $result['translations_day_use'][$translation->language_id] = [
                'id' => $translation->id,
                'day_use' => $translation->value
            ];
        }
        foreach ($ratesPlans['translations_notes'] as $translation) {
            $result['translations_notes'][$translation->language_id] = [
                'id' => $translation->id,
                'notes' => $translation->value
            ];
        }

        foreach ($ratesPlans['promotionsData'] as $promotion) {
            $result['promotionsData'][] = [
                'promotion_from' => $promotion->promotion_from,
                'promotion_to' => $promotion->promotion_to
            ];
        }

        return Response::json(['success' => true, 'data' => $result]);
    }

    public function update($hotelID, $rateID, Request $request)
    {
        $send_to_mkt =  $request->input('send_to_mkt');
        $type = $request->input('type', 0); // Valor por defecto 0
        $channelId = $request->input('channel_id', 0); // Valor por defecto 0

        $ratesPlan = RatesPlans::find($rateID);
        $ratesPlan->code = $request->input('code');
        $ratesPlan->name = $request->input('name');
        $ratesPlan->allotment = $request->input('allotment');
        $ratesPlan->rate = $request->input('rate');
        $ratesPlan->taxes = $request->input('taxes');
        $ratesPlan->services = $request->input('services');
        $ratesPlan->timeshares = $request->input('timeshares');
        $ratesPlan->promotions = $request->input('promotions');
        $ratesPlan->flag_process_markup = $request->input('flag_process_markup');
        $ratesPlan->meal_id = (int)($request->input('meal')['id'] ?? 0);
        $ratesPlan->price_dynamic = ($type == 3 && $channelId == 1) ? 1 : 0;
        $ratesPlan->rates_plans_type_id = $request->input('type');
        $ratesPlan->channel_id = $request->input('channel_id');
        $ratesPlan->charge_type_id = 1;
        $ratesPlan->type_channel = $request->input('type_channel') ?? null;
        $ratesPlan->save();


        $this->saveTranslation($request->input("translations"), 'rates_plan', $ratesPlan->id);

        $this->saveTranslation($request->input("translations_no_show"), 'rates_plan', $ratesPlan->id);

        $this->saveTranslation($request->input("translations_notes"), 'rates_plan', $ratesPlan->id);

        $this->saveTranslation($request->input("translations_day_use"), 'rates_plan', $ratesPlan->id);

        RatesPlansPromotions::where('rates_plans_id', $ratesPlan->id)->delete();

        if ($request->input('promotions')) {
            DB::transaction(function () use ($request, $ratesPlan) {
                foreach ($request->input('promotionsData') as $promotion) {
                    $ratesPlansPromotion = new RatesPlansPromotions();
                    $ratesPlansPromotion->rates_plans_id = $ratesPlan->id;
                    $ratesPlansPromotion->promotion_from = Carbon::createFromFormat('d/m/Y', $promotion['from'])
                        ->toDateString();
                    $ratesPlansPromotion->promotion_to = Carbon::createFromFormat('d/m/Y', $promotion['to'])
                        ->toDateString();
                    $ratesPlansPromotion->save();
                }
            });
        }

        if($send_to_mkt){
            $who = Auth::user()->name;
            $hotel_ = Hotel::find($ratesPlan->hotel_id);
            $hotel_id = $hotel_->id;
            $hotel_name = $hotel_->name;
            $rate_id = $ratesPlan->id;
            $rate_name = $ratesPlan->name;
            $this->send_notification_notes_mkt($who, $hotel_name, $rate_name, $hotel_id, $rate_id);
        }

        return Response::json([
            'success' => true,
            'rate_plan' => $ratesPlan->id,
        ]);
    }

    public function ratePlanHistory($hotel_id, $rate_plan_id, Request $request)
    {
        $ratesHistory = RatesHistory::select('id', 'dataRooms', 'created_at')
            ->where('rates_plan_id', $rate_plan_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $rooms = [];
        foreach ($ratesHistory as $x => $item) {
            $dataRooms = json_decode($item['dataRooms'], true);
            $ids = [];
            $tmpRooms = [];

            if ($dataRooms && array_key_exists('promotions', $dataRooms) === false) {
                foreach ($dataRooms as $room) {
                    array_push($ids, $room['id']);
                }

                $translations = Translation::where('slug', 'room_name')
                    ->where('type', 'room')
                    ->whereIn('object_id', $ids)
                    ->get()
                    ->groupBy('object_id');

                $policies = PoliciesRates::select('id', 'name')
                    ->where('hotel_id', $hotel_id)->orWhereNull('hotel_id')
                    ->get()
                    ->keyBy('id')
                    ->toArray();

                foreach ($dataRooms as $room) {

                    if (array_key_exists($room['group'], $tmpRooms)) {
                        array_push(
                            $tmpRooms[$room['group']]['rooms'],
                            [
                                "id" => $room['id'],
                                "room_id" => $room['id'],
                                "adult" => $room['adult'],
                                "child" => $room['child'],
                                "extra" => $room['extra'],
                                "infant" => $room['infant'],
                                "policy_name" => $policies[$room['policy_id']]['name'],
                                "name" => $translations[$room['id']][0]['value'],
                                "translations" => $translations[$room['id']]
                            ]
                        );
                    } else {
                        $tmpRooms[$room['group']] = [
                            'dates_from' => $room['dates_from'],
                            'dates_to' => $room['dates_to'],
                            'policy_id' => $room['policy_id'],
                            'rooms' => [
                                [
                                    "id" => $room['id'],
                                    "room_id" => $room['id'],
                                    "adult" => $room['adult'],
                                    "child" => $room['child'],
                                    "extra" => $room['extra'],
                                    "infant" => $room['infant'],
                                    "policy_name" => $policies[$room['policy_id']]['name'],
                                    "name" => $translations[$room['id']][0]['value'],
                                    "translations" => $translations[$room['id']]
                                ]
                            ]
                        ];
                    }

                }
            }

            $rooms[] = [
                'id' => $item->id,
                'date' => $item->created_at,
                'rooms' => array_values($tmpRooms)
            ];
        }

        return Response::json([
            'success' => true,
            'rate_plan' => $rate_plan_id,
            'history' => $rooms
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $hotelID
     * @param $rateID
     * @return JsonResponse
     */
    public function updateRooms(Request $request, $hotelID, $rateID)
    {

//        if ($request->input('del')) {
//            DB::transaction(function () use ($rateID) {
//                $ratesPlansRoomsIDs = RatesPlansRooms::where('rates_plans_id', $rateID)
//                    ->get()
//                    ->pluck('id');
//                $ratesPlansCalendars = RatesPlansCalendarys::whereIn('rates_plans_room_id', $ratesPlansRoomsIDs)
//                    ->get();
//                foreach ($ratesPlansCalendars as $ratesPlansCalendar) {
//                    Rates::where('rates_plans_calendarys_id', $ratesPlansCalendar["id"])->delete();
//
//                    RatesPlansCalendarys::where('id', $ratesPlansCalendar["id"])->delete();
//                }
//            });
//        }
//
//        $this->storeRooms($request, $hotelID, $rateID, true);

        return Response::json([
            'success' => true
        ]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $hotelID
     * @param $rateID
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy($hotelID, $rateID, Request $request)
    {

        $uses = $this->get_rate_plan_uses($rateID);

        if (count($uses) > 0) {
            return Response::json(['success' => false, 'uses' => $uses]);
        }

        try {
            DB::transaction(function () use ($rateID) {

                $ratesPlansRooms = RatesPlansRooms::select('id')
                    ->where('rates_plans_id', $rateID)
                    ->get()
                    ->pluck('id')->toArray();

                $ratesPlansCalendarys = RatesPlansCalendarys::select('id')
                    ->whereIn('rates_plans_room_id', $ratesPlansRooms)
                    ->get()
                    ->pluck('id')
                    ->toArray();

                $bag_room_ids = BagRate::whereIn('rate_plan_rooms_id', $ratesPlansRooms)->pluck('bag_room_id');

                BagRate::whereIn('rate_plan_rooms_id', $ratesPlansRooms)->delete();

                BagRoom::whereIn('id', $bag_room_ids)->delete();

                InventoryBag::whereIn('bag_room_id', $bag_room_ids)->delete();

                Rates::whereIn('rates_plans_calendarys_id', $ratesPlansCalendarys)->delete();

                RatesPlansCalendarys::whereIn('id', $ratesPlansCalendarys)->delete();

                Inventory::whereIn('rate_plan_rooms_id', $ratesPlansRooms)->delete();

                PackageServiceRoom::whereIn('rate_plan_room_id', $ratesPlansRooms)->delete();

                RatesPlansRooms::whereIn('id', $ratesPlansRooms)->delete();

                Translation::where('type', 'rates_plan')->where('object_id', $rateID)->delete();

                RatesPlans::where('id', $rateID)->delete();
            });

            return Response::json([
                'success' => true
            ]);

        } catch (Exception $exception) {
            return Response::json([
                'success' => false,
                'code' => 'error',
                'message' => $exception->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $hotelID
     * @param $rateID
     * @param Request $request
     * @return JsonResponse
     */
    public function clonarTarifa($hotelID, $rateID, Request $request)
    {

        DB::transaction(function () use ($request, $hotelID, $rateID) {

            $created_at = date('Y-m-d H:i:s');
            $updated_at = date('Y-m-d H:i:s');
            $columns_rates_plans = Schema::getColumnListing('rates_plans');
            $columns_rates_histories = Schema::getColumnListing('rates_histories');
            $columns_rates_promotions = Schema::getColumnListing('rates_plans_promotions');
            $columns_rates_plans_rooms = Schema::getColumnListing('rates_plans_rooms');
            $columns_translations = Schema::getColumnListing('translations');

            $columns_rates_plans_calendarys = Schema::getColumnListing('rates_plans_calendarys');
            $columns_rates = Schema::getColumnListing('rates');


            $rate_plan = RatesPlans::find($rateID);
            $data_rate_plans = $this->crearExtructura($columns_rates_plans, $rate_plan, $created_at, $updated_at);
            $data_rate_plans['name'] = $data_rate_plans['name'] . ' clonada';
            $rates_plans_id = DB::table('rates_plans')->insertGetId($data_rate_plans);


            $ratesPlansPromotions = RatesPlansPromotions::where('rates_plans_id', $rateID)->get();
            foreach ($ratesPlansPromotions as $promotions) {
                $data_rate_plan_promotions = $this->crearExtructura($columns_rates_promotions, $promotions, $created_at,
                    $updated_at);
                $data_rate_plan_promotions['rates_plans_id'] = $rates_plans_id;
                $rates_plans_promotions_id = DB::table('rates_plans_promotions')->insertGetId($data_rate_plan_promotions);
            }

            $ratesPlansHistory = RatesHistory::where('rates_plan_id', $rateID)->get();
            foreach ($ratesPlansHistory as $history) {
                $data_rate_plan_history = $this->crearExtructura($columns_rates_histories, $history, $created_at,
                    $updated_at);
                $data_rate_plan_history['rates_plan_id'] = $rates_plans_id;
                $rates_plans_history_id = DB::table('rates_histories')->insertGetId($data_rate_plan_history);
            }

            $rates_plan_translations = Translation::where('type', 'rates_plan')->where('object_id', $rateID)->get();
            foreach ($rates_plan_translations as $translation) {
                $data_rate_plan_translations = $this->crearExtructura($columns_translations, $translation, $created_at,
                    $updated_at);
                $data_rate_plan_translations['object_id'] = $rates_plans_id;
                $rates_plans_translations_id = DB::table('translations')->insertGetId($data_rate_plan_translations);
            }

            $ratesPlansRooms = RatesPlansRooms::where('rates_plans_id', $rateID)->get();
            foreach ($ratesPlansRooms as $raterooms) {
                $data_rate_plan_rooms = $this->crearExtructura($columns_rates_plans_rooms, $raterooms, $created_at,
                    $updated_at);
                $data_rate_plan_rooms['rates_plans_id'] = $rates_plans_id;
                $rates_plans_rooms_id = DB::table('rates_plans_rooms')->insertGetId($data_rate_plan_rooms);

                $ratesPlansRoomsCalendary = RatesPlansCalendarys::where('rates_plans_room_id', $raterooms->id)->get();
                foreach ($ratesPlansRoomsCalendary as $calendary) {
                    $data_rate_plan_calendary = $this->crearExtructura($columns_rates_plans_calendarys, $calendary,
                        $created_at, $updated_at);
                    $data_rate_plan_calendary['rates_plans_room_id'] = $rates_plans_rooms_id;
                    $rates_plans_calendary_id = DB::table('rates_plans_calendarys')->insertGetId($data_rate_plan_calendary);

                    $rates = Rates::where('rates_plans_calendarys_id', $calendary->id)->get();
                    foreach ($rates as $rate) {
                        $data_rate = $this->crearExtructura($columns_rates, $rate, $created_at, $updated_at);
                        $data_rate['rates_plans_calendarys_id'] = $rates_plans_calendary_id;
                        $rates_rate_id = DB::table('rates')->insertGetId($data_rate);
                    }
                }

            }


//            echo "<pre>";
//            print_r($data_rate_plam);
//            echo "</pre>";


        });


        return Response::json([
            'success' => true,
            'msn' => 'se clono bien'
        ]);


    }

    public function crearExtructura($columnas, $data, $created_at, $updated_at)
    {

        $extructura = [];
        foreach ($columnas as $column) {
            $extructura[$column] = $data->$column;
        }
        $extructura['created_at'] = $created_at;
        $extructura['updated_at'] = $updated_at;
        unset($extructura['id']);
        return $extructura;
    }


    /**
     * @param $hotelID
     * @param Request $request
     * @return JsonResponse
     */
    public function calendar($hotelID, Request $request)
    {
        $lang = $request->input('lang');
        $data = explode('-', $request->input('date'));

        $from = Carbon::createFromDate((int)$data[1], (int)$data[0], 1);
        $to = $from->copy()->endOfMonth();


        $hotel_impuesto = HotelTax::with('tax')->where('hotel_id', $hotelID)->where('status', '1')->get();

        $hotel = Hotel::with('country')->where('hotel_id', $hotelID);


        $ratesPlans = RatesPlans::select('id')
            ->where('hotel_id', $hotelID)->get();

        $tmpCalendar = RatesPlansCalendarys::with([
            'ratesPlansRooms',
            'ratesPlansRooms.rate_plan',
            'ratesPlansRooms.rate_plan.meal',
            'ratesPlansRooms.room',
            'rate',
            'policiesRates'
        ])
            ->with([
                'ratesPlansRooms.room.translations' => function ($query) use ($lang, $data) {
                    $query->where('type', 'room');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])
            ->with([
                'ratesPlansRooms.rate_plan.meal.translations' => function ($query) use ($lang) {
                    $query->where('type', 'meal');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->whereHas('ratesPlansRooms.rate_plan', function ($query) use ($hotelID) {
                $query->where('hotel_id', $hotelID);
            })
            ->whereHas('ratesPlansRooms', function ($query) use ($ratesPlans, $data, $request) {
                $query->whereIn('rates_plans_id', $ratesPlans);

                if ($request->input('room')) {
                    $query->where('room_id', $request->input('room'));
                }

                if ($request->input('rate')) {
                    $query->where('rates_plans_id', $request->input('rate'));
                }
            })
            ->get()
            ->toArray();

        $calendar = [];

        $channels = Channel::all()->keyBy('id')->toArray();

        foreach ($tmpCalendar as $tmpKey => $tmpCal) {
            $ratesPlansRoom = $tmpCal['rates_plans_rooms'];
            $room = $ratesPlansRoom['room'];

            if ($request->input('room')) {
                if ($room['id'] !== (int)$request->input('room')) {
                    continue;
                }
            }

            if ($request->input('rate')) {
                if ($ratesPlansRoom['rate_plan']['id'] !== (int)$request->input('rate')) {
                    continue;
                }
            }

            if ($request->input('channel')) {
                if ($channels[$ratesPlansRoom['channel_id']]['id'] !== (int)$request->input('channel')) {
                    continue;
                }
            }

            $tmpItem = [
                'calendar' => [
                    'id' => $tmpCal['id']
                ],
                'ratePlan' => [
                    'id' => $ratesPlansRoom['rate_plan']['id'],
                    'name' => $ratesPlansRoom['rate_plan']['name'],
                    'taxes' => $ratesPlansRoom['rate_plan']['taxes'],
                    'services' => $ratesPlansRoom['rate_plan']['services'],
                ],
                'room' => [
                    'id' => $room['id'],
                    'name' => $room['translations'][0]['value'],
                    'value' => 0
                ],
                'meal' => [
                    'id' => $ratesPlansRoom['rate_plan']['meal']['id'],
                    'name' => $ratesPlansRoom['rate_plan']['meal']['translations'][0]['value'],
                ],
                'channel' => [
                    'id' => $channels[$ratesPlansRoom['channel_id']]['id'],
                    'name' => $channels[$ratesPlansRoom['channel_id']]['name'],
                ],
                'rates' => []
            ];

            if ($tmpCal['policies_rates'] === null) {
                $tmpItem['policy'] = [
                    'name' => '',
                    'max_ab_offset' => $tmpCal['max_ab_offset'],
                    'min_ab_offset' => $tmpCal['min_ab_offset'],
                    'min_length_stay' => $tmpCal['min_length_stay'],
                    'max_length_stay' => $tmpCal['max_length_stay'],
                    'max_occupancy' => $tmpCal['max_occupancy'],
                ];
            } else {
                $tmpItem['policy'] = [
                    'id' => $tmpCal['policies_rates']['id'],
                    'name' => $tmpCal['policies_rates']['name'],
                    'max_ab_offset' => $tmpCal['policies_rates']['max_ab_offset'],
                    'min_ab_offset' => $tmpCal['policies_rates']['min_ab_offset'],
                    'min_length_stay' => $tmpCal['policies_rates']['min_length_stay'],
                    'max_length_stay' => $tmpCal['policies_rates']['max_length_stay'],
                    'max_occupancy' => $tmpCal['policies_rates']['max_occupancy'],
                ];
            }

            $rateValue = 9999999999;
            $winValue = 0;

//            echo "<pre>";
//            print_r($tmpCal['rate']);
//            echo "</pre>";
//            die('..');

            foreach ($tmpCal['rate'] as $rates) {

                $rates['price_adult'] = $this->calculoImpuestoServicios($hotel_impuesto, $ratesPlansRoom['rate_plan'],
                    $rates['price_adult']);
                $rates['price_child'] = $this->calculoImpuestoServicios($hotel_impuesto, $ratesPlansRoom['rate_plan'],
                    $rates['price_child']);
                $rates['price_infant'] = $this->calculoImpuestoServicios($hotel_impuesto, $ratesPlansRoom['rate_plan'],
                    $rates['price_infant']);
                $rates['price_extra'] = $this->calculoImpuestoServicios($hotel_impuesto, $ratesPlansRoom['rate_plan'],
                    $rates['price_extra']);
                $rates['price_total'] = $this->calculoImpuestoServicios($hotel_impuesto, $ratesPlansRoom['rate_plan'],
                    $rates['price_total']);

                if ((float)$rates['price_adult'] !== 0.0 && (float)$rates['price_adult'] < $rateValue) {
                    $rateValue = (float)$rates['price_adult'];
                    $winValue = number_format((float)$rates['price_adult'], 2);
                }

                if ((float)$rates['price_total'] !== 0.0 && (float)$rates['price_total'] < $rateValue) {
                    $rateValue = (float)$rates['price_total'];
                    $winValue = number_format((float)$rates['price_total'], 2);
                }

                if ((float)$rates['num_adult'] == 0.0 &&
                    (float)$rates['num_child'] == 0.0 &&
                    (float)$rates['num_infant'] == 0.0 &&
                    (float)$rates['price_adult'] == 0.0 &&
                    (float)$rates['price_child'] == 0.0 &&
                    (float)$rates['price_infant'] == 0.0 &&
                    (float)$rates['price_extra'] == 0.0 &&
                    (float)$rates['price_total'] > 0
                ) {
                    $tmpItem['rates'][] = [
                        'id' => $rates['id'],
                        'type' => 'By Room',
                        'category' => '',
                        'amount' => '',
                        'price' => $rates['price_total']
                    ];
                }
                if ((float)$rates['num_adult'] > 0 &&
                    (float)$rates['num_child'] == 0 &&
                    (float)$rates['num_infant'] == 0 &&
                    (float)$rates['price_adult'] > 0 &&
                    (float)$rates['price_child'] == 0 &&
                    (float)$rates['price_infant'] == 0 &&
                    (float)$rates['price_extra'] == 0 &&
                    (float)$rates['price_total'] == 0
                ) {
                    $tmpItem['rates'][] = [
                        'id' => $rates['id'],
                        'type' => 'By Person',
                        'category' => 'Adult',
                        'amount' => $rates['num_adult'],
                        'price' => $rates['price_adult']
                    ];
                }
                if ((float)$rates['num_adult'] == 0 &&
                    (float)$rates['num_child'] > 0 &&
                    (float)$rates['num_infant'] == 0 &&
                    (float)$rates['price_adult'] == 0 &&
                    (float)$rates['price_child'] > 0 &&
                    (float)$rates['price_infant'] == 0 &&
                    (float)$rates['price_extra'] == 0 &&
                    (float)$rates['price_total'] == 0
                ) {
                    $tmpItem['rates'][] = [
                        'id' => $rates['id'],
                        'type' => 'By Person',
                        'category' => 'Child',
                        'amount' => $rates['num_child'],
                        'price' => $rates['price_child']
                    ];
                }
                if ((float)$rates['num_adult'] == 0 &&
                    (float)$rates['num_child'] == 0 &&
                    (float)$rates['num_infant'] > 0 &&
                    (float)$rates['price_adult'] == 0 &&
                    (float)$rates['price_child'] == 0 &&
                    (float)$rates['price_infant'] > 0 &&
                    (float)$rates['price_extra'] == 0 &&
                    (float)$rates['price_total'] == 0
                ) {
                    $tmpItem['rates'][] = [
                        'id' => $rates['id'],
                        'type' => 'By Person',
                        'category' => 'Infant',
                        'amount' => $rates['num_infant'],
                        'price' => $rates['price_infant']
                    ];
                }
                if ((float)$rates['num_adult'] == 0 &&
                    (float)$rates['num_child'] == 0 &&
                    (float)$rates['num_infant'] == 0 &&
                    (float)$rates['price_adult'] > 0 &&
                    (float)$rates['price_total'] == 0
                ) {
                    $tmpItem['rates'][] = [
                        'id' => $rates['id'],
                        'type' => 'By Price',
                        'category' => 'Adult',
                        'amount' => '',
                        'price' => $rates['price_adult']
                    ];
                }
                if ((float)$rates['num_adult'] == 0 &&
                    (float)$rates['num_child'] == 0 &&
                    (float)$rates['num_infant'] == 0 &&
                    (float)$rates['price_child'] > 0 &&
                    (float)$rates['price_total'] == 0
                ) {
                    $tmpItem['rates'][] = [
                        'id' => $rates['id'],
                        'type' => 'By Price',
                        'category' => 'Child',
                        'amount' => '',
                        'price' => $rates['price_child']
                    ];
                }
                if ((float)$rates['num_adult'] == 0 &&
                    (float)$rates['num_child'] == 0 &&
                    (float)$rates['num_infant'] == 0 &&
                    (float)$rates['price_infant'] > 0 &&
                    (float)$rates['price_total'] == 0
                ) {
                    $tmpItem['rates'][] = [
                        'id' => $rates['id'],
                        'type' => 'By Price',
                        'category' => 'Infant',
                        'amount' => '',
                        'price' => $rates['price_infant']
                    ];
                }
                if ((float)$rates['num_adult'] == 0 &&
                    (float)$rates['num_child'] == 0 &&
                    (float)$rates['num_infant'] == 0 &&
                    (float)$rates['price_extra'] > 0 &&
                    (float)$rates['price_total'] == 0
                ) {
                    $tmpItem['rates'][] = [
                        'id' => $rates['id'],
                        'type' => 'By Price',
                        'category' => 'Extra',
                        'amount' => '',
                        'price' => $rates['price_extra']
                    ];
                }
                if ((
                        (float)$rates['num_adult'] > 0 ||
                        (float)$rates['num_child'] > 0 ||
                        (float)$rates['num_infant'] > 0
                    ) &&
                    (float)$rates['price_adult'] == 0 &&
                    (float)$rates['price_child'] == 0 &&
                    (float)$rates['price_infant'] == 0 &&
                    (float)$rates['price_extra'] == 0 &&
                    (float)$rates['price_total'] > 0
                ) {
                    $tmpItem['rates'][] = [
                        'id' => $rates['id'],
                        'type' => 'By Occupancy',
                        'category' => '',
                        'amount' => 'A:' . $rates['num_adult'] . ' / C: ' . $rates['price_child'] . ' / I: ' . $rates['price_infant'],
                        'price' => $rates['price_total']
                    ];
                }
            }

            $tmpItem['room']['value'] = $winValue;

            $tmpItemCollection = collect($tmpItem['rates']);
            $tmpItemArray = $tmpItemCollection->sortBy('type')->toArray();
            $tmpItem['rates'] = $tmpItemArray;

//            if ((float)$winValue > 0) {
            if (array_key_exists($tmpCal['date'], $calendar)) {
                $calendar[$tmpCal['date']][] = $tmpItem;
            } else {
                $calendar[$tmpCal['date']] = [$tmpItem];
            }
//            }
        }

        return Response::json(['success' => true, 'data' => $calendar]);
    }

    public function calculoImpuestoServicios($impuestosServicios, $rate_plan, $importe)
    {

        $impuestoCalculados = 0;

        foreach ($impuestosServicios as $impuesto) {

            if ($rate_plan['taxes'] == 1) {
                if ($impuesto->tax->type == "t") {
                    $impuestoCalculados = $impuestoCalculados + (($impuesto->amount / 100) * $importe);
                }
            }

            if ($rate_plan['services'] == 1) {
                if ($impuesto->tax->type == "s") {
                    $impuestoCalculados = $impuestoCalculados + (($impuesto->amount / 100) * $importe);
                }
            }
        }

        $importeTotal = $importe + $impuestoCalculados;

        return $importeTotal;

    }

    public function calendarUpdate(Request $request)
    {
        $tmpData = [];
        foreach ($request->input('rates') as $rate) {
            $tmpData[strtolower($rate['category'])] = $rate['price'];
        }

        $rates = Rates::find($request->input('rates')[0]['id']);
        if (array_key_exists('adult', $tmpData)) {
            $rates->price_adult = $tmpData['adult'];
        }
        if (array_key_exists('child', $tmpData)) {
            $rates->price_child = $tmpData['child'];
        }
        if (array_key_exists('infant', $tmpData)) {
            $rates->price_infant = $tmpData['infant'];
        }
        if (array_key_exists('extra', $tmpData)) {
            $rates->price_extra = $tmpData['extra'];
        }
        $rates->save();

        return Response::json(['success' => true]);
    }


    public function calendarDelete($hotelID, $calendarID, Request $request)
    {
        Rates::where('rates_plans_calendarys_id', $calendarID)->delete();
        RatesPlansCalendarys::find($calendarID)->delete();

        return Response::json(['success' => true]);
    }

    public function calendarDeleteAll($hotelID, Request $request)
    {
        $data = explode('|', $request->input('date'));

        $from = Carbon::createFromDate((int)$data[1], (int)$data[0], 1);
        $to = $from->copy()->endOfMonth();

        $ratesPlans = RatesPlans::select('id')->where('hotel_id', $hotelID)->get();

        $calendar = RatesPlansCalendarys::select('id')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()]);

        $calendar->whereHas('ratesPlansRooms', function ($query) use ($ratesPlans, $data) {
            $query->whereIn('rates_plans_id', $ratesPlans);

            if ($data[2]) {
                $query->where('room_id', $data[2]);
            }

            if ($data[3]) {
                $query->where('rates_plans_id', $data[3]);
            }
        });

        $calendar = $calendar->get()->pluck('id');

        Rates::whereIn('rates_plans_calendarys_id', $calendar)->delete();
        RatesPlansCalendarys::whereIn('id', $calendar)->delete();

        return Response::json(['success' => true]);
    }

    public function selectBox(Request $request)
    {
        $hotelID = $request->input('hotel_id');

        $rooms = RatesPlans::select('id as value', 'name as text')
            ->where('hotel_id', $hotelID)->get();

        return Response::json(['success' => true, 'data' => $rooms]);
    }

    public function getChannels($hotelID, $rateID, Request $request)
    {
        $lang = $request->input('lang');
        $channelId = $request->input('channel');
        $year = $request->input('year');

        $language_id = Language::where('iso', $lang)->first()->id;

        $rooms = Room::with([
            'translations' => function ($query) use ($language_id) {
                $query->where('type', 'room');
                $query->where('language_id', $language_id);
            }
        ])
            ->whereHas('channels', function ($query) use ($channelId) {
                $query->where('channels.id', '=', $channelId);
                $query->whereNotNull('channel_room.code');
            })
            ->where('rooms.hotel_id', (int)$hotelID)
            ->get()
            ->toArray();

        $ratesPlansRooms = RatesPlansRooms::select(
            'room_id',
            'status',
            'channel_child_price',
            'channel_infant_price'
        )
            ->where('rates_plans_id', $rateID)
            ->where('channel_id', $request->input('channel'))
            ->get()
            ->keyBy('room_id')
            ->toArray();

        $ratesPlansRoomsPoliceCancelation = RatesPlansRooms::select('id', 'room_id', 'status')->with([
            'policies_cancelation' => function ($query) {
                $query->select('policies_cancelations.id as code', 'policies_cancelations.name as label');
            }
        ])
            ->whereHas('policies_cancelation')
            ->where('rates_plans_id', $rateID)
            ->where('channel_id', $request->input('channel'))
            ->get()
            ->toArray();

        $result = [];

        foreach ($rooms as $room) {
            $tmp = [
                "id" => $room['id'],
                "name" => $room['translations'][0]['value'],
                'channel_child_price'  => 0.00,
                'channel_infant_price' => 0.00,
                "selected" => false
            ];

            foreach ($ratesPlansRooms as $rates) {
                if ($rates['room_id'] == $room['id']) {
                    // $tmp['channel_child_price'] = $rates['channel_child_price'];
                    // $tmp['channel_infant_price'] = $rates['channel_infant_price'];
                    $tmp['selected'] = $rates['status'] == "1";
                    break;
                }
            }

//            if (array_key_exists($room['id'], $ratesPlansRooms)) {
//                $tmp['selected'] = true;
//            }

            $result[] = $tmp;
        }

        $prices = RatePlanRoomDateRange::with(['rate_plan_room.room.translations'])->where(function ($query) use ($year) {
            $query->orWhereYear('date_from', $year);
            $query->orWhereYear('date_to', $year);
        })->whereHas('rate_plan_room', function ($query) use ($rateID) {
            $query->where('rates_plans_id', '=', $rateID);
        })->get()->toArray();

        return Response::json([
            'success' => true,
            'data' => $result,
            'prices' => $prices,
            'police' => $ratesPlansRoomsPoliceCancelation,
            'rate' => $ratesPlansRooms
        ]);
    }

    public function storeChannels(HotelRatePlanRoomRequest $request, Hotel $hotel_id, RatesPlans $rate_id): JsonResponse
    {
        $form = $request->validated();
        // TODO validar que el $rate_plan le pertenece al $hotel
        $hotel = $hotel_id;
        $rate_plan = $rate_id;
        // TODO validar que las $room le pertenecen al $hotel
        $rooms = collect($request->get('rooms', []));
        $channel_id = (int)$request->get('channel_id');

        // dates..
        $date_from = $request->get('date_from');
        if($date_from){
           $date_from = explode("/", $date_from); $date_from = $date_from[2] . '-' . $date_from[1] . '-' . $date_from[0];
        }

        $date_to = $request->get('date_to');
        if($date_to){
           $date_to = explode("/", $date_to); $date_to = $date_to[2] . '-' . $date_to[1] . '-' . $date_to[0];
        }

        $policiesCancellation = collect($request->input("policies_cancelation", []));
        foreach ($rooms as $room) {
            /** @var RatesPlansRooms $ratePlanRoom */
            $ratePlanRoom = RatesPlansRooms::query()->updateOrCreate([
                'rates_plans_id' => $rate_plan['id'],
                'room_id' => (int)$room['id'],
                'channel_id' => $channel_id,
            ], [
                'status' => $room['selected'],
            ]);

            if ($policiesCancellation->count()) {
                $ratePlanRoom->policies_cancelation()->sync($policiesCancellation->pluck('code')->toArray());
            }

            // $ratePlanRoom['channel_child_price'] = $room['channel_child_price'] ?? 0.00;
            // $ratePlanRoom['channel_infant_price'] = $room['channel_infant_price'] ?? 0.00;

            $ratePlanRoom->save();

            if(@$room['channel_child_price'] > 0 OR @$room['channel_infant_price'] > 0)
            {
                // Prices..
                if(isset($room['price_id']))
                {
                    $price = RatePlanRoomDateRange::find($room['price_id']);
                }
                else
                {
                    $group = RatePlanRoomDateRange::where('rate_plan_room_id', '=', $ratePlanRoom->id)->max('group');

                    $price = new RatePlanRoomDateRange();
                    $price->rate_plan_room_id = $ratePlanRoom->id;
                    $price->group = $group + 1;
                }

                $price->date_from = $date_from;
                $price->date_to = $date_to;
                $price->price_child = $room['channel_child_price'] ?? 0.00;
                $price->price_infant = $room['channel_infant_price'] ?? 0.00;
                $price->save();
            }
        }

        return Response::json(['success' => true]);
    }

    public function destroy_date_range(Request $request, $hotel_id, $rate_id, $price_id)
    {
        try
        {
            RatePlanRoomDateRange::find($price_id)->delete();
            return Response::json(['success' => true]);
        }
        catch(\Exception $ex)
        {
            return Response::json(false);
        }

    }

    public function history($hotelID, $rateID, Request $request)
    {
        $ratesPlan = RatesPlans::find($rateID);

        $ratesHistory = RatesHistory::where('rates_plan_id', $rateID)
            ->with('user')
            ->where('hotel_id', $hotelID)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        $result = [];
        $tmpDataRoom = [];

        $meals = Meal::select('id')
            ->with('translations')
            ->get()
            ->keyBy('id')
            ->toArray();

        $rooms = Room::select('id')
            ->with('translations')
            ->get()
            ->keyBy('id')
            ->toArray();

        foreach ($ratesHistory as $item) {
            $item['data'] = json_decode($item['data'], true);
            $item['data']['meal_name'] = $meals[$item['data']['meal_id']]['translations'][0]['value'];
            $item['data']['type_name'] = RatesPlansTypes::find($item['data']['type'])['name'];
            $item['dataRooms'] = json_decode($item['dataRooms'], true);
            $item['label'] = $item['created_at'] . ' - ' . $item['user']['name'];

            if ($item['dataRooms']) {
                foreach ($item['dataRooms'] as $dataRoom) {
                    $tmpDataRoom[] = [
                        'room_name' => $rooms[$dataRoom['id']]['translations'][0]['value'],
                        'policy_name' => PoliciesRates::find($dataRoom['policy_id'])->name,
                        'from' => $dataRoom['dates_from'],
                        'to' => $dataRoom['dates_to'],
                        'adult' => number_format((float)$dataRoom['adult'], 2),
                        'child' => number_format((float)$dataRoom['child'], 2),
                        'infant' => number_format((float)$dataRoom['infant'], 2),
                        'extra' => number_format((float)$dataRoom['extra'], 2),
                    ];
                }
            }

            $item['dataRooms'] = $tmpDataRoom;

            $result[] = $item;
        }

        return Response::json(['success' => true, 'data' => $result, 'name' => $ratesPlan->name]);
    }

    public function merge(Request $request, $hotelID, $ratesPlanID, $fromUpdate = false)
    {

        $ratesPlan = DB::transaction(function () use ($request, $hotelID) {
            if (!$request->input('id')) {
                $ratesPlan = new RatesPlans();
                if ($request->input('code')) {
                    $ratesPlan->code = $request->input('code');
                }
                $ratesPlan->name = $request->input('name');
                $ratesPlan->allotment = $request->input('allotment', 1);
                $ratesPlan->taxes = $request->input('taxes', false);
                $ratesPlan->services = $request->input('services', false);
                $ratesPlan->timeshares = $request->input('timeshares', 0);
                $ratesPlan->promotions = $request->input('promotions', false);
                $ratesPlan->status = true;
                $ratesPlan->meal_id = (int)$request->input('meal_id', 1);
                $ratesPlan->rates_plans_type_id = $request->input('rates_plans_type_id');
                $ratesPlan->charge_type_id = 1;
                $ratesPlan->hotel_id = $hotelID;
                $ratesPlan->save();

                if (!$request->input('code')) {
                    $ratesPlan->code = $ratesPlan->id;
                    $ratesPlan->save();
                }
            } else {
                $ratesPlan = RatesPlans::find($request->input('id'));
            }

            $languages = Language::all()->where('state', '=', 1);
            $translations = [];
            foreach ($languages as $item) {
                $translations[$item['id']] = [
                    'id' => '',
                    'commercial_name' => $request->input('name'),
                ];
            }

            $this->saveTranslation($translations, 'rates_plan', $ratesPlan->id);

            $rate_plan_id = $request->input('rate_plan_from');
            $rooms = $request->input('rooms');

            $ratesHistory = RatesHistory::where('rates_plan_id', $rate_plan_id)
                ->orderBy('created_at', 'desc')
                ->first();

            $dataRoomsNew = [];
            $datesInserBuRoom = [];
            $dataRoomsFrom = json_decode($ratesHistory->dataRooms, true);
            foreach ($rooms as $roomsSelected) {
                if (!$roomsSelected['room_from'] or !$roomsSelected['room_to'] or !$roomsSelected['policy']) {
                    continue;
                }

                $room_from = $roomsSelected['room_from']['id'];
                $room_to = $roomsSelected['room_to']['id'];
                $policy_id = $roomsSelected['policy']['id'];
                $availableDays = explode(
                    "|",
                    PoliciesRates::select('days_apply')->where('id', $policy_id)->first()->days_apply
                );

                $dataRoomsTo = Room::where('id', $room_to)->with('translations')->get();

                foreach ($dataRoomsFrom as $room) {
                    if ($room['id'] == $room_from) {
                        $dateFrom = Carbon::createFromFormat('d/m/Y', $room['dates_from']);
                        $dateTo = Carbon::createFromFormat('d/m/Y', $room['dates_to']);
                        $currentDate = $dateFrom->copy();

                        $roomNew = $room;
                        foreach ($dataRoomsTo as $dataRoomTo) {
                            $ratesPlansRoom = RatesPlansRooms::firstOrCreate([
                                'rates_plans_id' => $ratesPlan['id'],
                                'room_id' => $dataRoomTo['id'],
                                'status' => true,
                                'channel_id' => 1
                            ]);

                            $roomNew['id'] = $dataRoomTo['id'];
                            $roomNew['translations'] = $dataRoomTo['translations'];
                            $roomNew['policy_id'] = $policy_id;

                            $dataRoomsNew[] = $roomNew;

                            do {
                                if ($availableDays[0] === 'all' || in_array($currentDate->dayOfWeekIso,
                                        $availableDays)) {
                                    $datesInserBuRoom[$dataRoomTo['id']][$currentDate->format("Y-m-d")] = [
                                        'rates_plans_room_id' => $ratesPlansRoom->id,
                                        'policies_rate_id' => $policy_id,
                                        'price_adult' => $room['adult'],
                                        'price_child' => $room['child'],
                                        'price_infant' => $room['infant'],
                                        'price_extra' => $room['extra'],
                                    ];
                                }

                                $currentDate = $currentDate->addDay();
                            } while ($currentDate->lessThanOrEqualTo($dateTo));
                        }
                    }
                }
            }

            foreach ($datesInserBuRoom as $room) {
                foreach ($room as $date => $item) {
                    $ratesPlanCalendarys = RatesPlansCalendarys::updateOrCreate([
                        'date' => $date,
                        'rates_plans_room_id' => $item['rates_plans_room_id'],
                    ], [
                        'policies_rate_id' => $item['policies_rate_id'],
                        'status' => true
                    ]);

                    Rates::updateOrCreate([
                        'rates_plans_calendarys_id' => $ratesPlanCalendarys->id
                    ], [
                        'num_adult' => 0,
                        'num_child' => 0,
                        'num_infant' => 0,
                        'price_adult' => $item['price_adult'],
                        'price_child' => $item['price_child'],
                        'price_infant' => $item['price_infant'],
                        'price_extra' => $item['price_extra'],
                        'price_total' => 0
                    ]);
                }
            }

            $data = json_decode($request->getContent(), true);

            $data['meal_name'] = Meal::find($ratesPlan->meal_id)->translations;
            $data['hotel_name'] = Hotel::find($hotelID)->translations;
            $data['type_name'] = RatesPlansTypes::find($ratesPlan->rates_plans_type_id)->name;

            $data['rooms'] = $dataRoomsNew;

            $request->input('id');

            RatesHistory::create([
                'rates_plan_id' => $ratesPlan->id,
                'meal_id' => $ratesPlan->meal_id,
                'hotel_id' => $hotelID,
                'data' => json_encode($data),
                'dataRooms' => json_encode($dataRoomsNew),
                'user_id' => Auth::user()->id
            ]);

            return $ratesPlan;
        });


        return Response::json(['success' => true, 'rate_plan_id' => $ratesPlan->id]);
    }

    public function validateSuperPositionRate(Request $request)
    {
        $rate_plan_id = $request->post('rate_plan_id');
        $date_from = Carbon::createFromFormat('d/m/Y', $request->post('date_from'))->format('Y-m-d');
        $date_to = Carbon::createFromFormat('d/m/Y', $request->post('date_to'))->format('Y-m-d');
        $policies_rate_id = $request->post('policies_rate_id');
        $rooms = $request->post('rooms');

        $exist_calendars = 0;
        foreach ($rooms as $room) {
            if ($room["adult"] > 0) {

                $rate_plan_room = RatesPlansRooms::where('rates_plans_id', $rate_plan_id)->where('room_id',
                    $room["id"])->first();

                if ($rate_plan_room == null) {
                    $rate_plan_room = new RatesPlansRooms();
                    $rate_plan_room->rates_plans_id = $rate_plan_id;
                    $rate_plan_room->room_id = $room["id"];
                    $rate_plan_room->status = 1;
                    $rate_plan_room->bag = 0;
                    $rate_plan_room->channel_id = 1;
                    $rate_plan_room->save();
                }

                $calendars = RatesPlansCalendarys::where('rates_plans_room_id', $rate_plan_room->id)
                    ->where('policies_rate_id', $policies_rate_id)
                    ->where('date', '>=', $date_from)
                    ->where('date', '<=', $date_to)
                    ->get();

                if ($calendars->count() > 0) {
                    $exist_calendars = $calendars->count();
                    break;
                }

            }

        }
        if ($exist_calendars > 0) {
            return \response()->json(true);
        } else {
            return \response()->json(false);
        }
    }

    public function getDateRangesHotel($rate_plan_id, Request $request)
    {
        $year = $request->__get('year');
        $room_id = $request->__get('room_id');
        if (empty($year)) {
            $year = date('Y');
        }

        $date_range_ids_not_show = DateRangeHotel::select('old_id_date_range')->where('rate_plan_id',
            $rate_plan_id)->whereNotNull('old_id_date_range')->pluck('old_id_date_range')->toArray();
        $date_ranges = DateRangeHotel::where('rate_plan_id', $rate_plan_id)
            ->where(function ($query) use ($year) {
                $query->whereYear('date_from', $year);
            })->join('rooms', 'date_range_hotels.room_id', '=', 'rooms.id');

        if (!empty($room_id)) {
            $date_ranges = $date_ranges->where('date_range_hotels.room_id', $room_id);
        }

        $date_ranges = $date_ranges->whereNotIn('date_range_hotels.id', $date_range_ids_not_show)
            ->orderBy('rooms.order')->orderBy('date_range_hotels.date_from')->orderBy('date_range_hotels.group')->get([
                'date_range_hotels.id',
                'date_range_hotels.date_from',
                'date_range_hotels.date_to',
                'date_range_hotels.price_adult',
                'date_range_hotels.price_child',
                'date_range_hotels.price_infant',
                'date_range_hotels.price_extra',
                'date_range_hotels.discount_for_national',
                'date_range_hotels.rate_plan_id',
                'date_range_hotels.hotel_id',
                'date_range_hotels.room_id',
                'date_range_hotels.rate_plan_room_id',
                'date_range_hotels.meal_id',
                'date_range_hotels.policy_id',
                'date_range_hotels.old_id_date_range',
                'date_range_hotels.group',
                'date_range_hotels.updated',
                'date_range_hotels.created_at',
                'date_range_hotels.updated_at',
                'date_range_hotels.flag_migrate'
            ]);

        $policy_ids = DateRangeHotel::select('policy_id')->where('rate_plan_id',
            $rate_plan_id)->distinct()->pluck('policy_id');

        $room_ids = DateRangeHotel::select('room_id')->where('rate_plan_id',
            $rate_plan_id)->distinct()->pluck('room_id');

        $policy_names = PoliciesRates::select('id', 'name')->whereIn('id', $policy_ids)->get()->toArray();

        $room_names = Translation::select('object_id', 'value')->where('slug', 'room_name')->where('type',
            'room')->whereIn('object_id', $room_ids)->where('language_id', 1)->get()->toArray();


        $date_ranges->transform(function ($date_range, $key) use ($policy_names, $room_names) {
            foreach ($policy_names as $policy_name) {
                if ($date_range["policy_id"] == $policy_name["id"]) {
                    $date_range["policy_name"] = $policy_name["name"];
                }
            }
            foreach ($room_names as $room_name) {
                if ($date_range["room_id"] == $room_name["object_id"]) {
                    $date_range["room_name"] = $room_name["value"];
                }
            }
            return $date_range;
        });

        return \response()->json($date_ranges);
    }

    public function setDateRangesHotel($rate_plan_id, Request $request)
    {
        $date_from_input = Carbon::createFromFormat('d/m/Y', $request->input('date_from'));
        $date_to_input = Carbon::createFromFormat('d/m/Y', $request->input('date_to'));
        $hotel_id = $request->input('hotel_id');
        $date_from = $date_from_input->format('Y-m-d');
        $date_to = $date_to_input->format('Y-m-d');
        $rooms = $request->input('rooms');
        $policy_id = $request->input('policy_id');
        $operation = $request->input('operation');

        $days = $date_from_input->diffInDays($date_to_input);

        if ($days > 365) {
            return response()->json(["message" => "Fechas de ingreso invalidas"], 402);
        }
        $rate_plan = RatesPlans::find($rate_plan_id);

        $room_ids = Room::select('id')->where('hotel_id', $hotel_id)->where('state', 1)->pluck('id')->toArray();

        $rate_plan_rooms = RatesPlansRooms::select('id', 'room_id')->where('rates_plans_id',
            $rate_plan_id)->whereIn('room_id', $room_ids)->where('channel_id', 1)->where('status', 1)->get()->toArray();

        if ($operation == "put") {
            $group = 999;
            foreach ($rooms as $room) {
                if ($room["date_range_id"] != null) {
                    $date_range_hotel = DateRangeHotel::find($room["date_range_id"]);
                    $group = $date_range_hotel->group;
                }
            }
            foreach ($rooms as $room) {
                if ($room["edit"] == true) {
                    if ($room["date_range_id"] != null) {

                        $date_range_hotel = DateRangeHotel::find($room["date_range_id"]);
                        $date_range_hotel->date_from = $date_from;
                        $date_range_hotel->date_to = $date_to;
                        $date_range_hotel->price_adult = $room["price_adult"];
                        $date_range_hotel->price_child = $room["price_child"];
                        $date_range_hotel->price_infant = $room["price_infant"];
                        $date_range_hotel->price_extra = $room["price_extra"];
                        $date_range_hotel->policy_id = $policy_id;
                        $date_range_hotel->updated = 1;
                        $date_range_hotel->flag_migrate = 0;
                        $date_range_hotel->save();

                    } else {
                        $rate_plan_room_id = null;
                        foreach ($rate_plan_rooms as $rate_plan_room) {
                            if ($rate_plan_room["room_id"] == $room["room_id"]) {
                                $rate_plan_room_id = $rate_plan_room["id"];
                            }
                        }
                        if ($rate_plan_room_id == null) {
                            $rate_plan_room_new = new RatesPlansRooms();
                            $rate_plan_room_new->rates_plans_id = $rate_plan_id;
                            $rate_plan_room_new->room_id = $room["room_id"];
                            $rate_plan_room_new->status = 1;
                            $rate_plan_room_new->bag = 0;
                            $rate_plan_room_new->channel_id = 1;
                            $rate_plan_room_new->save();

                            $rate_plan_room_id = $rate_plan_room_new->id;
                        }
                        $new_date_range_hotel = new DateRangeHotel();
                        $new_date_range_hotel->date_from = $date_from;
                        $new_date_range_hotel->date_to = $date_to;
                        $new_date_range_hotel->price_adult = $room["price_adult"];
                        $new_date_range_hotel->price_child = $room["price_child"];
                        $new_date_range_hotel->price_infant = $room["price_infant"];
                        $new_date_range_hotel->price_extra = $room["price_extra"] ?? 0;
                        $new_date_range_hotel->discount_for_national = 0;
                        $new_date_range_hotel->rate_plan_id = $rate_plan_id;
                        $new_date_range_hotel->hotel_id = $hotel_id;
                        $new_date_range_hotel->room_id = $room["room_id"];
                        $new_date_range_hotel->rate_plan_room_id = $rate_plan_room_id;
                        $new_date_range_hotel->meal_id = $rate_plan->meal_id;
                        $new_date_range_hotel->policy_id = $policy_id;
                        $new_date_range_hotel->old_id_date_range = null;
                        $new_date_range_hotel->group = $group;
                        $new_date_range_hotel->updated = 1;
                        $new_date_range_hotel->flag_migrate = 0;
                        $new_date_range_hotel->save();
                    }
                }
            }
        }

        if ($operation == "new") {

            $group = DateRangeHotel::select('group')->where('rate_plan_id', $rate_plan_id)->max('group') + 1;

            foreach ($rooms as $room) {
                if ($room["edit"] == true) {
                    $date_range_ids_not_show = DateRangeHotel::select('old_id_date_range')->where('rate_plan_id',
                        $rate_plan_id)->whereNotNull('old_id_date_range')->pluck('old_id_date_range')->toArray();
                    $validate_date_from = DateRangeHotel::where('date_from', '<=', $date_from)
                        ->where('date_to', '>=', $date_from)
                        ->where('policy_id', $policy_id)
                        ->where('room_id', $room["room_id"])
                        ->where('rate_plan_id', $rate_plan_id)
                        ->whereNotIn('id', $date_range_ids_not_show)
                        ->get()->count();
                    $validate_date_to = DateRangeHotel::where('date_from', '<=', $date_to)
                        ->where('date_to', '>=', $date_to)
                        ->where('policy_id', $policy_id)
                        ->where('room_id', $room["room_id"])
                        ->whereNotIn('id', $date_range_ids_not_show)
                        ->where('rate_plan_id', $rate_plan_id)
                        ->get()->count();
                    if ($validate_date_from > 0 || $validate_date_to > 0) {
                        return response()->json(["message" => "Ya hay tarifas ingresadas en esos cortes de fecha"],
                            402);
                    }
                    $rate_plan_room_id = null;
                    foreach ($rate_plan_rooms as $rate_plan_room) {
                        if ($rate_plan_room["room_id"] == $room["room_id"]) {
                            $rate_plan_room_id = $rate_plan_room["id"];
                            $check_exist_rate_plan_room = true;
                        }
                    }
                    if ($rate_plan_room_id == null) {
                        $rate_plan_room_new = new RatesPlansRooms();
                        $rate_plan_room_new->rates_plans_id = $rate_plan_id;
                        $rate_plan_room_new->room_id = $room["room_id"];
                        $rate_plan_room_new->status = 1;
                        $rate_plan_room_new->bag = 0;
                        $rate_plan_room_new->channel_id = 1;
                        $rate_plan_room_new->save();

                        $rate_plan_room_id = $rate_plan_room_new->id;
                    }
                    $new_date_range_hotel = new DateRangeHotel();
                    $new_date_range_hotel->date_from = $date_from;
                    $new_date_range_hotel->date_to = $date_to;
                    $new_date_range_hotel->price_adult = $room["price_adult"];
                    $new_date_range_hotel->price_child = $room["price_child"];
                    $new_date_range_hotel->price_infant = $room["price_infant"];
                    $new_date_range_hotel->price_extra = $room["price_extra"] ?? 0;
                    $new_date_range_hotel->discount_for_national = 0;
                    $new_date_range_hotel->rate_plan_id = $rate_plan_id;
                    $new_date_range_hotel->hotel_id = $hotel_id;
                    $new_date_range_hotel->room_id = $room["room_id"];
                    $new_date_range_hotel->rate_plan_room_id = $rate_plan_room_id;
                    $new_date_range_hotel->meal_id = $rate_plan->meal_id;
                    $new_date_range_hotel->policy_id = $policy_id;
                    $new_date_range_hotel->old_id_date_range = null;
                    $new_date_range_hotel->group = $group;
                    $new_date_range_hotel->updated = 1;
                    $new_date_range_hotel->flag_migrate = 0;
                    $new_date_range_hotel->save();

                }
            }
        }


        $generate_Rates_In_Calendar = GenerateRatesInCalendar::where('hotel_id', $hotel_id)->where('rates_plans_id', $rate_plan_id)->where('status', '0')->first();
        if (!$generate_Rates_In_Calendar) {
            $params = [];
            $params['hotel_id'] = $hotel_id;
            $params['rates_plans_id'] = $rate_plan_id;
            $params['room_id'] = NULL;
            $params['perido'] = date('Y');
            $params['status'] = 0;
            $params['user_add'] = auth()->user()->id;
            GenerateRatesInCalendar::create($params);
        }


        return \response()->json("Rangos de fecha creados o actualizados correctamente");
        //        $fechaInicio = Carbon::parse("2020-11-01");
//        $fechaFin = Carbon::parse("2020-11-30");
//
//        $diferencia = $fechaInicio->diff($fechaFin)->days;
//        $monday_days = [];
//        for ($i=1;$i<=$diferencia;$i++)
//        {
//
//            if ($fechaInicio->dayOfWeek == 1)
//            {
//                array_push($monday_days,$fechaInicio->format('Y-m-d'));
//            }
//            $fechaInicio->addDays(1);
////            $new_date = $fechaInicio->addDays($i);
//        }
//        dd($monday_days);

        //Recorro las fechas y con la función strotime obtengo los lunes
//        $fechas_lunes = [];
//        for ($i = $fechaInicio; $i <= $fechaFin; $i += 86400 * 7){
//            array_push($fechas_lunes,date("Y-m-d", strtotime('monday this week', $i))) ;
//        }
//        dd($fechas_lunes);
    }

    public function updateDateRangeHotelGroup(Request $request)
    {
        $date_range_id = $request->input('date_range_id');
        $group = $request->input('group');

        $date_range = DateRangeHotel::find($date_range_id);
        $date_range->group = $group;
        $date_range->save();

        return \response()->json("Grupo actualizado en rango de fecha");
    }

    public function deleteDateRangeHotel($date_range_id)
    {
        $date_range = DateRangeHotel::find($date_range_id);
        if ($date_range) {
            DB::raw('SET SQL_SAFE_UPDATES = 0;');

            $generate_Rates_In_Calendar = GenerateRatesInCalendar::where('hotel_id', $date_range->hotel_id)->where('rates_plans_id', $date_range->rate_plan_id)->where('status', '0')->first();
            if (!$generate_Rates_In_Calendar) {

                $params = [];
                $params['hotel_id'] = $date_range->hotel_id;
                $params['rates_plans_id'] = $date_range->rate_plan_id;
                $params['room_id'] = NULL;
                $params['perido'] = date('Y');
                $params['status'] = 0;
                $params['user_add'] = auth()->user()->id;
                GenerateRatesInCalendar::create($params);
            }

            $date_range->delete();
            DB::raw('SET SQL_SAFE_UPDATES = 1;');
        } else {
            return \response()->json("Rango de fecha no existe");
        }
        return \response()->json("Rango de fecha eliminado");
    }

    public function updateStatus(Request $request)
    {

        $rate_plan_id = $request->input('rate_plan_id');
        $status = $request->input('status');
        $send_to_mkt = $request->input('send_to_mkt');

        $uses = [];

        if (!$status) {
            $uses = $this->get_rate_plan_uses($rate_plan_id);
            if (count($uses) > 0) {
                return Response::json(['success' => false, 'uses' => $uses]);
            }
        }

        $rate_plan = RatesPlans::find($rate_plan_id);
        $rate_plan->status = $status;
        $rate_plan->save();

        RatesPlansRooms::where('rates_plans_id', $rate_plan_id)->update(['status' => $status]);

        if($send_to_mkt){
            $who = Auth::user()->name;
            $hotel_ = Hotel::find($rate_plan->hotel_id);
            $hotel_id = $hotel_->id;
            $hotel_name = $hotel_->name;
            $rate_id = $rate_plan->id;
            $rate_name = $rate_plan->name;
            $this->send_notification_notes_mkt($who, $hotel_name, $rate_name, $hotel_id, $rate_id);
        }

        return Response::json(['success' => true, 'uses' => $uses]);
    }

    private function send_notification_notes_mkt($who, $hotel_name, $rate_name, $hotel_id, $rate_id){
        $mail = Mail::to('marketing@limatours.com.pe');

        $data = [
            "who" => $who,
            "hotel_name" => $hotel_name,
            "rate_name" => $rate_name,
            "hotel_id" => $hotel_id,
            "rate_id" => $rate_id,
        ];

        $mail->send(new NotificationRateNotesMarketing($data));
    }

    private function getArrayDates(Carbon $date_from, Carbon $date_to, $days_apply)
    {
        $array_dates = [];
        if ($days_apply == "all") {
            while ($date_from <= $date_to) {
                array_push($array_dates, $date_from->format('Y-m-d'));
                $date_from->addDays(1);
            }
        } else {
            $days_of_week_valid = explode('|', $days_apply);
            while ($date_from <= $date_to) {
                if (in_array($date_from->dayOfWeekIso, $days_of_week_valid)) {
                    array_push($array_dates, $date_from->format('Y-m-d'));

                }
                $date_from->addDays(1);
            }
        }
        return $array_dates;
    }


    public function report_uses($id, Request $request)
    {

        try {

            $data = $request->input('data');

            $data["user"] = Auth::user()->name . ' (' . Auth::user()->code . ')';

            $rate_plan = RatesPlans::where('id', $id)->with('hotel.channels')->first();
//           return var_export( json_encode( $rate_plan ) ); die;
            $data["hotel"] = $rate_plan->hotel->channels[0]->pivot->code . ' - ' . $rate_plan->hotel->name;

            $mail = mail::to("producto@limatours.com.pe");
            $mail->cc(["neg@limatours.com.pe", "kams@limatours.com.pe", "qr@limatours.com.pe"]);

            $mail->send(new \App\Mail\NotificationRatePlanStatus($data));

            $new_deactivatable_entity = new DeactivatableEntity();
            $new_deactivatable_entity->entity = "App\RatesPlans";
            $new_deactivatable_entity->object_id = $id;
            $new_deactivatable_entity->after_hours = 48;
            $new_deactivatable_entity->param = "status";
            $new_deactivatable_entity->value = "0";
            $new_deactivatable_entity->save();

            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'error' => $e->getMessage()]);
        }


    }

    public function notify_new_rate(Request $request, $_rate)
    {
        try {

            $mail = mail::to("qr@limatours.com.pe")
                ->cc("kams@limatours.com.pe")
                ->cc("neg@limatours.com.pe")
                ->bcc(["kluizsv@gmail.com"]);
            // $mail->cc(["neg@limatours.com.pe", "kams@limatours.com.pe", "qr@limatours.com.pe"]);

            $rate = (array)$request->__get('rate');

            $hotel = Hotel::where('id', '=', $rate['hotel_id'])->first()->toArray();

            $data = [
                'rate' => $request->__get('rate'),
                'hotel' => $hotel,
            ];

            $log = new LogNotification;
            $log->user_id = Auth::user()->id;
            $log->action = 'Notify new hotel';

            $details = [
                'to' => 'qr@limatours.com.pe',
                'cc' => ['kams@limatours.com.pe', 'neg@limatours.com.pe'],
                'bcc' => 'kluizsv@gmail.com',
                'data' => $data
            ];

            $log->details = json_encode($details);
            $log->ip = $request->ip();
            $log->save();

            $mail->send(new \App\Mail\NotificationPromotion($data, 'rate'));

            return Response::json(['success' => true, $data]);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function storeAssociateRate($hotel_id, $rate_id, Request $request)
    {

        $regions = $request->input('regions');
        $countries = $request->input('countries');
        $except_country = $request->input('except_country');
        $clients = $request->input('clients');
        $except_client = $request->input('except_client');
        $year = Carbon::now()->format('Y');
        $next_year = (int)$year + 1;
        $job_status = $this->checkStatusJobExecute('RatePlanAssociation', $rate_id, null, $year);


        if (!$job_status) {
            $find_rows = RatePlanAssociation::where('rate_plan_id', $rate_id)->get(['id']);


            if ($find_rows->count() > 0) {
                DB::table('rate_plan_associations')->whereIn('id', $find_rows->pluck('id'))->delete();
            }

            if (count($regions) > 0) {
                $regions = collect($regions)->unique();
                foreach ($regions as $region) {
                    $new_rate_plan_association = new RatePlanAssociation();
                    $new_rate_plan_association->rate_plan_id = $rate_id;
                    $new_rate_plan_association->entity = 'Market';
                    $new_rate_plan_association->object_id = $region['value'];
                    $new_rate_plan_association->save();
                }
            }

            if (count($countries) > 0) {
                $countries = collect($countries)->unique();
                foreach ($countries as $country) {
                    $new_rate_plan_association = new RatePlanAssociation();
                    $new_rate_plan_association->rate_plan_id = $rate_id;
                    $new_rate_plan_association->entity = 'Country';
                    $new_rate_plan_association->except = $except_country;
                    $new_rate_plan_association->object_id = $country['id'];
                    $new_rate_plan_association->save();
                }
            }

            if (count($clients) > 0) {
                $clients = collect($clients)->unique();
                foreach ($clients as $client) {
                    $new_rate_plan_association = new RatePlanAssociation();
                    $new_rate_plan_association->rate_plan_id = $rate_id;
                    $new_rate_plan_association->entity = 'Client';
                    $new_rate_plan_association->except = $except_client;
                    $new_rate_plan_association->object_id = $client['code'];
                    $new_rate_plan_association->save();
                }
            }

            $this->store_job('RatePlanAssociation', $rate_id, $request->all(), null, $year);
            $this->store_job('RatePlanAssociation', $rate_id, $request->all(), null, (string)$next_year);

            StoreClientsAssociations::dispatch($rate_id, $year, Auth::id());
            StoreClientsAssociations::dispatch($rate_id, (string)$next_year, Auth::id());


        } else {
            return Response::json([
                'success' => false,
                'message' => 'Ya se encuentra procesando una solicitud, por favor espere un momento.'
            ]);
        }


        return Response::json([
            'success' => true,
            'message' => 'Su solicitud está siendo procesada, se le notificará a su email cuando el proceso culmine.'
        ]);
    }

    public function getAssociateRate($rate_plan_id, Request $request)
    {
        $lang = ($request->has('lang')) ? $request->input('lang') : 'en';
        $language = Language::where('iso', $lang)->first();
        $result = [
            'association_countries' => [],
            'association_regions' => [],
            'association_clients' => [],
            'except_country' => false,
            'except_client' => false,
        ];

        $rate_plan_associations = RatePlanAssociation::where('rate_plan_id', $rate_plan_id)->get([
            'id',
            'rate_plan_id',
            'entity',
            'object_id',
            'except'
        ]);

        foreach ($rate_plan_associations as $association) {
            if ($association['entity'] === 'Market') {
                $market = Market::select('id as value', 'name as text')->find($association['object_id']);
                array_push($result['association_regions'], $market);
            }

            if ($association['entity'] == 'Country') {
                $country = Country::select('id', 'iso')
                    ->with([
                        'translations' => function ($query) use ($language) {
                            $query->select('value', 'object_id');
                            $query->where('type', 'country');
                            $query->where('language_id', $language->id);
                        }
                    ])->find($association['object_id']);

                $country['name'] = '[' . $country['iso'] . '] - ' . $country['translations'][0]['value'];
                $result['except_country'] = (boolean)$association['except'];
                array_push($result['association_countries'], $country);
            }

            if ($association['entity'] === 'Client') {
                $client = Client::select('id as code',
                    DB::raw("concat( '(', code, ') ',name) as label"))->find($association['object_id']);
                $result['except_client'] = (boolean)$association['except'];
                array_push($result['association_clients'], $client);
            }

        }

        return Response::json(['success' => true, 'data' => $result]);

    }

}

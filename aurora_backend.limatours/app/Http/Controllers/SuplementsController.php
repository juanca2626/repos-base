<?php

namespace App\Http\Controllers;

use App\HotelOptionSupplement;
use App\HotelOptionSupplementCalendar;
use App\HotelSupplement;
use App\Http\Requests\SupplementRateRequest;
use App\RateSupplement;
use App\Suplement;
use App\Http\Traits\Translations;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class SuplementsController extends Controller
{
    use Translations;


    public function __construct()
    {
        $this->middleware('permission:supplements.read')->only('index');
        $this->middleware('permission:supplements.create')->only('store');
        $this->middleware('permission:supplements.update')->only('update');
        $this->middleware('permission:supplements.delete')->only('delete');
    }


    /**
     * Display a listing of the resource.
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $lang = $request->input("lang");
        $suplements = Suplement::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'suplement');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();


        return Response::json(['success' => true, 'data' => $suplements]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'per_person' => 'boolean',
            'per_room' => 'boolean',
            'state' => 'required|boolean',
            'translations.*.suplement_name' => 'unique:translations,value,NULL,id,type,suplement'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                array_push($arrayErrors, $error);
            }
            $countErrors++;
        }

        if ($countErrors > 0) {
            return Response::json(['success' => false, 'error' => $arrayErrors]);
        } else {
            $suplement = new Suplement();
            $suplement->state = $request->input("state");
            $suplement->per_person = $request->input("per_person");
            $suplement->per_room = $request->input("per_room");
            $suplement->save();
            $this->saveTranslation($request->input("translations"), 'suplement', $suplement->id);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(Request $request, $id)
    {
        $lang = $request->get('lang');

        $suplement = Suplement::with([
            'translations' => function ($query) use ($id, $lang) {
                $query->where('type', 'suplement');
                $query->where('object_id', $id);

            }
        ])->where('id', $id)->first();

        return Response::json(['success' => true, 'data' => $suplement]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'per_person' => 'boolean',
            'per_room' => 'boolean',
            'state' => 'required|boolean',
            'translations.*.suplement_name' => 'unique:translations,value,'.$id.',object_id,type,suplement'
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
            $suplement = Suplement::find($id);
            $suplement->state = $request->input("state");
            $suplement->per_person = $request->input("per_person");
            $suplement->per_room = $request->input("per_room");
            $suplement->save();
            $this->saveTranslation($request->input('translations'), 'suplement', $id);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $suplement = Suplement::find($id);

        $suplement->delete();

        $this->deleteTranslation('suplement', $id);

        return Response::json(['success' => true]);
    }

    public function getByHotelId(Request $request)
    {
        $hotel_id = $request->get('hotel_id');
        $lang = $request->get('lang');

        $supplements = HotelSupplement::with([
            'supplement' => function ($query) use ($lang) {
                $query->with([
                    'translations' => function ($query) use ($lang) {
                        $query->where('type', 'suplement');
                        $query->whereHas('language', function ($q) use ($lang) {
                            $q->where('iso', $lang);
                        });
                    }
                ]);
            }
        ])->whereHas('supplement', function ($q) {
            $q->where('state', 1);
        })->where('hotel_id', $hotel_id)->get();

        return Response::json(['success' => true, 'data' => $supplements]);
    }

    public function getByHotelIdSupplementId(Request $request)
    {
        $hotel_id = $request->get('hotel_id');
        $supplement_id = $request->get('supplement_id');
        $lang = $request->get('lang');

        $supplements = HotelOptionSupplement::where('hotel_id', $hotel_id)->where('supplement_id',
            $supplement_id)->orderBy('from')->orderBy('min_age')->get()->toArray();

        foreach ($supplements as $id => $supplement) {
            $supplement['accion'] = false;
            $supplements[$id] = $supplement;
        }

        return Response::json(['success' => true, 'data' => $supplements]);
    }

    public function getCalendaryByHotelIdSupplementId(Request $request)
    {

        $hotel_id = $request->get('hotel_id');
        $inicio = $request->get('inicio');
        $fin = $request->get('fin');
        $supplement_id = $request->get('supplement_id');
        $lang = $request->get('lang');

        //$supplements = HotelOptionSupplementCalendar::where('hotel_id',$hotel_id)->where('supplement_id',$supplement_id)->get();


        $calendary = HotelOptionSupplementCalendar::with([
            'supplement' => function ($query) use ($lang) {
                $query->with([
                    'translations' => function ($query) use ($lang) {
                        $query->where('type', 'suplement');
                        $query->whereHas('language', function ($q) use ($lang) {
                            $q->where('iso', $lang);
                        });
                    }
                ]);
            }
        ])->whereHas('supplement', function ($q) {
            $q->where('state', 1);
        })->where('hotel_id', $hotel_id)
            ->where('date', '>=', $inicio)
            ->where('date', '<=', $fin)->groupBy(['date', 'supplement_id'])->get()->toArray();

        //})->where('hotel_id',$hotel_id)->get()->groupBy(['date','supplement_id']);

        $formatCalenday = [];

        foreach ($calendary as $calen) {

            $formatCalenday[] = [
                'id' => $calen['id'].'-'.$calen['supplement_id'],
                'startDate' => $calen['date'],
                'title' => $calen['supplement']['translations'][0]['value']
            ];
        }

        return Response::json(['success' => true, 'data' => $formatCalenday]);
    }

    public function getCalendaryByHotelIdSupplementIdFecha(Request $request)
    {
        $lang = $request->get('lang');
        $hotel_id = $request->get('hotel_id');
        $supplement_id = $request->get('supplement_id');
        $fecha = $request->get('fecha');

        $supplements = HotelOptionSupplementCalendar::where('hotel_id', $hotel_id)->where('supplement_id',
            $supplement_id)->where('date', $fecha)->get()->toArray();


        if ($supplements[0]['price_per_room'] > 0) {
            $result = ['room' => '1', 'pers' => '0', 'price' => $supplements[0]['price_per_room']];
        } else {
            $result = ['room' => '0', 'pers' => '1', 'price' => $supplements];
        }

        return Response::json(['success' => true, 'data' => $result]);

    }

    public function getByRatePlanId(Request $request)
    {
        $rate_plan_id = $request->get('rate_plan_id');
        $lang = $request->get('lang');

        $supplements = RateSupplement::with([
            'supplement' => function ($query) use ($lang) {
                $query->with([
                    'translations' => function ($query) use ($lang) {
                        $query->where('type', 'suplement');
                        $query->whereHas('language', function ($q) use ($lang) {
                            $q->where('iso', $lang);
                        });
                    }
                ]);
            }
        ])->whereHas('supplement', function ($q) {
            $q->where('state', 1);
        })->where('rate_plan_id', $rate_plan_id)->get();

        return response()->json($supplements, 200);
    }

    public function getSelectByHotel(Request $request)
    {
        $hotel_id = $request->get('hotel_id');
        $lang = $request->get('lang');

        $supplementsByHotel = HotelSupplement::where('hotel_id', $hotel_id)->pluck('supplement_id');

        $supplements = Suplement::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'suplement');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->where('state', 1)->whereNotIn('id', $supplementsByHotel)->get();

        return Response::json(['success' => true, 'data' => $supplements]);
    }

    public function getSelectByRate(Request $request)
    {
        $hotel_id = $request->get('hotel_id');
        $rate_plan_id = $request->get('rate_plan_id');
        $lang = $request->get('lang');

        $supplementsByRate = RateSupplement::where('rate_plan_id', $rate_plan_id)->pluck('supplement_id');

        $supplements = HotelSupplement::with([
            'supplement' => function ($query) use ($lang) {
                $query->with([
                    'translations' => function ($query) use ($lang) {
                        $query->where('type', 'suplement');
                        $query->whereHas('language', function ($q) use ($lang) {
                            $q->where('iso', $lang);
                        });
                    }
                ]);
            }
        ])->whereHas('supplement', function ($q) {
            $q->where('state', 1);
        })->where('hotel_id', $hotel_id)->whereNotIn('supplement_id', $supplementsByRate)->get();

        return response()->json($supplements, 200);
    }

    public function setSupplementHotel(Request $request)
    {
        $hotel_id = $request->post('hotel_id');

        $supplement_id = $request->post('supplement_id');

        $supplement = new HotelSupplement();

        $supplement->hotel_id = $hotel_id;
        $supplement->supplement_id = $supplement_id;
        $supplement->save();

        return Response::json(['success' => true]);
    }

    public function setSupplementRate(SupplementRateRequest $request)
    {
        $rate_plan_id = $request->post('rate_plan_id');

        $supplement_id = $request->post('supplement_id');

        $type = $request->post('type');

        $amount_extra = $request->post('amount_extra');

        $supplement = new RateSupplement();
        $supplement->type = $type;
        $supplement->rate_plan_id = $rate_plan_id;
        $supplement->amount_extra = $amount_extra;
        $supplement->supplement_id = $supplement_id;
        $supplement->save();

        return response()->json(['message' => "Suplemento guardado correctamente"], 200);
    }

    public function deleteSupplementRate(Request $request)
    {
        $supplement = RateSupplement::find($request->post('id'));

        $supplement->delete();

        return Response::json(['message' => "Suplemento Eliminado Correctamente"]);
    }

    public function deleteSupplementHotel(Request $request)
    {

        DB::transaction(function () use ($request) {

            $supplement = HotelSupplement::find($request->post('id'));
            $supplement_option = HotelOptionSupplement::where('hotel_id', $supplement->hotel_id)->where('supplement_id',
                $supplement->supplement_id);
            $supplement_option_calendars = HotelOptionSupplementCalendar::where('hotel_id',
                $supplement->hotel_id)->where('supplement_id', $supplement->supplement_id);

            $supplement_option->delete();
            $supplement_option_calendars->delete();
            $supplement->delete();

        });

        return Response::json(['success' => true]);

    }

    public function setSupplementHotelPerRoom(Request $request)
    {
        $hotel_id = $request->post('hotel_id');
        $supplement_id = $request->post('supplement_id');
        $options = $request->post('options');
        $created_at = date("Y-m-d H:i:s");

        DB::transaction(function () use ($hotel_id, $supplement_id, $options, $created_at) {

            foreach ($options as $option) {
                $date_from = Carbon::parse($option["dateRange"]["startDate"]);
                $date_to = Carbon::parse($option["dateRange"]["endDate"]);
                $quantity_days = $date_from->diffInDays($date_to);
                $created_at = date("Y-m-d H:i:s");

                DB::table('hotel_option_supplements')->insert([
                    'from' => $date_from,
                    'to' => $date_to,
                    'price_per_room' => $option["price_per_room"],
                    'hotel_id' => $hotel_id,
                    'supplement_id' => $supplement_id,
                    'created_at' => $created_at,
                    'updated_at' => $created_at
                ]);

                DB::table('hotel_option_supplement_calendars')
                    ->where('hotel_id', $hotel_id)
                    ->where('supplement_id', $supplement_id)
                    ->where('date', '>=', $date_from->format('Y-m-d'))
                    ->where('date', '<=', $date_to->format('Y-m-d'))
                    ->delete();

                for ($i = 0; $i <= $quantity_days; $i++) {
                    if ($i == 0) {
                        DB::table('hotel_option_supplement_calendars')->insert([
                            'date' => $date_from,
                            'price_per_room' => $option["price_per_room"],
                            'hotel_id' => $hotel_id,
                            'supplement_id' => $supplement_id,
                            'created_at' => $created_at,
                            'updated_at' => $created_at
                        ]);
                    } else {
                        DB::table('hotel_option_supplement_calendars')->insert([
                            'date' => $date_from->copy()->addDays($i),
                            'price_per_room' => $option["price_per_room"],
                            'hotel_id' => $hotel_id,
                            'supplement_id' => $supplement_id,
                            'created_at' => $created_at,
                            'updated_at' => $created_at
                        ]);
                    }
                }
            }
        });
        return Response::json(['success' => true, "message" => "Supplemento Cargado Correctamente"]);
    }

    public function setSupplementHotelPerPerson(Request $request)
    {
        $hotel_id = $request->post('hotel_id');
        $supplement_id = $request->post('supplement_id');
        $date_range = $request->post('date_range');
        $options = $request->post('options');
        $created_at = date("Y-m-d H:i:s");


        DB::transaction(function () use ($hotel_id, $supplement_id, $options, $date_range, $created_at) {
            $date_from = Carbon::parse($date_range["startDate"]);
            $date_to = Carbon::parse($date_range["endDate"]);
            $quantity_days = $date_from->diffInDays($date_to);

            DB::table('hotel_option_supplement_calendars')
                ->where('hotel_id', $hotel_id)
                ->where('supplement_id', $supplement_id)
                ->where('date', '>=', $date_from->format('Y-m-d'))
                ->where('date', '<=', $date_to->format('Y-m-d'))
                ->delete();

            foreach ($options as $option) {
                DB::table('hotel_option_supplements')->insert([
                    'from' => $date_from,
                    'to' => $date_to,
                    'price_per_person' => $option["price_per_person"],
                    'min_age' => $option["min_age"],
                    'max_age' => $option["max_age"],
                    'hotel_id' => $hotel_id,
                    'supplement_id' => $supplement_id,
                    'created_at' => $created_at,
                    'updated_at' => $created_at
                ]);

                for ($i = 0; $i <= $quantity_days; $i++) {
                    if ($i == 0) {
                        DB::table('hotel_option_supplement_calendars')->insert([
                            'date' => $date_from,
                            'price_per_person' => $option["price_per_person"],
                            'min_age' => $option["min_age"],
                            'max_age' => $option["max_age"],
                            'hotel_id' => $hotel_id,
                            'supplement_id' => $supplement_id,
                            'created_at' => $created_at,
                            'updated_at' => $created_at
                        ]);
                    } else {
                        DB::table('hotel_option_supplement_calendars')->insert([
                            'date' => $date_from->copy()->addDays($i),
                            'price_per_person' => $option["price_per_person"],
                            'min_age' => $option["min_age"],
                            'max_age' => $option["max_age"],
                            'hotel_id' => $hotel_id,
                            'supplement_id' => $supplement_id,
                            'created_at' => $created_at,
                            'updated_at' => $created_at
                        ]);
                    }
                }
            }
        });
        return Response::json(['success' => true, "message" => "Supplemento Cargado Correctamente"]);
    }


    public function setUpdateSupplementHotelPerPerson(Request $request)
    {

        DB::transaction(function () use ($request) {

            $id = $request->post('id');
            $tipo = $request->post('tipo');

            $supplement_option = HotelOptionSupplement::find($id);


            $hotel_id = $supplement_option->hotel_id;
            $supplement_id = $supplement_option->supplement_id;
            $date_from = $supplement_option->from;
            $date_to = $supplement_option->to;
            $min_age = $supplement_option->min_age;
            $max_age = $supplement_option->max_age;
            $price_per_person = $supplement_option->price_per_person;
            $price_per_room = $supplement_option->price_per_room;

            $min_age_new = $request->post('min_age');
            $max_age_new = $request->post('max_age');
            $price_per_person_new = $request->post('price_per_person');
            $price_per_room_new = $request->post('price_per_room');


            $supplement_calendario = HotelOptionSupplementCalendar::where('hotel_id', $hotel_id)
                ->where('supplement_id', $supplement_id)
                ->where('date', '>=', $date_from)
                ->where('date', '<=', $date_to)
                ->get();

            foreach ($supplement_calendario as $calendar) {
                if ($tipo == "1") {
                    if ($calendar->min_age == $min_age and $calendar->max_age == $max_age and $calendar->price_per_person == $price_per_person) {
                        $calendar->min_age = $min_age_new;
                        $calendar->max_age = $max_age_new;
                        $calendar->price_per_person = $price_per_person_new;
                        $calendar->save();
                    }
                } else {
                    if ($calendar->price_per_room == $price_per_room) {
                        $calendar->price_per_room = $price_per_room_new;
                        $calendar->save();
                    }
                }
            }

            $supplement_option->min_age = $min_age_new;
            $supplement_option->max_age = $max_age_new;
            $supplement_option->price_per_person = $price_per_person_new;
            $supplement_option->price_per_room = $price_per_room_new;
            $supplement_option->save();

        });

        return Response::json(['success' => true, "message" => "Supplemento Cargado Correctamente"]);
    }

    public function deleteSupplementHotelPerPerson(Request $request)
    {

        DB::transaction(function () use ($request) {

            $id = $request->post('id');
            $tipo = $request->post('tipo');

            $supplement_option = HotelOptionSupplement::find($id);


            $hotel_id = $supplement_option->hotel_id;
            $supplement_id = $supplement_option->supplement_id;
            $date_from = $supplement_option->from;
            $date_to = $supplement_option->to;
            $min_age = $supplement_option->min_age;
            $max_age = $supplement_option->max_age;
            $price_per_person = $supplement_option->price_per_person;
            $price_per_room = $supplement_option->price_per_room;

            $supplement_calendario = HotelOptionSupplementCalendar::where('hotel_id', $hotel_id)
                ->where('supplement_id', $supplement_id)
                ->where('date', '>=', $date_from)
                ->where('date', '<=', $date_to)
                ->get();

            foreach ($supplement_calendario as $calendar) {
                if ($tipo == "1") {
                    if ($calendar->min_age == $min_age and $calendar->max_age == $max_age and $calendar->price_per_person == $price_per_person) {
                        $calendar->delete();
                    }
                } else {
                    if ($calendar->price_per_room == $price_per_room) {
                        $calendar->delete();
                    }
                }
            }

            $supplement_option->delete();

        });

        return Response::json(['success' => true, "message" => "Supplemento eliminado Correctamente"]);
    }


}

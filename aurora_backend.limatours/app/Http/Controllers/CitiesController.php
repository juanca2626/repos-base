<?php

namespace App\Http\Controllers;

use App\City;
use App\HotelRateOrderCity;
use App\Language;
use App\RateOrderCity;
use App\State;
use App\Http\Traits\Translations;
use App\Translation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class CitiesController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:cities.read')->only('index');
        $this->middleware('permission:cities.create')->only('store');
        $this->middleware('permission:cities.update')->only('update');
        $this->middleware('permission:cities.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $lang = $request->input('lang');

        $language_id = Language::select('id')->where('iso', $lang)->first()->id;

        $city_ids = [];
        $state_ids = [];

        if ($querySearch) {

            $city_ids = Translation::select('object_id')->where('type', 'city')
                ->where('language_id', $language_id)
                ->where('value', 'like', '%'.$querySearch.'%')
                ->pluck('object_id')
                ->toArray();

            $state_ids = Translation::select('object_id')->where('type', 'state')
                ->where('language_id', $language_id)
                ->where('value', 'like', '%'.$querySearch.'%')
                ->pluck('object_id')
                ->toArray();
        }

        $cities = City::with([
            'translations' => function ($query) use ($language_id) {

                $query->where('language_id', $language_id);

            }
        ])
            ->with([
                'state.translations' => function ($query) use ($language_id) {

                    $query->where('language_id', $language_id);

                }
            ]);
        if ($querySearch) {
            $cities->whereIn('id', $city_ids);
            $cities->orWhereIn('state_id', $state_ids);
        }
        $count = $cities->count();

        $cities = $cities->paginate($limit);

        $data = [
            'data' => $cities->items(),
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
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
            'state_id' => 'required|exists:states,id',
            'iso' => 'nullable|string|max:10',
            'translations' => 'required|array',
            // Corrige el nombre del campo: usa city_name (o cambia el validador si de verdad se llama state_name)
            'translations.*.city_name' => 'required|string|max:255',
            // si en tu payload el campo se llama state_name, entonces usa esta línea y borra la anterior:
            // 'translations.*.state_name' => 'required|string|max:255',
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
            $city = new City();
            $city->state_id = $request->input("state_id");
            $city->iso = $request->input("iso");
            $city->save();

            $this->saveTranslation($request->input("translations"), 'city', $city->id);
            return Response::json(['success' => true]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @param  Request  $request
     * @return JsonResponse
     */
    public function show($id, Request $request)
    {
        $lang = $request->input('lang');
        $city = City::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'city');
                $query->where('object_id', $id);
            }
        ])->with([
            'state.translations' => function ($query) use ($lang) {
                $query->where('type', 'state');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->where('id', $id)->get();

        return Response::json(['success' => true, 'data' => $city]);
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
            'state_id' => "exists:states,id",
            'translations.*.city_name' => 'unique:translations,value,'.$id.',object_id,type,city'
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
            $city = City::find($id);
            if ($request->input('state_id') != null) {
                $city->state_id = $request->input('state_id');
                $city->iso = $request->input("iso");
                $city->save();
            }
            $this->saveTranslation($request->input('translations'), 'city', $id);

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
        $city = City::find($id);

        if ($city->hotels()->count() > 0) {
            return Response::json([
                'success' => false,
                'message' => 'Can not delete a City related to Hotels'
            ]);
        }

        $city->delete();

        $this->deleteTranslation('city', $id);

        return Response::json(['success' => true]);
    }

    public function selectBox(Request $request)
    {
        $lang = $request->input('lang');
        $cities = City::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'city');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'state.translations' => function ($query) use ($lang) {
                $query->where('type', 'state');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'state.country.translations' => function ($query) use ($lang) {
                $query->where('type', 'country');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();
        return Response::json(['success' => true, 'data' => $cities]);
    }

    public function getCitiesPeruByVueSelect()
    {
        $state_ids = State::where('country_id', 89)->pluck('id');

        $cities_old = City::whereIn('state_id', $state_ids)
            ->with([
                'translations' => function ($query) {
                    $query->where('type', 'city');
                    $query->where('language_id', 1);
                }
            ])->get();

        $cities = [];

        foreach ($cities_old as $city) {
            array_push($cities, [
                "code" => $city["id"],
                "label" => $city["translations"][0]["value"]
            ]);
        }

        return \response()->json($cities);
    }

    public function getCities($id, $lang)
    {
        $cities = City::where('state_id', $id)
            ->with([
                'translations' => function ($query) use ($lang) {
                    $query->where('type', 'city');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])->get();
        return Response::json(['success' => true, 'data' => $cities]);
    }

    public function service_orders_rate(Request $request)
    {
        $city_name = $request->get('filter_by_name');
        $states_id = State::where('country_id', 89)->pluck('id');
        $cities_id = [];

        if ($city_name != null) {
            $cities_id = Translation::where('type', 'city')->where('language_id', 1)->where('value', 'like',
                '%'.$city_name.'%')->pluck('object_id');
        }
        if ($city_name != null) {
            $cities = City::with([
                'translations' => function ($query) use ($city_name) {
                    $query->where('type', 'city');
                    $query->where('language_id', 1);
                }
            ])
                ->with('order_rate')
                ->whereIn('cities.state_id', $states_id)
                ->WhereIn('cities.id', $cities_id)
                ->join('rate_order_cities', 'cities.id', '=', 'rate_order_cities.city_id')
                ->orderBy('rate_order_cities.order')
                ->select('cities.*', 'rate_order_cities.order')
                ->paginate(10);
        } else {
            $cities = City::with([
                'translations' => function ($query) use ($city_name) {
                    $query->where('type', 'city');
                    $query->where('language_id', 1);
                }
            ])
                ->with('order_rate')
                ->whereIn('cities.state_id', $states_id)
                ->join('rate_order_cities', 'cities.id', '=', 'rate_order_cities.city_id')
                ->orderBy('rate_order_cities.order')
                ->select('cities.*', 'rate_order_cities.order')
                ->paginate(10);
        }


        return \response()->json($cities);
    }

    public function update_service_order_city(Request $request)
    {
        $order_city_id = $request->input('order_city_id');
        $order = (int)$request->input('order');

        $rate_order_city = RateOrderCity::find($order_city_id);
        $rate_order_city->order = ($rate_order_city->order < $order) ? $order + 1 : $order;
        $rate_order_city->save();

        $rate_order_cities = RateOrderCity::orderBy('order')->orderBy('updated_at', 'desc')->get();

        foreach ($rate_order_cities as $k => $rate_order_city) {
            $rate_order_city->order = $k + 1;
            $rate_order_city->save();
        }

        return \response()->json("orden de ciudad actualizada");
    }

    public function hotel_orders_rate(Request $request)
    {
        $city_name = $request->get('filter_by_name');
        $states_id = State::where('country_id', 89)->pluck('id');
        $cities_id = [];

        if ($city_name != null) {
            $cities_id = Translation::where('type', 'city')->where('language_id', 1)->where('value', 'like',
                '%'.$city_name.'%')->pluck('object_id');
        }
        if ($city_name != null) {
            $cities = City::with([
                'translations' => function ($query) use ($city_name) {
                    $query->where('type', 'city');
                    $query->where('language_id', 1);
                }
            ])
                ->with('hotel_order_rate')
                ->whereIn('cities.state_id', $states_id)
                ->WhereIn('cities.id', $cities_id)
                ->join('hotel_rate_order_cities', 'cities.id', '=', 'hotel_rate_order_cities.city_id')
                ->orderBy('hotel_rate_order_cities.order')
                ->select('cities.*', 'hotel_rate_order_cities.order')
                ->paginate(10);
        } else {
            $cities = City::with([
                'translations' => function ($query) use ($city_name) {
                    $query->where('type', 'city');
                    $query->where('language_id', 1);
                }
            ])
                ->with('hotel_order_rate')
                ->whereIn('cities.state_id', $states_id)
                ->join('hotel_rate_order_cities', 'cities.id', '=', 'hotel_rate_order_cities.city_id')
                ->orderBy('hotel_rate_order_cities.order')
                ->select('cities.*', 'hotel_rate_order_cities.order')
                ->paginate(10);
        }

        return \response()->json($cities);
    }

    public function from_informix(Request $request)
    {
        $country_code = ($request->get('country_code')) ? $request->get('country_code') : '';
        $filter = $request->get('filter');

        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET',
        config('services.stella.domain')  . 'api/v1/cities?filter='.$filter.'&country_code='.$country_code);
        $response = json_decode($response->getBody()->getContents());

        return \response()->json($response);
    }

    public function update_hotel_order_city(Request $request)
    {
        try
        {
            $cities = (array) $request->__get('cities');

            foreach($cities as $key => $value)
            {
                $rate_order_city = HotelRateOrderCity::find($value['hotel_order_rate']['id']);
                $rate_order_city->order = $value['hotel_order_rate']['order'];
                $rate_order_city->save();
            }

            return \response()->json(["success" => true]);
        }
        catch(\Exception $ex)
        {
            return \response()->json(["success" => false]);
        }

        /*
        $hotel_order_city_id = $request->input('hotel_order_city_id');
        $order = (int)$request->input('order');

        $rate_order_city = HotelRateOrderCity::find($hotel_order_city_id);
        $rate_order_city->order = ($rate_order_city->order < $order) ? $order + 1 : $order;

        if ($rate_order_city->save()) {

            $rate_order_cities = HotelRateOrderCity::orderBy('order')->orderBy('updated_at', 'desc')->get();

            foreach ($rate_order_cities as $k => $rate_order_city) {
                $rate_order_city->order = $k + 1;
                $rate_order_city->save();
            }

            return \response()->json(["success" => true]);
        } else {
            return \response()->json(["success" => false]);
        }
        */
    }
}

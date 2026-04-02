<?php

namespace App\Http\Controllers;

use App\BusinessRegionsCountry;
use App\ChannelHotel;
use App\DeactivatableEntity;
use App\Hotel;
use App\HotelClient;
use App\HotelFavoriteUser;
use App\HotelPreferentials;
use App\HotelTypeClass;
use App\Language;
use App\LogNotification;
use App\Mail\NotificationHotel;
use App\Mail\NotificationRateNotesMarketing;
use App\MarkupHotel;
use App\PackageService;
use App\ProgressBar;
use App\HotelAlert;
use App\HotelBackup;
use App\Http\Traits\AccessInfo;
use App\Http\Traits\ClientHotels;
use App\Http\Traits\Package as TraitPackage;
use App\Http\Traits\Translations;
use App\HyperguestProperty;
use App\Rates;
use App\RatesPlans;
use App\Translation;
use App\UpSelling;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use JD\Cloudder\Facades\Cloudder;


class HotelsController extends Controller
{
    use Translations, AccessInfo, ClientHotels, TraitPackage;

    public function __construct()
    {
        $this->middleware('permission:hotels.read')->only('index');
        $this->middleware('permission:hotels.create')->only('store');
        $this->middleware('permission:hotels.update')->only('update');
        $this->middleware('permission:hotels.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {

        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $lang = $request->input('lang');

        $chain_id = $request->input('chain');
        $country_id = $request->input('country');
        $state_id = $request->input('state');
        $city_id = $request->input('city');
        $district_id = $request->input('district');
        $status = $request->input('status');
        $typeclass_id = $request->input('typeclass_id');
        $aurora_name_hotel = $request->input('aurora_name');
        $channel_id = $request->input('channel_id');
        $channel_type = $request->input('channel_type');

        $chainUser_id = $this->filter();

        $hotels_database = Hotel::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'hotel');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'city.translations' => function ($query) use ($lang) {
                $query->where('type', 'city');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'city.state.translations' => function ($query) use ($lang) {
                $query->where('type', 'state');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'city.state.country.translations' => function ($query) use ($lang) {
                $query->where('type', 'country');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'channels'
        ])->withCount('galeries');
        //Todo: Si es de tipo proveedor y no tiene asignado ningun hotel no se devuelve nada
        if (empty($chainUser_id) and Auth::user()->user_type_id == 2) {
            $data = [
                'data' => [],
                'count' => 0,
                'success' => true
            ];
            return Response::json($data);
        } else {
            if (!empty($chainUser_id)) {
                $hotels_database->where('chain_id', $chainUser_id);
            }
        }


        //filtro de buscador
        if (trim($chain_id) != '' && $chain_id != null && $chain_id != "undefined" && $chain_id != 'null') {
            $hotels_database->Where('chain_id', $chain_id);
        }

        if (trim($country_id) != '' && $country_id != null && $country_id != "undefined" && $country_id != 'null') {
            $hotels_database->where('country_id', $country_id);
        }
        if (trim($typeclass_id) != '' && $typeclass_id != null && $typeclass_id != "undefined" && $typeclass_id != 'null') {
            $hotels_database->where('typeclass_id', $typeclass_id);
        }

        if (trim($state_id) != '' && $state_id != null && $state_id != "undefined" && $state_id != 'null') {
            $hotels_database->where('state_id', $state_id);
        }

        if (trim($city_id) != '' && $city_id != null && $city_id != "undefined" && $city_id != 'null') {
            $hotels_database->where('city_id', $city_id);
        }

        if (trim($district_id) != '' && $district_id != null && $district_id != "undefined" && $district_id != 'null') {
            $hotels_database->where('district_id', $district_id);
        }

        if (trim($status) != '' && $status != null && $status != "undefined" && $status != 'null') {
            $hotels_database->where('status', $status);
        }

        if (trim($aurora_name_hotel) != '' && $aurora_name_hotel != null && $aurora_name_hotel != "undefined" && $aurora_name_hotel != "null") {
            $hotels_database->where('name', 'like', '%' . $aurora_name_hotel . '%');
            $hotels_database->orWhereHas('channels', function ($q) use ($aurora_name_hotel) {
                $q->where('channel_hotel.code', 'like', '%' . $aurora_name_hotel . '%');
            });
        }

        if (trim($channel_id) != '' && $channel_id != null && $channel_id != "undefined" && $channel_id != 'null') {
            $hotels_database->whereHas('channel', function ($q) use ($channel_id, $channel_type) {
                $q->where('channel_hotel.channel_id', $channel_id);
                $q->where('channel_hotel.state', 1);

                if (trim($channel_type) != '' && $channel_type != null && $channel_type != "undefined" && $channel_type != 'null') {
                    $q->where('channel_hotel.type', $channel_type);
                }
            });
        }

        $count = $hotels_database->count();

        if ($paging === 1) {
            $hotels_database = $hotels_database->take($limit)->get();
        } else {
            $hotels_database = $hotels_database->skip($limit * ($paging - 1))->take($limit)->get();
        }

        if(config('app.env') === 'production')
        {
            $hotels_database->each(function ($hotel) {
                if($hotel->galeries_count === 0)
                {
                    if($hotel->created_cloudinary === 0)
                    {
                        $filename = 'https://backend.limatours.com.pe/images/logo.png';
                        $channel = $hotel->channels->first();
                        if (!$channel) {
                            return; // Si no hay canales activos, saltamos este hotel y seguimos con el siguiente
                        }
                        $aurora_code = $channel->pivot->code;
                        $folder = sprintf('hotels/%s', $aurora_code);

                        try
                        {
                            Cloudder::upload($filename, null, [
                                'folder' => $folder,
                                'public_id' => 'temp_placeholder',
                            ]);

                            // Borrar el archivo si solo quieres dejar la carpeta
                            Cloudder::destroyImage(sprintf('%s/temp_placeholder', $folder));

                            $hotel->created_cloudinary = 1;
                            $hotel->save();
                        }
                        catch(\Exception $ex)
                        {
                            app('sentry')->captureException($ex);
                        }
                    }
                }
                else
                {
                    if($hotel->created_cloudinary === 0)
                    {
                        $hotel->created_cloudinary = 1;
                        $hotel->save();
                    }
                }
            });
        }

        foreach ($hotels_database as $hotel) {
            $hotel->progress_bar_value = $hotel->progress_bars->sum('value');
        }

        // $hotels_database = $hotels_database->toArray();

        $hotels_database = $hotels_database->transform(function ($hotel) {
            $first_channel = $hotel->channels->first();
            $hotel->aurora_code = $first_channel ? $first_channel->pivot->code : '';

            $hotel->setRelation('channels', $hotel->channels->where('pivot.state', 1)->values());

            if ($hotel->date_end_flag_new < date("Y-m-d")) {
                $hotel->flag_new = 0;
            }

            return $hotel;
        });

        $data = [
            'data' => $hotels_database,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }


    public function list_general(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit') ? $request->input('limit') : 10;
        $lang = $request->input('lang') ? $request->input('lang') : "es";

        $filter = $request->input('filter');

        $hotels_database = Hotel::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'hotel');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'district.translations' => function ($query) use ($lang) {
                $query->where('type', 'city');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'country.translations' => function ($query) use ($lang) {
                $query->where('type', 'city');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'state.translations' => function ($query) use ($lang) {
                $query->where('type', 'city');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'city.translations' => function ($query) use ($lang) {
                $query->where('type', 'city');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'city.state.translations' => function ($query) use ($lang) {
                $query->where('type', 'state');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'city.state.country.translations' => function ($query) use ($lang) {
                $query->where('type', 'country');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'channel' => function ($query) {
                $query->where('channel_id', 1);
            }
        ]);

        if ($filter) {
            $hotels_database = $hotels_database->where("name", 'like', '%' . $filter . '%');
        }

        $count = $hotels_database->count();

        if ($paging === 1) {
            $hotels_database = $hotels_database->take($limit)->get();
        } else {
            $hotels_database = $hotels_database->skip($limit * ($paging - 1))->take($limit)->get();
        }


        $data = [
            'data' => $hotels_database,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }


    public function access()
    {

        $chainUser_id = $this->filter();
        $result = false;
        if ($chainUser_id == null) {
            $result = true;
        }

        return Response::json(['success' => true, 'access_bloqued' => $result]);
    }


    public function filter()
    {

        $user_id = Auth::user()->id;
        $chainUser_id = null;

        $hotels = Hotel::whereHas('hotelUser2', function ($query) use ($user_id) {
            $query->where('user_id', $user_id);
        })->with('hotelUser2')->get();

        if (count($hotels) > 0) {
            $chainUser_id = $hotels[0]->chain_id;
        }

        return $chainUser_id;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $channels = $request->input('channels');
        if (isset($channels[1]['code']) && !empty($channels[1]['code'])) {
            $existingChannel = ChannelHotel::where('code', $channels[1]['code'])->first();

            if ($existingChannel) {
                return response()->json([
                    'success' => false,
                    'message' => 'El código ingresado para el canal Aurora ya existe. Por favor, ingrese un código único.'
                ], 200);
            }
        }


        $send_to_mkt = $request->input('send_to_mkt');
        $hotel = new Hotel();
        $hotel->name = $request->input('name');
        $hotel->stars = $request->input('stars');
        $hotel->web_site = $request->input('web_site');
        $hotel->status = $request->input('status');
        $hotel->flag_new = $request->input('flag_new');
        // $hotel->preferential = $request->input('preferential') ? $request->input('preferential') : 0;
        $hotel->latitude = $request->input('latitude');
        $hotel->longitude = $request->input('longitude');
        $hotel->check_in_time = $request->input('check_in_time');
        $hotel->check_out_time = $request->input('check_out_time');
        $hotel->percentage_completion = 0;
        // $hotel->typeclass_id = $request->input('typeclass_id');
        $hotel->chain_id = $request->input('chain_id');
        $hotel->currency_id = $request->input('currency_id');
        $hotel->hotel_type_id = $request->input('hotel_type_id');
        $hotel->hotelcategory_id = $request->input('hotelcategory_id');
        $hotel->country_id = $request->input('country_id');
        $hotel->state_id = $request->input('state_id');
        $hotel->city_id = $request->input('city_id');
        $hotel->district_id = $request->input('district_id');
        $hotel->zone_id = $request->input('zone_id');
        // $hotel->notes = $request->input('notes');

        $chainUser_id = $this->filter();

        if ($chainUser_id != null) {
            $hotel->chain_id = $chainUser_id;
        }

        //if ($hotel->save()) {
            //$this->storeAllClient($hotel->id);
        //}

        if ($hotel->flag_new == 1) {
            $time = strtotime(date("Y-m-d"));
            $final = date("Y-m-d", strtotime("+1 month", $time));
            $hotel->date_end_flag_new = $final;
        }

        $hotel->save();

        //DESCRIPCION Y DIRECCION
        $this->saveTranslation($request->input("translDesc"), 'hotel', $hotel->id);
        // $this->saveTranslation($request->input("translSummary"), 'hotel', $hotel->id);
        $this->saveTranslation($request->input("translAddr"), 'hotel', $hotel->id);
        // $this->saveTranslation($request->input("translNotes"), 'hotel', $hotel->id);

        //CHANNELS
        $check_channels = 0;
        foreach ($request->input("channels") as $clave => $valor) {
            if ($valor['code'] != "") {
                $hotel->channels()->attach([
                    $clave => [
                        'code' => $valor['code'],
                        'state' => $valor['state'],
                        'type' => $valor['type'] ?? null
                    ]
                ]);
                $check_channels += 1;
            }
        }

        $alerts = $request->input("alerts");
        if($alerts == ''){
            return Response::json(['success' => false, 'message' => 'Ingrese los remarks']);
        }

        foreach ($request->input("alerts") as $year => $translations) {
            foreach ($translations as $language_id => $translation) {
               $hotelAlerta = new HotelAlert();
               $hotelAlerta->remarks = $translation['remarks'];
               $hotelAlerta->notes = $translation['notes'];
               $hotelAlerta->year = $year;
               $hotelAlerta->language_id = $language_id;
               $hotelAlerta->hotel_id = $hotel->id;
               $hotelAlerta->save();
            }
        }

        //AMENIDADES
        $hotel->amenity()->attach($request->input("translAmenity"));

        if ($hotel->hotel_type_id != "" && $hotel->hotelcategory_id != "" &&
            $hotel->hotelcategory_id != "" && $hotel->typeclass_id != "" &&
            $hotel->check_in_time != "" && $hotel->check_out_time != "" &&
            $hotel->stars != "") {
            ProgressBar::firstOrCreate(
                [
                    'slug' => 'hotel_progress_details',
                    'value' => 10,
                    'type' => 'hotel',
                    'object_id' => $hotel->id
                ]
            );
        }
        if ($hotel->latitude != "" && $hotel->longitude != "") {
            ProgressBar::firstOrCreate(
                [
                    'slug' => 'hotel_progress_location',
                    'value' => 10,
                    'type' => 'hotel',
                    'object_id' => $hotel->id
                ]
            );
        }
        if (count($request->input("translAmenity")) > 0) {
            ProgressBar::firstOrCreate(
                [
                    'slug' => 'hotel_progress_amenities',
                    'value' => 10,
                    'type' => 'hotel',
                    'object_id' => $hotel->id
                ]
            );
        }
        if ($check_channels > 0) {
            ProgressBar::firstOrCreate(
                [
                    'slug' => 'hotel_progress_channels',
                    'value' => 10,
                    'type' => 'hotel',
                    'object_id' => $hotel->id
                ]
            );
        }

        $classification_preference = $request->input("classification_preference");
        if($classification_preference == ''){
            return Response::json(['success' => false, 'message' => 'seleccione la clasificacion del hotel']);
        }

        foreach($classification_preference as $value){
            foreach ($value['dataClass'] as $value2) {
                $classification = new HotelTypeClass();
                $classification->year = $value['year'];
                $classification->typeclass_id = $value2['code'];
                $classification->hotel_id = $hotel->id;
                $classification->save();
            }

            $preferentials = new HotelPreferentials();
            $preferentials->year = $value['year'];
            $preferentials->value = $value['preference'];
            $preferentials->hotel_id = $hotel->id;
            $preferentials->save();

            $backups = new HotelBackup();
            $backups->year = $value['year'];
            $backups->value = isset($value['backup']) ? $value['backup'] : 0;
            $backups->hotel_id = $hotel->id;
            $backups->save();
        }

        if($send_to_mkt){
            $who = Auth::user()->name;
            $hotel_id = $hotel->id;
            $hotel_name = $hotel->name;
            $rate_id = "";
            $rate_name = "";
            $this->send_notification_notes_mkt($who, $hotel_name, $rate_name, $hotel_id, $rate_id);
        }

        return Response::json(['success' => true, 'object_id' => $hotel->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show($id, Request $request)
    {
        $lang = $request->input("lang");

        // dd($id);
        // die("...");

        $hotel = Hotel::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'hotel');
                $query->where('object_id', $id);
            }
        ])->with([
            'currency.translations' => function ($query) use ($lang) {
                $query->where('type', 'currency');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'hoteltype.translations' => function ($query) use ($lang) {
                $query->where('type', 'hoteltype');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'hotelcategory.translations' => function ($query) use ($lang) {
                $query->where('type', 'hotelcategory');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'typeclass.translations' => function ($query) use ($lang) {
                $query->where('type', 'typeclass');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'amenity.translations' => function ($query) use ($lang) {
                $query->where('type', 'amenity');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'country.translations' => function ($query) use ($lang) {
                $query->where('type', 'country');
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
            'city.translations' => function ($query) use ($lang) {
                $query->where('type', 'city');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'district.translations' => function ($query) use ($lang) {
                $query->where('type', 'district');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'zone.translations' => function ($query) use ($lang) {
                $query->where('type', 'zone');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'galeries' => function ($query) {
                $query->where('type', 'hotel');
                $query->where('slug', 'hotel_logo');
            }
        ])->with([
            'channels'
        ])
        ->with([
            'hotelpreferentials',
            'hotelbackups',
            'hoteltypeclass.translations' => function ($query) use ($lang) {
                //$query->where('hotel_id', $id);
                $query->where('type', 'typeclass');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with('alerts')->with('chains')->where('id', $id)->first();

        $form_alerts = [];
        $year = date('Y');
        $year_next = $year + 1;
        $years = [$year, $year_next];
        foreach($hotel->alerts as $alert){
            $form_alerts[$alert['year']][$alert['language_id']] = [
                'id' => $alert->id,
                'year' => $alert->year,
                'remarks' => $alert->remarks,
                'notes' => $alert->notes,
                'language_id' => $alert->language_id
            ];
        }


        if (count($form_alerts) == 0 || !isset($form_alerts[$year_next])) {
            $languages = Language::where('state', 1)->get();

            foreach ($years as $y) {
                foreach ($languages as $language) {
                    if (!isset($form_alerts[$y][$language->id])) {
                        $form_alerts[$y][$language->id] = [
                            'id' => '',
                            'year' => $y,
                            'remarks' => '',
                            'notes' => '',
                            'language_id' => $language->id
                        ];
                    }
                }
            }
        }

        return Response::json(['success' => true, 'data' => $hotel, 'form_alerts' => $form_alerts]);
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
        $send_to_mkt = $request->input('send_to_mkt');

        $hotel = Hotel::find($id);

        if ($hotel->flag_new != 1 and ((int)$request->input('flag_new') == 1)) {
            $time = strtotime(date("Y-m-d"));
            $final = date("Y-m-d", strtotime("+1 month", $time));
            $hotel->date_end_flag_new = $final;
        }

        $hotel->name = $request->input('name');
        $hotel->stars = $request->input('stars');
        $hotel->web_site = $request->input('web_site');
        $hotel->status = $request->input('status');
        $hotel->flag_new = $request->input('flag_new');
        // $hotel->preferential = $request->input('preferential') ? $request->input('preferential') : 0;
        $hotel->latitude = $request->input('latitude');
        $hotel->longitude = $request->input('longitude');
        $hotel->check_in_time = $request->input('check_in_time');
        $hotel->check_out_time = $request->input('check_out_time');
        // $hotel->typeclass_id = $request->input('typeclass_id');
        $hotel->chain_id = $request->input('chain_id');
        $hotel->currency_id = $request->input('currency_id');
        $hotel->hotel_type_id = $request->input('hotel_type_id');
        $hotel->hotelcategory_id = $request->input('hotelcategory_id');
        $hotel->country_id = $request->input('country_id');
        $hotel->state_id = $request->input('state_id');
        $hotel->city_id = $request->input('city_id');
        $hotel->district_id = $request->input('district_id');
        $hotel->zone_id = $request->input('zone_id');
        // $hotel->notes = $request->input('notes');
        $hotel->save();

        $hotel->amenity()->sync($request->input("translAmenity"));

        $check_channels = 0;
        foreach ($request->input("channels") as $clave => $valor) {
            $hotel->channels()->sync([
                $clave => [
                    'code' => $valor['code'],
                    'state' => $valor['state'],
                    'type' => $valor['type'] ?? null
                ]
            ], false);
            $check_channels += 1;

            if ($valor['id'] == 6) {

                $hyperguestProperty = HyperguestProperty::where('property_id', $valor['code'])->first();
                if (!$hyperguestProperty) {
                    if ($valor['state']) {
                        HyperguestProperty::create(['property_id' => $valor['code']]);
                    }
                } else {
                    if (!$valor['state']) {
                        $hyperguestProperty->delete();
                    }
                }

            }
        }

        $alerts = $request->input("alerts");
        if($alerts == ''){
            $alerts = [];
        }

        foreach ($alerts as $year => $translations) {
            foreach ($translations as $language_id => $translation) {

                $existingAlert = HotelAlert::where([
                    'hotel_id'    => $hotel->id,
                    'year'        => $year,
                    'language_id' => $language_id
                ])->first();

                if ($existingAlert) {
                    $hotelAlerta = $existingAlert;
                } else {
                    $hotelAlerta = new HotelAlert();
                    $hotelAlerta->hotel_id = $hotel->id;
                    $hotelAlerta->year = $year;
                    $hotelAlerta->language_id = $language_id;
                }
                $hotelAlerta->remarks = $translation['remarks'];
                $hotelAlerta->notes = $translation['notes'];
                $hotelAlerta->save();
            }
        }

        $this->saveTranslation($request->input("translDesc"), 'hotel', $hotel->id);
        // $this->saveTranslation($request->input("translSummary"), 'hotel', $hotel->id);
        $this->saveTranslation($request->input("translAddr"), 'hotel', $hotel->id);
        // $this->saveTranslation($request->input("translNotes"), 'hotel', $hotel->id);

        if ($hotel->hotel_type_id != "" && $hotel->hotelcategory_id != "" &&
            $hotel->hotelcategory_id != "" && $hotel->typeclass_id != "" &&
            $hotel->check_in_time != "" && $hotel->check_out_time != "" &&
            $hotel->stars != "") {
            ProgressBar::updateOrCreate(
                [
                    'slug' => 'hotel_progress_details',
                    'value' => 10,
                    'type' => 'hotel',
                    'object_id' => $hotel->id
                ]
            );
        }
        if ($hotel->latitude != "" && $hotel->longitude != "") {
            ProgressBar::updateOrCreate(
                [
                    'slug' => 'hotel_progress_location',
                    'value' => 10,
                    'type' => 'hotel',
                    'object_id' => $hotel->id
                ]
            );
        }
        if (count($request->input("translAmenity")) > 0) {
            ProgressBar::updateOrCreate(
                [
                    'slug' => 'hotel_progress_amenities',
                    'value' => 10,
                    'type' => 'hotel',
                    'object_id' => $hotel->id
                ]
            );
        }
        if ($check_channels > 0) {
            ProgressBar::updateOrCreate(
                [
                    'slug' => 'hotel_progress_channels',
                    'value' => 10,
                    'type' => 'hotel',
                    'object_id' => $hotel->id
                ]
            );
        }

        HotelPreferentials::where('hotel_id', '=', $hotel->id)->delete();
        HotelTypeClass::where('hotel_id', '=', $hotel->id)->delete();

        foreach($request->input("classification_preference") as $value){

            foreach ($value['dataClass'] as $value2) {

                $classification = new HotelTypeClass();
                $classification->year = $value['year'];
                $classification->typeclass_id = $value2['code'];
                $classification->hotel_id = $hotel->id;
                $classification->save();
            }

            $preferentials = new HotelPreferentials();
            $preferentials->year = $value['year'];
            $preferentials->value = $value['preference'];
            $preferentials->hotel_id = $hotel->id;
            $preferentials->save();

            HotelBackup::updateOrCreate(
                [
                    'hotel_id' => $hotel->id,
                    'year' => $value['year']
                ],
                [
                    'value' => isset($value['backup']) ? $value['backup'] : 0
                ]
            );

        }

        if($send_to_mkt){
            $who = Auth::user()->name;
            $hotel_id = $hotel->id;
            $hotel_name = $hotel->name;
            $rate_id = "";
            $rate_name = "";
            $this->send_notification_notes_mkt($who, $hotel_name, $rate_name, $hotel_id, $rate_id);
        }

        return Response::json(['success' => true, 'object_id' => $hotel->id]);
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

    public function hotels_rate_notes()
    {

        $hotels = Hotel::where('status', 1)
            ->with(['rates_plans'=>function($query){
                $query->where('status', 1);
                $query->with('translations_notes');
            }])
            ->with(['translations'=>function($query){
                $query->where('slug', "summary");
            }])
            ->with([
                'channels' => function ($query) {
                    $query->where('state', 1);
                }
            ])
            ->get();

        foreach($hotels as $hotel){
            // Agregando lógica de chancar notas siempre y cuando no tenga actualizado el texto hasta la fecha.
            if(strtotime($hotel->updated_at) < strtotime("2023-10-16 12:00:00")){ // Si es menor a la fecha que se subió el desarrollo
                $review_notes_es = '';
                $review_notes_en = '';
                $review_notes_pt = '';

                $n_rate = 0;
                foreach ($hotel->rates_plans as $rate_plan){
                    $review_notes_es .= $n_rate+1 . ' - "' . $rate_plan->name . '"' . "\n";
                    $review_notes_en .= $n_rate+1 . ' - "' . $rate_plan->name . '"' . "\n";
                    $review_notes_pt .= $n_rate+1 . ' - "' . $rate_plan->name . '"' . "\n";
                    foreach ($rate_plan->translations_notes as $translation_note){
                        if($translation_note->language_id == 1){ // es
                            $review_notes_es .= $translation_note->value . "\n";
                        }
                        if($translation_note->language_id == 2){ // es
                            $review_notes_en .= $translation_note->value . "\n";
                        }
                        if($translation_note->language_id == 3){ // es
                            $review_notes_pt .= $translation_note->value . "\n";
                        }
                    }

                    $n_rate++;
                }

                foreach ($hotel->translations as $hotel_translation){
                    if($hotel_translation->slug=="summary"){
                        if($hotel_translation->language_id==1){
                            $hotel_translation->value = $review_notes_es;
                        }
                        if($hotel_translation->language_id==2){
                            $hotel_translation->value = $review_notes_en;
                        }
                        if($hotel_translation->language_id==3){
                            $hotel_translation->value = $review_notes_pt;
                        }
                    }
                }

            }
        }
        return view('exports.hotels_rate_notes')->with(['hotels'=> $hotels]);
//        return Response::json(['hotels'=> $hotels]);

    }

    public function buildDataNotification($hotel, $translations)
    {
        $emails = ['marketing@limatours.com.pe'];
        $data = [
            'hotel_name' => $hotel->name,
            'hotel_id' => $hotel->id,
            'hotel' => $hotel,
            'translations' => $translations,
        ];
        Mail::to($emails)->send(new NotificationHotel($data));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $hotel = Hotel::find($id);

        $hotel->delete();

        //eliminacion de rooms del hotel

        $this->deleteTranslation('hotel', $id);

        $hotel->amenity()->detach();

        $hotel->channels()->detach();

        return Response::json(['success' => true]);
    }

    public function updateStatus($id, Request $request)
    {
        $hotel = Hotel::find($id);
        if ($request->input("status")) {
            $hotel->status = false;
        } else {
            $hotel->status = true;
        }
        $hotel->save();
        return Response::json(['success' => true]);
    }

    public function selectBox()
    {
        $hotels_database = Hotel::select('id', 'name')->get();
        $result = [];
        foreach ($hotels_database as $hotel) {
            array_push($result, ['text' => $hotel->name, 'value' => $hotel->id]);
        }
        return Response::json(['success' => true, 'data' => $result]);
    }

    public function hotelUpSelling(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');

        $hotel_id = $request->input('hotel_id');

        $hotelSeleccionado = Hotel::find($hotel_id);

        $hotels_database = Hotel::select(['id', 'name'])->where('state_id', $hotelSeleccionado->state_id)->where('id',
            '<>', $hotel_id);

        $hotels_frontend = [];

        $up_selling_database = UpSelling::select(['hotel_child_id'])->where('hotel_id', $hotel_id);

        if ($up_selling_database->count() > 0) {
            $hotels_database->whereNotIn('id', $up_selling_database);
        }
        $count = $hotels_database->count();

        if ($querySearch) {
            $hotels_database->where(function ($query) use ($querySearch) {
                $query->orWhere('name', 'like', '%' . $querySearch . '%');
            });
        }

        if ($paging === 1) {
            $hotels_database = $hotels_database->take($limit)->get();
        } else {
            $hotels_database = $hotels_database->skip($limit * ($paging - 1))->take($limit)->get();
        }

        if ($count > 0) {

            for ($j = 0; $j < count($hotels_database); $j++) {
                $hotels_frontend[$j]["up_selling_id"] = "";
                $hotels_frontend[$j]["hotel_child_id"] = $hotels_database[$j]["id"];
                $hotels_frontend[$j]["name"] = $hotels_database[$j]["name"];
                $hotels_frontend[$j]["hotel_id"] = $hotel_id;
                $hotels_frontend[$j]["selected"] = false;
            }
        }
        $data = [
            'data' => $hotels_frontend,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    public function hotelClient(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $business_region_id = $request->input('region_id');
        $client_id = $request->input('client_id');
        $period = $request->input('period');

        $regions_country_ids = BusinessRegionsCountry::where('business_region_id', $business_region_id)->pluck('country_id');
        $hotels_database = Hotel::select(['id', 'name'])->with(['country:id,iso'])->whereIn('country_id', $regions_country_ids)->where('status', 1);

        $hotels_frontend = [];

        $hotel_client_database = HotelClient::select(['hotel_id', 'period'])->where([
            'business_region_id'=> $business_region_id,
            'client_id' => $client_id,
            'period' => $period
        ]);

        $hotel_client_ids = $hotel_client_database->pluck('hotel_id');

        if ($hotel_client_database->count() > 0) {
            $hotels_database->whereNotIn('id', $hotel_client_ids);
        }
        $count = $hotels_database->count();

        if ($querySearch) {
            $hotels_database->where(function ($query) use ($querySearch) {
                $query->orWhere('name', 'like', '%' . $querySearch . '%');
            });
        }

        if ($paging === 1) {
            $hotels_database = $hotels_database->take($limit)->get();
        } else {
            $hotels_database = $hotels_database->skip($limit * ($paging - 1))->take($limit)->get();
        }

        if ($count > 0) {

            $markupHotels = MarkupHotel::select(['markup', 'hotel_id'])->where([
                'client_id' => $client_id,
                'period' => $period
            ])->get();

            for ($j = 0; $j < count($hotels_database); $j++) {
                $markup = "";
                foreach ($markupHotels as $markupHotel) {
                    if ($markupHotel->hotel_id == $hotels_database[$j]["id"]) {
                        $markup = $markupHotel->markup;
                    }
                }

                $hotels_frontend[$j]["id"] = "";
                $hotels_frontend[$j]["hotel_id"] = $hotels_database[$j]["id"];
                $hotels_frontend[$j]["name"] = $hotels_database[$j]["name"];
                $hotels_frontend[$j]["country_iso"] = isset($hotels_database[$j]["country"]["iso"]) ? $hotels_database[$j]["country"]["iso"] : null;                
                $hotels_frontend[$j]["client_id"] = $client_id;
                $hotels_frontend[$j]["markup"] = $markup;
                $hotels_frontend[$j]["selected"] = false;
            }
        }
        $data = [
            'data' => $hotels_frontend,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    public function getUbigeoHotel($lang)
    {

        $hotel = Hotel::with([
            'district.translations' => function ($query) use ($lang) {
                $query->where('type', 'district');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'city.translations' => function ($query) use ($lang) {
                $query->where('type', 'city');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'city.state.translations' => function ($query) use ($lang) {
                $query->where('type', 'state');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'city.state.country.translations' => function ($query) use ($lang) {
                $query->where('type', 'country');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->get();

        /*
                $hotel = Hotel::with([
                    'district.translations' => function ($query) use ($lang) {
                        $query->where('type', 'district');
                        $query->whereHas('language', function ($q) use ($lang) {
                            $q->where('iso', $lang);
                        });
                    }
                ])->with([
                    'district.city.translations' => function ($query) use ($lang) {
                        $query->where('type', 'city');
                        $query->whereHas('language', function ($q) use ($lang) {
                            $q->where('iso', $lang);
                        });
                    }
                ])->with([
                    'district.city.state.translations' => function ($query) use ($lang) {
                        $query->where('type', 'state');
                        $query->whereHas('language', function ($q) use ($lang) {
                            $q->where('iso', $lang);
                        });
                    }
                ])->with([
                    'district.city.state.country.translations' => function ($query) use ($lang) {
                        $query->where('type', 'country');
                        $query->whereHas('language', function ($q) use ($lang) {
                            $q->where('iso', $lang);
                        });
                    }
                ])->get();*/

        return Response::json(['success' => true, 'data' => $hotel]);
    }

    public function getHotelName($id)
    {
        $hotel = Hotel::find($id);
        return Response::json(['success' => true, 'name' => $hotel->name]);
    }

    public function updateFavorite(Request $request)
    {
        $hotel_id = $request->post('hotel_id');

        $hotel_favorite_exists = HotelFavoriteUser::where('user_id', Auth::user()->id)->where('hotel_id',
            $hotel_id)->get();

        if ($hotel_favorite_exists->count() > 0) {
            $hotel_favorite = HotelFavoriteUser::find($hotel_favorite_exists[0]["id"]);

            if ($hotel_favorite->favorite == 0) {
                $hotel_favorite->favorite = 1;
            } else {
                $hotel_favorite->favorite = 0;
            }
            $hotel_favorite->save();

            return response()->json([
                'message' => "Has Agregado este Hotel Como Favorito",
                "favorite" => $hotel_favorite->favorite
            ]);
        } else {
            $hotel_favorite_new = new HotelFavoriteUser();
            $hotel_favorite_new->user_id = Auth::user()->id;
            $hotel_favorite_new->hotel_id = $hotel_id;
            $hotel_favorite_new->favorite = 1;
            $hotel_favorite_new->save();

            return response()->json([
                'message' => "Has Agregado este Hotel Como Favorito",
                "favorite" => $hotel_favorite_new->favorite
            ]);
        }

    }


    public function updateRatesInPackages($hotel_id)
    {

        $package_services = PackageService::where('type', 'hotel')
            ->where('object_id', $hotel_id)
            ->get();

        $_verify_id_category = [];
        $_n = 0;

        foreach ($package_services as $package_service) {
            if (!isset($_verify_id_category[$package_service->package_plan_rate_category_id])) {
                $_verify_id_category[$package_service->package_plan_rate_category_id] = true;
                $_n++;
                $this->calculatePricePackage($package_service->package_plan_rate_category_id);
            }
        }

        return Response::json([
            'success' => true,
            'package_plan_rate_categories_ids' => $_verify_id_category,
            'total' => $_n
        ]);

    }

    public function getHotelsMultimediaReport(Request $request)
    {
        $code_hotel = $request->get('code_hotel');

        if ($code_hotel != null) {
            $hotel_id = ChannelHotel::where('channel_id', 1)->where('code', $code_hotel)->first();
            if ($hotel_id != null) {
                $hotel_id = $hotel_id->hotel_id;
            }

            $hotels = Hotel::where('status', 1)->with([
                'rooms' => function ($query) {
                    $query->where('state', 1);
                    $query->with([
                        'translations' => function ($query) {
                            $query->where('language_id', 1);
                        }
                    ]);
                }
            ])->with([
                'channels' => function ($query) {
                    $query->where('channel_id', 1);
                }
            ])->where('id', $hotel_id)->paginate(2);

            return \response()->json($hotels);
        }

        $hotels = Hotel::where('status', 1)->with([
            'rooms' => function ($query) {
                $query->where('state', 1);
                $query->with([
                    'translations' => function ($query) {
                        $query->where('language_id', 1);
                    }
                ]);
            }
        ])->with([
            'channels' => function ($query) {
                $query->where('channel_id', 1);
            }
        ])->paginate(2);

        return \response()->json($hotels);
    }


    public function orders_rate(Request $request)
    {
        $filter_city_id = $request->get('filter_city_id');
        $filter_subcategory_id = $request->get('filter_subcategory_id');
        $type = $request->get('type');

        if ($filter_subcategory_id != null) {
            $services = Hotel::where('city_id', $filter_city_id)
                ->with('channel')
                ->where('typeclass_id', $filter_subcategory_id)
                ->whereHas('rates_plans', function ($q) {
                    $q->where('rate', 1);
                })
                ->where('status', 1);
            if ($type == 'rate') {
                $services = $services->orderBy('rate_order')->paginate(10);
            } else {
                $services = $services->orderBy('order')->paginate(10);
            }

        } else {
            $services = Hotel::where('city_id', $filter_city_id)
                ->with('channel')
                ->whereHas('rates_plans', function ($q) {
                    $q->where('rate', 1);
                })
                ->where('status', 1);
            if ($type == 'rate') {
                $services = $services->orderBy('rate_order')->paginate(10);
            } else {
                $services = $services->orderBy('order')->paginate(10);
            }
        }

        return \response()->json($services);
    }

    public function update_order(Request $request)
    {
        $hotels = (array)$request->__get('hotels');
        // $city_id = $request->input('city_id');
        $type = $request->input('type');
        // $service_sub_category_id = $request->input('service_sub_category_id');

        foreach ($hotels as $key => $value) {
            $rate_order_hotel = Hotel::find($value['id']);

            if ($type == 'rate') {
                $order = (isset($value['rate_order_new'])) ? $value['rate_order_new'] : $value['rate_order'];
                $rate_order_hotel->rate_order = $order;
            } else {
                $order = (isset($value['order_new'])) ? $value['order_new'] : $value['order'];
                $rate_order_hotel->order = $order;
            }

            $rate_order_hotel->save();
        }

//        DB::transaction(function () use ($request) {

        /*
        if ($rate_order_hotel->save()) {
            $rate_order_cities = Hotel::where('typeclass_id', $service_sub_category_id)
                ->where('city_id', $city_id)
                ->whereHas('rates_plans', function ($q) {
                    $q->where('rate', 1);
                })
                ->where('status', 1);
            if ($type == 'rate') {
                $rate_order_cities = $rate_order_cities->orderBy('rate_order');
            } else {
                $rate_order_cities = $rate_order_cities->orderBy('order');
            }
            $rate_order_cities = $rate_order_cities->orderBy('updated_at', 'desc')->get();

            foreach ($rate_order_cities as $k => $rate_order_hotel) {
                if ($type == 'rate') {
                    $rate_order_hotel->rate_order = $k + 1;
                } else {
                    $rate_order_hotel->order = $k + 1;
                }
                $rate_order_hotel->save();
            }

        }
        */
//        });
        return \response()->json(["success" => true]);
    }

    public function get_uses($id)
    {
        $packages = $this->get_service_uses($id, 'hotel');

        return Response::json(['success' => true, 'packages' => $packages]);
    }

    public function report_uses($id, Request $request)
    {

        try {

            $data = $request->input('data');

            $data["user"] = Auth::user()->name . ' (' . Auth::user()->code . ')';

            $mail = mail::to("producto@limatours.com.pe");
            $mail->cc(["neg@limatours.com.pe", "kams@limatours.com.pe", "qr@limatours.com.pe"]);

            $mail->send(new \App\Mail\NotificationHotelStatus($data));

            $new_deactivatable_entity = new DeactivatableEntity();
            $new_deactivatable_entity->entity = "App\Hotel";
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

    public function notify_new_hotel(Request $request, $_hotel)
    {
        try {
            $hotel = Hotel::with(['typeclass.translations'])->where('id', '=', $_hotel)->first();

            $mail = mail::to("qr@limatours.com.pe")
                ->cc("kams@limatours.com.pe")
                ->cc('neg@limatours.com.pe')
                ->bcc(["kluizsv@gmail.com"]);

            $data = [
                'hotel' => $request->__get('hotel'),
                'type' => $hotel->typeclass,
                'lang' => 1
            ];

            $mail->send(new \App\Mail\NotificationPromotion($data, 'hotel'));

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

            return Response::json(['success' => true, $data]);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getConfigurations($id, Request $request)
    {
        $hotels = Hotel::with([
            'channel' => function ($query) {
                $query->select(['id', 'code', 'hotel_id', 'channel_id']);
                $query->where('channel_id', 1);
            }
        ])->where('id', $id)->first(['id', 'name']);

        return Response::json(['success' => true, 'data' => $hotels]);
    }

    public function getChannels($id){
        $hotels = Hotel::with('channels')->where('id', $id)->first(['id', 'name']);
        return Response::json(['success' => true, 'data' => $hotels->channels ?? []]);
    }
}

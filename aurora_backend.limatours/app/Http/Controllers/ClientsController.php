<?php

namespace App\Http\Controllers;

use App\BusinessRegionsCountry;
use App\User;
use App\Client;
use App\Market;
use App\Markup;
use App\Country;
use App\Service;
use App\Language;
use Carbon\Carbon;
use App\RatesPlans;
use App\HotelClient;
use App\MarkupHotel;
use App\MarkupTrain;
use App\ServiceRate;
use App\TrainClient;
use App\Translation;
use App\ClientMailing;
use App\MarkupService;
use App\ServiceClient;
use App\ClientRatePlan;
use App\ClientExecutive;
use App\ClientConfiguration;
use App\Hotel;
use App\RatePlanAssociation;
use Illuminate\Http\Request;
use App\ServiceRateAssociation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\PolicyCancellationParameter;
use Illuminate\Support\Facades\Auth;
use App\Jobs\DisabledRateClientService;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Jobs\DisabledRatesPlansClientHotel;

class ClientsController extends Controller
{

    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $limit = $request->input('limit');
            $querySearch = $request->input('query');
            $search = $request->input('search');
            $status = $request->input('status');
            $market = $request->input('market');

            $clients = Client::search($search)
                ->with([
                    'markups' => function ($query) {
                        $query->select(['id', 'hotel', 'service', 'status', 'client_id', 'created_at', 'client_id']);
                        $query->where("status", 1);
                        $query->where("period", '>=', Carbon::now()->format('Y'));
                        $query->orderBy("period", "desc");
                    }
                ])
                ->with([
                    'client_executives' => function ($query) {
                        $query->select('users.id', 'users.name', 'users.email', 'users.code');
                    },
                ])
                ->with([
                    'markets' => function ($query) {
                        $query->select(['id', 'name', 'code']);
                    }
                ])
                ->with([
                    'businessRegions' => function ($query) {
                        $query->select([
                            'business_region_client.id',
                            'business_region_client.client_id',
                            'business_region_client.business_region_id',
                            'business_region_client.created_at as pivot_created_at',
                            'business_region_client.deleted_at'
                        ])
                            ->whereNull('business_region_client.deleted_at')
                            ->orderBy('business_region_client.created_at', 'asc');
                    }
                ])
                ->orderBy('name', 'asc');

            if (!empty($market) and $market != "null") {
                $clients = $clients->where('market_id', $market);
            }


            if ($querySearch) {
                $clients->where(function ($query) use ($querySearch) {
                    $query->orWhere('code', 'like', '%' . $querySearch . '%');
                    $query->orWhere('name', 'like', '%' . $querySearch . '%');
                });
            }

            if (!empty($status)) {
                $clients = $clients->where('status', $status);
            }


            $count = $clients->count();
            $clients = $clients->paginate($limit);
            $clients = $clients->items();

            foreach ($clients as $item) {
                $item['markup_service'] = 0;
                $item['markup_hotel'] = 0;
                foreach ($item['markups'] as $markup) {
                    if ($markup['period'] == Carbon::now()->year) {
                        $item['markup_service'] = $markup['service'];
                        $item['markup_hotel'] = $markup['hotel'];
                        break;
                    }
                }
                if ($item['markup_service'] == 0 && count($item['markups']) > 0) {
                    $item['markup_service'] = $item['markups'][0]['service'];
                    $item['markup_hotel'] = $item['markups'][0]['hotel'];
                }

                $item['first_business_region'] = null;
                if (count($item['businessRegions']) > 0) {
                    $item['first_business_region'] = $item['businessRegions'][0];
                }
            }

            $data = [
                'data' => $clients,
                'count' => $count,
                'success' => true
            ];
            return Response::json($data);
        } catch (\Exception $ex) {
            return Response::json(['error' => $ex->getMessage(), 'success' => false]);
        } catch (\Error $ex) {
            return Response::json(['error' => $ex->getMessage(), 'success' => false]);
        }
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
            'name' => 'required',
            'code' => 'required',
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

            $market_code = Market::find($request->input('market_id'))->code;
            $country_iso = Country::find($request->input('country_id'))->iso_ifx;
            // COMENTADO PARA HACER PRUEBAS
            $client = new \GuzzleHttp\Client(["verify" => false]);
            $response = $client->request(
                'POST',
                config('services.stella.domain') . 'api/v1/clients/receptive',
                [
                    "json" => [
                        'codigo' => $request->input('code'),
                        'codcla' => $request->input('classification_code'),
                        'codven' => strtoupper($request->input('executive_code')),
                        'name' => $request->input('name'),
                        'razon' => $request->input('business_name'),
                        'address' => $request->input('address'),
                        'codciu' => $request->input('city_code'),
                        'ciudad' => $request->input('city_name'),
                        'postal' => $request->input('postal_code'),
                        'codgru' => $market_code,
                        'pais' => $country_iso,
                        'phone' => $request->input('phone'),
                        'comis1' => '-' . $request->input('general_markup'), // in negative
                        'ruc' => $request->input('ruc'),
                        'web' => $request->input('web'),
                    ]
                ]
            );
            $response = json_decode($response->getBody()->getContents());

            if ($response->success) {

                $find_c = Client::where('code', $request->input('code'))->count();
                if ($find_c > 0) {
                    return Response::json(['success' => false, 'response' => "Code already exist"]);
                }
                DB::beginTransaction();
                try {
                    $client = new Client();
                    $client->code = $request->input('code');
                    $client->name = $request->input('name');
                    $client->business_name = $request->input('business_name');
                    $client->address = $request->input('address');
                    $client->ruc = $request->input('ruc');
                    $client->postal_code = $request->input('postal_code');
                    $client->web = $request->input('web');
                    $client->anniversary = ($request->input('anniversary')) ? convertDate(
                        $request->input('anniversary'),
                        '/',
                        '-',
                        1
                    ) : '';
                    $client->phone = $request->input('phone');
                    $client->email = $request->input('email');
                    $client->use_email = $request->input('use_email');
                    $client->credit_line = $request->input('credit_line');
                    $client->have_credit = (int)$request->input('have_credit');
                    $client->status = (int)$request->input('status');
                    $client->market_id = $request->input('market_id');
                    $client->classification_code = $request->input('classification_code');
                    $client->classification_name = $request->input('classification_name');
                    $client->executive_code = strtoupper($request->input('executive_code'));
                    $client->general_markup = $request->input('general_markup');
                    $client->country_id = $request->input('country_id');
                    $client->city_code = $request->input('city_code');
                    $client->city_name = $request->input('city_name');
                    $client->language_id = $request->input('language_id');
                    $client->logo = $request->input('logo');
                    $client->ecommerce = $request->input('ecommerce');
                    $client->allow_direct_passenger_creation = $request->input('allow_direct_passenger_creation');
                    $client->bdm = $request->input('bdm');
                    $client->bdm_id = $request->input('bdm');
                    $client->commission_status = $request->input('commission_status') ? 1 : 0;
                    $client->commission = $request->input('commission_status') ? $request->input('commission') : NULL;
                    $client->save();

                    $hotel_allowed_on_request = $request->has('hotel_allowed_on_request') ? (bool)$request->input('hotel_allowed_on_request') : true;

                    $client_id = $client->id;

                    $client_mailing = new ClientMailing();
                    $client_mailing->clients_id = $client_id;
                    $client_mailing->weekly = 1;
                    $client_mailing->day_before = 1;
                    $client_mailing->daily = 1;
                    $client_mailing->survey = 1;
                    $client_mailing->whatsapp = 1;
                    $client_mailing->status = 1;
                    $client_mailing->created_at = Carbon::now();
                    $client_mailing->updated_at = Carbon::now();
                    $client_mailing->save();

                    $this->set_markups_configurations($client_id, 3, $request->input('general_markup'));
                    $this->saveClientConfiguration($client_id, $hotel_allowed_on_request);

                    //Todo Job Bloqueo de tarifas de hoteles
                    DisabledRatesPlansClientHotel::dispatch($client);
                    //Todo Job Bloqueo de tarifas de servicios
                    DisabledRateClientService::dispatch($client);
                    DB::commit();
                    // all good
                } catch (\Exception $e) {
                    DB::rollback();
                }
            } else {
                return Response::json(['success' => false, 'response' => $response]);
            }
        }

        return Response::json(['success' => true, 'object_id' => $client->id]);
    }

    public function store_rate_plans_disabled($client)
    {

        $rate_plans = RatesPlans::where('status', 1)->get();

        foreach ($rate_plans as $rate_plan) {

            $country_ids = [];
            $client_ids = [];
            $market_ids = [];

            $associations = RatePlanAssociation::where('rate_plan_id', $rate_plan["id"])->get();

            foreach ($associations as $association) {

                if ($association["entity"] == "Market") {
                    array_push($market_ids, $association["object_id"]);
                }
                if ($association["entity"] == "Client") {
                    array_push($client_ids, $association["object_id"]);
                }
                if ($association["entity"] == "Country") {
                    array_push($country_ids, $association["object_id"]);
                }
            }

            $id_clients = Client::select('id')->where('status', 1)
                ->when($country_ids, function ($query) use ($country_ids) {
                    return $query->whereNotIn('country_id', $country_ids);
                })->when($market_ids, function ($query) use ($market_ids) {
                    return $query->whereNotIn('market_id', $market_ids);
                })->when($client_ids, function ($query) use ($client_ids) {
                    return $query->whereNotIn('id', $client_ids);
                })->where('id', $client->id)->pluck('id')->toArray();

            foreach ($id_clients as $id_client) {

                ClientRatePlan::insert([
                    "period" => 2021,
                    "client_id" => $id_client,
                    "rate_plan_id" => $rate_plan["id"],
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);
                ClientRatePlan::insert([
                    "period" => 2022,
                    "client_id" => $id_client,
                    "rate_plan_id" => $rate_plan["id"],
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);
                ClientRatePlan::insert([
                    "period" => 2023,
                    "client_id" => $id_client,
                    "rate_plan_id" => $rate_plan["id"],
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);
            }
        }
    }

    public function verifyExist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|unique:clients|min:3|alpha_num',
        ], [
            'required' => 'El código es requerido',
            'min' => 'El código debe tener al menos 3 caracteres',
            'alpha_num' => 'El código solo debe tener números o letras',
            'unique' => 'El código ya existe'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'errors' => $validator->errors()]);
        } else {
            return Response::json(['success' => true]);
        }
    }

    public function storeRatePlan($client_id)
    {
        DB::transaction(function () use ($client_id) {
            $rate_plans_ids = DB::table('rates_plans')->where('rates_plans_type_id', 1)->where(
                'deleted_at',
                null
            )->get();
            foreach ($rate_plans_ids as $rate_plans_id) {
                DB::table('client_rate_plans')->insert([
                    'period' => Carbon::now()->year,
                    'client_id' => $client_id,
                    'rate_plan_id' => $rate_plans_id->id,
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ]);
            }
        });
    }

    public function store_rate_plans_associations($client_id, $client_country_id, $client_market_id)
    {

        // Tener en cuenta ya no poner los de storeRatePlan
        DB::transaction(function () use ($client_id, $client_country_id, $client_market_id) {

            $already_storeds = [];

            // PAISES
            $client_rate_plans_current_ids = ClientRatePlan::where('client_id', $client_id)->pluck('rate_plan_id');
            $country_rate_plan_associations = RatePlanAssociation::where('entity', 'Country')
                ->where('object_id', '!=', $client_country_id)
                ->whereNotIn('rate_plan_id', $client_rate_plans_current_ids)
                ->get();
            foreach ($country_rate_plan_associations as $rate_plan_association) {
                if (!isset($already_storeds[$rate_plan_association->rate_plan_id])) {
                    DB::table('client_rate_plans')->insert([
                        'period' => Carbon::now()->year,
                        'client_id' => $client_id,
                        'rate_plan_id' => $rate_plan_association->rate_plan_id,
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                    ]);
                    $already_storeds[$rate_plan_association->rate_plan_id] = true;
                }
            }
            // / PAISES

            // MARKET
            $client_rate_plans_current_ids = ClientRatePlan::where('client_id', $client_id)->pluck('rate_plan_id');
            $country_rate_plan_associations = RatePlanAssociation::where('entity', 'Market')
                ->where('object_id', '!=', $client_market_id)
                ->whereNotIn('rate_plan_id', $client_rate_plans_current_ids)
                ->get();
            foreach ($country_rate_plan_associations as $rate_plan_association) {
                if (!isset($already_storeds[$rate_plan_association->rate_plan_id])) {
                    DB::table('client_rate_plans')->insert([
                        'period' => Carbon::now()->year,
                        'client_id' => $client_id,
                        'rate_plan_id' => $rate_plan_association->rate_plan_id,
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                    ]);
                    $already_storeds[$rate_plan_association->rate_plan_id] = true;
                }
            }
            // / MARKET

            // CLIENT
            $client_rate_plans_current_ids = ClientRatePlan::where('client_id', $client_id)->pluck('rate_plan_id');
            $country_rate_plan_associations = RatePlanAssociation::where('entity', 'Client')
                ->where('object_id', '!=', $client_id)
                ->whereNotIn('rate_plan_id', $client_rate_plans_current_ids)
                ->get();
            foreach ($country_rate_plan_associations as $rate_plan_association) {
                if (!isset($already_storeds[$rate_plan_association->rate_plan_id])) {
                    DB::table('client_rate_plans')->insert([
                        'period' => Carbon::now()->year,
                        'client_id' => $client_id,
                        'rate_plan_id' => $rate_plan_association->rate_plan_id,
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                    ]);
                    $already_storeds[$rate_plan_association->rate_plan_id] = true;
                }
            }
            // / CLIENT

        });
    }

    public function set_markups_configurations($client_id, $years, $markup)
    {
        for ($i = 0; $i < $years; $i++) {
            $year = Carbon::now()->year + $i;
            $new_markup = new Markup();
            $new_markup->period = $year;
            $new_markup->hotel = $markup;
            $new_markup->service = $markup;
            $new_markup->status = 1;
            $new_markup->client_id = $client_id;
            $new_markup->save();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id, Request $request)
    {
        $lang = $request->input("lang");

        $client = Client::with('markets')->with([
            'galeries' => function ($query) {
                $query->where('type', 'client');
                $query->where('slug', 'client_logo');
            }
        ])->with([
            'countries.translations' => function ($query) use ($lang) {
                $query->where('type', 'country');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->with([
            'configuration' => function ($query) {
                $query->select([
                    'id',
                    'client_id',
                    'hotel_allowed_on_request',
                    'service_allowed_on_request',
                ]);
            }
        ])
            ->with('languages')
            ->with('businessRegions')
            ->with('bdm')
            ->where('id', $id)->first();

        return Response::json(['success' => true, 'data' => $client]);
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
            'name' => 'required',
            'code' => 'required',
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

            $market_code = Market::find($request->input('market_id'))->code;
            $country_iso = Country::find($request->input('country_id'))->iso_ifx;
            // COMENTADO PARA HACER PRUEBAS
            // dd(json_encode([
            //             'codigo' => $request->input('code'),
            //             'codcla' => $request->input('classification_code'),
            //             'codven' => strtoupper($request->input('executive_code')),
            //             'name' => $request->input('name'),
            //             'razon' => $request->input('business_name'),
            //             'address' => $request->input('address'),
            //             'codciu' => $request->input('city_code'),
            //             'ciudad' => $request->input('city_name'),
            //             'postal' => $request->input('postal_code'),
            //             'codgru' => $market_code,
            //             'pais' => $country_iso,
            //             'phone' => $request->input('phone'),
            //             'comis1' => '-' . $request->input('general_markup'), // in negative
            //             'ruc' => $request->input('ruc'),
            //             'web' => $request->input('web'),
            //         ]));
            //["verify"=>false]
            $client = new \GuzzleHttp\Client(["verify" => false]);
            $response = $client->request(
                'PUT',
                config('services.stella.domain') . 'api/v1/clients/receptive',
                [
                    "json" => [
                        'codigo' => $request->input('code'),
                        'codcla' => $request->input('classification_code'),
                        'codven' => strtoupper($request->input('executive_code')),
                        'name' => $request->input('name'),
                        'razon' => $request->input('business_name'),
                        'address' => $request->input('address'),
                        'codciu' => $request->input('city_code'),
                        'ciudad' => $request->input('city_name'),
                        'provin' => '',
                        'postal' => $request->input('postal_code'),
                        'codgru' => $market_code,
                        'pais' => $country_iso,
                        'phone' => $request->input('phone'),
                        'comis1' => '-' . $request->input('general_markup'), // in negative
                        'ruc' => $request->input('ruc'),
                        'web' => $request->input('web'),
                    ]
                ]
            );
            $response = json_decode($response->getBody()->getContents());

            if ($response->success) {

                $find_c = Client::where('code', $request->input('code'))->where('id', '!=', $id)->where(
                    'status',
                    '1'
                )->count();
                if ($find_c > 0) {
                    return Response::json(['success' => false, 'response' => "Code already exist"]);
                }

                try {
                    $client = Client::find($id);
                    $client->code = $request->input('code');
                    $client->name = $request->input('name');
                    $client->business_name = $request->input('business_name');
                    $client->address = $request->input('address');
                    $client->ruc = $request->input('ruc');
                    $client->postal_code = $request->input('postal_code');
                    $client->web = $request->input('web');
                    $client->anniversary = ($request->input('anniversary')) ? convertDate(
                        $request->input('anniversary'),
                        '/',
                        '-',
                        1
                    ) : '';
                    $client->phone = $request->input('phone');
                    $client->email = $request->input('email');
                    $client->use_email = $request->input('use_email');
                    $client->credit_line = $request->input('credit_line');
                    $client->have_credit = (int)$request->input('have_credit');
                    $client->status = (int)$request->input('status');
                    $client->market_id = $request->input('market_id');
                    $client->classification_code = $request->input('classification_code');
                    $client->classification_name = $request->input('classification_name');
                    $client->executive_code = strtoupper($request->input('executive_code'));
                    $client->general_markup = $request->input('general_markup');
                    $client->country_id = $request->input('country_id');
                    $client->city_code = $request->input('city_code');
                    $client->city_name = $request->input('city_name');
                    $client->language_id = $request->input('language_id');
                    $client->logo = $request->input('logo');
                    $client->ecommerce = $request->input('ecommerce');
                    $client->allow_direct_passenger_creation = $request->input('allow_direct_passenger_creation');
                    $client->bdm = $request->input('bdm');
                    $client->bdm_id = $request->input('bdm');
                    $client->commission_status = $request->input('commission_status') ? 1 : 0;
                    $client->commission = $request->input('commission_status') ? $request->input('commission') : NULL;
                    $client->save();
                    $hotel_allowed_on_request = $request->has('hotel_allowed_on_request') ? (bool)$request->input('hotel_allowed_on_request') : true;
                    $this->saveClientConfiguration($id, $hotel_allowed_on_request);
                } catch (\Exception $e) {
                    return Response::json(['success' => false, 'response' => $e->getMessage()]);
                }
            } else {
                return Response::json(['success' => false, 'response' => $response]);
            }
        }


        return Response::json(['success' => true, 'object_id' => $client->id]);
    }

    public function updateStatus($id, Request $request)
    {
        $client = Client::find($id);
        if ($request->input("status")) {
            $client->status = 0;
        } else {
            $client->status = 1;
        }
        $client->save();
        return Response::json(['success' => true]);
    }

    public function selectBox(Request $request)
    {
        $clients = Client::select('id', 'name')
            ->where('status', 1)
            ->whereDoesntHave('fromHotel', function ($query) use ($request) {
                $query->where(['hotel_id' => $request->input('hotel_id'), 'period' => $request->input('period')]);
            })
            ->get();

        $result = [];
        foreach ($clients as $client) {
            array_push($result, ['text' => $client->name, 'value' => $client->id]);
        }
        return Response::json(['success' => true, 'data' => $result]);
    }

    public function selectBoxByName(Request $request)
    {
        $querySearch = strtoupper($request->get('query'));
        $countries_id = ($request->has('countries')) ? $request->get('countries') : [];
        $except_country = $request->get('except_country');

        $clients = Client::select('id', 'code', 'name', 'country_id')
            ->where('status', 1)
            ->where(function ($query) use ($querySearch) {
                $query->orWhere('code', 'like', '%' . $querySearch . '%');
                $query->orWhere('name', 'like', '%' . $querySearch . '%');
                $query->orWhere('id', $querySearch);
            });


        if (count($countries_id) > 0 and $except_country == "false") {

            $clients = $clients->whereIn('country_id', $countries_id);
        }

        if (count($countries_id) > 0 and $except_country == "true") {
            $clients = $clients->whereNotIn('country_id', $countries_id);
        }

        $clients = $clients->take(10)->get();

        foreach ($clients as $client) {
            $client->name = '(' . $client->code . ') ' . $client->name;
        }

        return Response::json(['success' => true, 'data' => $clients]);
    }

    public function selectBoxByServices(Request $request)
    {
        $clients = Client::select('id', 'name')
            ->where('status', 1)
            ->whereHas('fromService', function ($query) use ($request) {
                $query->where('service_id', $request->input('service_id'));
            })
            ->get();

        $result = [];
        foreach ($clients as $client) {
            array_push($result, ['text' => $client->name, 'value' => $client->id]);
        }
        return Response::json(['success' => true, 'data' => $result]);
    }

    public function selectBoxByTrains(Request $request)
    {
        $clients = Client::select('id', 'name')
            ->where('status', 1)
            ->whereHas('fromTrain', function ($query) use ($request) {
                $query->where('train_template_id', $request->input('train_template_id'));
            })
            ->get();

        $result = [];
        foreach ($clients as $client) {
            array_push($result, ['text' => $client->name, 'value' => $client->id]);
        }
        return Response::json(['success' => true, 'data' => $result]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $client = Client::find($id);
        $client->delete();
        return Response::json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroyParameters($id)
    {
        $parameter = PolicyCancellationParameter::find($id);
        $parameter->delete();
        return Response::json(['success' => true]);
    }

    public function clientsByExecutive()
    {
        $clients = ClientExecutive::select('client_id', 'user_id')
            ->whereHas('client', function ($query) {
                $query->where('status', 1);
            })->with([
                'client' => function ($query) {
                    $query->select('id', 'name');
                    $query->where('status', 1);
                }
            ])->where('user_id', Auth::user()->id)->where('status', 1)->get();

        return Response::json(['success' => true, 'clients' => $clients]);
    }

    public function clientsByExecutiveFront(Request $request)
    {
        $user_id = ($request->input('user_id')) ? $request->input('user_id') : Auth::user()->id;
        $queryCustom = $request->get('queryCustom');
        $limit = $request->has('limit') ? $request->get('limit') : 20;


        $user = User::with([
            'clients' => function ($query) use ($queryCustom, $limit) {
                if (!empty($queryCustom)) {
                    $query->where(function ($query) use ($queryCustom) {
                        if (is_numeric($queryCustom)) {
                            $query->where('clients.id', '=', $queryCustom);
                        } else {
                            $query->where('name', 'like', "%$queryCustom%");
                            $query->orWhere('code', 'like', "%$queryCustom%");
                        }
                    });
                }
                $query->limit($limit);
                $query->where('status', 1);
            }
        ])->where('id', $user_id)->where('status', 1)->first();


        $result = [];

        if (count($user->clients) > 0) {
            foreach ($user->clients as $client) {
                array_push($result, [
                    'label' => '(' . $client->code . ') ' . $client->name,
                    'code' => $client->id,
                    'allow_direct_passenger_create' => $client->allow_direct_passenger_creation,
                    'client_code' => $client->code
                ]);
            }
        }


        return Response::json(['success' => true, 'data' => $result]);
    }


    public function associateExecutives(Request $request)
    {

        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $market = $request->input('market');
        $client_id = $request->input('client_id');
        $region_id = $request->input('region_id');


        $users_database = Client::find($client_id)->users()
            ->where('users.user_type_id', 3)
            ->where('users.status', 1)
            ->whereHas('businessRegions', function ($query) use ($region_id) {
                $query->where('business_region_id', $region_id);
            })
            ->whereDoesntHave('clientExecutive', function ($query) use ($client_id, $region_id) {
                $query->where('client_id', $client_id);
                $query->where('business_region_id', $region_id);
            });


        $users_frontend = [];


        $count = $users_database->count();

        if ($querySearch) {

            $users_database->where(function ($query) use ($querySearch) {
                $query->orWhere('users.name', 'like', '%' . $querySearch . '%');
                $query->orWhere('users.code', 'like', '%' . $querySearch . '%');
            });
        }

        if ($paging === 1) {
            $users_database = $users_database->take($limit)->get();
        } else {
            $users_database = $users_database->skip($limit * ($paging - 1))->take($limit)->get();
        }

        if ($count > 0) {
            for ($j = 0; $j < count($users_database); $j++) {
                $users_frontend[$j]["id"] = "";
                $users_frontend[$j]["user_id"] = $users_database[$j]["id"];
                $users_frontend[$j]["name"] = $users_database[$j]['name'] ? $users_database[$j]['name'] : $users_database[$j]['email'];
                $users_frontend[$j]["code"] = $users_database[$j]['code'];
                $users_frontend[$j]["client_id"] = $client_id;
                $users_frontend[$j]["selected"] = false;
            }
        }
        $data = [
            'data' => $users_frontend,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }


    public function clientHotel(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');

        $hotel_id = $request->input('hotel_id');
        $period = $request->input('period');
        $market = $request->input('market');

        $hotel = Hotel::find($hotel_id);
        $businessRegion = BusinessRegionsCountry::where('country_id', $hotel->country_id)->first();
        // $client_database = Client::select(['id', 'code', 'name', 'country_id' ])->with([
        $client_database = Client::with([
            'markups' => function ($query) use ($period, $hotel_id, $businessRegion) {
                $query->where('period', $period)->where('status', 1)->where('business_region_id', $businessRegion->business_region_id);
            }
        ])->with('markets')
            ->with('countries.translations')
            ->whereHas('markets', function ($query) {
                $query->where('status', 1);
            })
            ->whereHas('markups', function ($query) use ($period) {
                $query->where('period', $period)->where('status', 1);
            })
            ->where('status', 1);


        if ($market) {
            $client_database->where('market_id', $market);
        }

        //         $client_database->where('id', 16598);
        // dd(json_encode($client_database->get()->toArray()));
        $hotels_frontend = [];

        $hotel_client_database = HotelClient::select(['client_id', 'period'])->where([
            'hotel_id' => $hotel_id,
            'period' => $period
        ]);

        $hotel_client_ids = $hotel_client_database->pluck('client_id');

        if ($hotel_client_database->count() > 0) {
            $client_database->whereNotIn('id', $hotel_client_ids);
        }


        if ($querySearch) {
            $country_ids = Translation::where('value', 'LIKE', '%' . $querySearch . '%')
                ->where('type', 'country')
                ->where('language_id', 1)
                ->pluck('object_id');
            $client_database->whereIn('country_id', $country_ids);
            $client_database->orWhere(function ($query) use ($querySearch) {
                $query->orWhereRaw("CONCAT(code, '', name) LIKE ?", ['%' . $querySearch . '%']);
                // })->whereHas('markups', function ($query) use ($period, $businessRegion) {
                //     $query->where('period', $period)->where('status', 1);
            })->whereHas('markets', function ($query) {
                $query->where('status', 1);
            });
        }

        // $client_database->whereHas('markups', function ($query) use ($period, $businessRegion) {
        //     $query->where('period', $period)->where('status', 1)->where('business_region_id', $businessRegion->business_region_id);
        // });

        $client_database->whereHas('businessRegions', function ($query) use ($businessRegion) {
            $query->where('business_region_id', $businessRegion->business_region_id);
        });

        $count = $client_database->count();

        if ($paging === 1) {
            $client_database = $client_database->take($limit)->get();
        } else {
            $client_database = $client_database->skip($limit * ($paging - 1))->take($limit)->get();
        }
        if ($count > 0) {
            $markupHotels = MarkupHotel::select(['markup', 'client_id'])->where([
                'hotel_id' => $hotel_id,
                'period' => $period
            ])->get();

            for ($j = 0; $j < count($client_database); $j++) {
                $markup = "";
                foreach ($markupHotels as $markupHotel) {
                    if ($markupHotel->client_id == $client_database[$j]["id"]) {
                        $markup = $markupHotel->markup;
                    }
                }

                $markup_client = $client_database[$j]->markups->first() ? $client_database[$j]->markups->first()->hotel : '';
                $hotels_frontend[$j]["id"] = "";
                $hotels_frontend[$j]["client_id"] = $client_database[$j]["id"];
                $hotels_frontend[$j]["name"] = $client_database[$j]["code"] . ' - ' . $client_database[$j]["name"] . ' - ' . $client_database[$j]["countries"]["translations"][0]["value"];
                $hotels_frontend[$j]["hotel_id"] = $hotel_id;
                $hotels_frontend[$j]["markup"] = $markup ? $markup : ($markup_client ? $markup_client : 0);
                $hotels_frontend[$j]["markup_no_defined"] = $markup_client ? 0 : 1;
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

    public function clientService(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');

        $service_id = $request->input('service_id');
        $period = $request->input('period');
        $market = $request->input('market');

        // TRAER EL SERVICIO
        $region = DB::table('services')
            ->select('business_regions.id', 'business_regions.description')
            ->join('service_origins', 'services.id', '=', 'service_origins.service_id')
            ->join('countries', 'service_origins.country_id', '=', 'countries.id')
            ->join('business_region_country', 'countries.id', '=', 'business_region_country.country_id')
            ->join('business_regions', 'business_region_country.business_region_id', '=', 'business_regions.id')
            ->where('services.id', $service_id)
            ->first();

        // return response()->json($region);

        $client_database = Client::select(['id', 'code', 'name', 'country_id', 'general_markup'])
            ->with([
                'markups' => function ($query) use ($period, $region) {
                    $query->where('period', $period)->where('status', 1);
                    $query->where('business_region_id', $region->id);
                }
            ])
            ->with('countries.translations')->whereHas('markups', function ($query) use ($period) {
                $query->where('period', $period);
            })
            ->whereHas('markups', function ($query) use ($period) {
                $query->where('period', $period)->where('status', 1);
            })->where('status', 1)
            ->whereHas('businessRegions', function ($query) use ($region) {
                $query->where('business_region_id', $region->id);
            });

        if ($market) {
            $client_database->where('market_id', $market);
        }

        $services_frontend = [];

        $service_client_database = ServiceClient::select(['client_id', 'period'])->where([
            'service_id' => $service_id,
            'period' => $period,
            'business_region_id' => $region->id
        ]);

        $service_client_ids = $service_client_database->pluck('client_id');

        if ($service_client_database->count() > 0) {
            $client_database->whereNotIn('id', $service_client_ids);
        }
        $count = $client_database->count();

        if (!empty($querySearch)) {
            $country_ids = Translation::where('value', 'LIKE', '%' . $querySearch . '%')->where(
                'type',
                'country'
            )->where('language_id', 1)->pluck('object_id');
            if ($country_ids->count() > 0) {
                $client_database->whereIn('country_id', $country_ids);
            }
            $client_database->where(function ($query) use ($querySearch) {
                $query->orWhereRaw("CONCAT(code, '', name) LIKE ?", ['%' . $querySearch . '%']);
            });
        }

        if ($paging === 1) {
            $client_database = $client_database->take($limit)->get();
        } else {
            $client_database = $client_database->skip($limit * ($paging - 1))->take($limit)->get();
        }

        // return

        if ($count > 0) {

            $markupServices = MarkupService::select(['markup', 'client_id'])->where([
                'service_id' => $service_id,
                'period' => $period
            ])->get();

            // return response()->json($client_database);

            for ($j = 0; $j < count($client_database); $j++) {
                $markup = "";
                foreach ($markupServices as $markupService) {
                    if ($markupService->client_id == $client_database[$j]["id"]) {
                        $markup = $markupService->markup;
                    }
                }

                $services_frontend[$j]["id"] = "";
                $services_frontend[$j]["client_id"] = $client_database[$j]["id"];
                $services_frontend[$j]["name"] = $client_database[$j]["code"] . ' - ' . $client_database[$j]["name"] . ' - ' . $client_database[$j]["countries"]["translations"][0]["value"];
                $services_frontend[$j]["service_id"] = $service_id;
                $services_frontend[$j]["markup"] = $markup ? $markup : ($client_database[$j]->markups->first() ? $client_database[$j]->markups->first()->service : 0);

                $services_frontend[$j]["selected"] = false;
            }
        }

        $data = [
            'data' => $services_frontend,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    public function clientTrain(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');

        $train_template_id = $request->input('train_template_id');
        $period = $request->input('period');
        $market = $request->input('market');

        $client_database = Client::select(['id', 'code', 'name', 'country_id'])
            ->with([
                'markups' => function ($query) use ($period) {
                    $query->where('period', $period)->where('status', 1);
                }
            ])
            ->with('countries.translations')->whereHas('markups', function ($query) use ($period) {
                $query->where('period', $period);
            })
            ->whereHas('markups', function ($query) use ($period) {
                $query->where('period', $period)->where('status', 1);
            })->where('status', 1);

        if ($market) {
            $client_database->where('market_id', $market);
        }

        $services_frontend = [];

        $service_client_database = TrainClient::select(['client_id', 'period'])->where([
            'train_template_id' => $train_template_id,
            'period' => $period
        ]);

        $service_client_ids = $service_client_database->pluck('client_id');

        if ($service_client_database->count() > 0) {
            $client_database->whereNotIn('id', $service_client_ids);
        }
        $count = $client_database->count();

        if (!empty($querySearch)) {
            $country_ids = Translation::where('value', 'LIKE', '%' . $querySearch . '%')->where(
                'type',
                'country'
            )->where('language_id', 1)->pluck('object_id');
            if ($country_ids->count() > 0) {
                $client_database->whereIn('country_id', $country_ids);
            }
            $client_database->where(function ($query) use ($querySearch) {
                $query->orWhereRaw("CONCAT(code, '', name) LIKE ?", ['%' . $querySearch . '%']);
            });
        }

        if ($paging === 1) {
            $client_database = $client_database->take($limit)->get();
        } else {
            $client_database = $client_database->skip($limit * ($paging - 1))->take($limit)->get();
        }

        if ($count > 0) {

            $markupServices = MarkupTrain::select(['markup', 'client_id'])->where([
                'train_template_id' => $train_template_id,
                'period' => $period
            ])->get();

            for ($j = 0; $j < count($client_database); $j++) {
                $markup = "";
                foreach ($markupServices as $markupService) {
                    if ($markupService->client_id == $client_database[$j]["id"]) {
                        $markup = $markupService->markup;
                    }
                }

                $services_frontend[$j]["id"] = "";
                $services_frontend[$j]["client_id"] = $client_database[$j]["id"];
                $services_frontend[$j]["name"] = $client_database[$j]["code"] . ' - ' . $client_database[$j]["name"] . ' - ' . $client_database[$j]["countries"]["translations"][0]["value"];
                $services_frontend[$j]["train_template_id"] = $train_template_id;
                $services_frontend[$j]["markup"] = $markup ? $markup : $client_database[$j]->markups->first()->service;
                $services_frontend[$j]["selected"] = false;
            }
        }

        $data = [
            'data' => $services_frontend,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    public function syncStore(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigo' => 'required',
                'name' => 'required',
                'market' => 'required',
                'country' => 'required',
                'language' => 'required',
                'markup' => 'required',
                'status' => 'required',
            ]);

            if ($validator->fails()) {
                return Response::json(['success' => false, 'message' => (string)$validator->getMessageBag()]);
            } else {
                DB::beginTransaction();
                $code = $request->input('codigo');
                $client_find = Client::where('code', $code)->first();
                //Si encontramos el cliente por el codigo lo actualizamos
                if ($client_find) {
                    //Buscamos el pais por el iso
                    $country = Country::where('iso', $request->input('country'))->first();
                    if ($country) {
                        $client_find->country_id = $country->id;
                    } else {
                        return Response::json(['success' => false, 'message' => 'no se encontro el país']);
                    }
                    //Buscamos el idioma por el iso
                    $language = Language::where('iso', strtolower($request->input('language')))->first();
                    if ($language) {
                        $client_find->language_id = $language->id;
                    } else {
                        return Response::json(['success' => false, 'message' => 'no se encontro el idioma']);
                    }
                    //Buscamos el mercado por el code
                    $market = Market::where('code', strtolower($request->input('market')))->first();
                    if ($market) {
                        $client_find->market_id = $market->id;
                    } else {
                        return Response::json(['success' => false, 'message' => 'no se encontro el mercado']);
                    }
                    $credit_line = $request->input('credit_line');
                    if (!empty($credit_line) and $credit_line !== 0 and $credit_line !== '0') {
                        $client_find->credit_line = $credit_line;
                        $client_find->have_credit = 1;
                    }
                    $client_find->name = $request->input('name');
                    $client_find->status = $request->input('status');
                    $client_find->save();
                    //Buscamos el markup por el año y cliente
                    $markup_period = Markup::where('client_id', $client_find->id)->where(
                        'period',
                        Carbon::now()->format('Y')
                    )->first();
                    if ($markup_period) {
                        $markup_period->hotel = $request->input('markup');
                        $markup_period->service = $request->input('markup');
                        $markup_period->save();
                    } else {
                        $new_markup = new Markup();
                        $new_markup->client_id = $client_find->id;
                        $new_markup->hotel = $request->input('markup');
                        $new_markup->service = $request->input('markup');
                        $new_markup->period = Carbon::now()->format('Y');
                        $new_markup->status = 1;
                        $new_markup->save();
                    }
                } else {
                    //Creamos el nuevo cliente
                    $new_client = new Client();
                    $new_client->code = $code;
                    $new_client->name = $request->input('name');
                    $new_client->have_credit = 0;
                    $new_client->credit_line = null;
                    $new_client->status = $request->input('status');
                    $country = Country::where('iso', $request->input('country'))->first();
                    if ($country) {
                        $new_client->country_id = $country->id;
                    } else {
                        return Response::json(['success' => false, 'message' => 'no se encontro el país']);
                    }
                    //Buscamos el idioma por el iso
                    $language = Language::where('iso', strtolower($request->input('language')))->first();
                    if ($language) {
                        $new_client->language_id = $language->id;
                    } else {
                        return Response::json(['success' => false, 'message' => 'no se encontro el idioma']);
                    }
                    //Buscamos el mercado por el code
                    $market = Market::where('code', strtolower($request->input('market')))->first();
                    if ($market) {
                        $new_client->market_id = $market->id;
                    } else {
                        return Response::json(['success' => false, 'message' => 'no se encontro el mercado']);
                    }
                    $credit_line = $request->input('credit_line');
                    if (!empty($credit_line) and $credit_line !== 0 and $credit_line !== '0') {
                        $client_find->credit_line = $credit_line;
                        $client_find->have_credit = 1;
                    }
                    $new_client->save();
                    $new_markup = new Markup();
                    $new_markup->client_id = $new_client->id;
                    $new_markup->hotel = $request->input('markup');
                    $new_markup->service = $request->input('markup');
                    $new_markup->period = Carbon::now()->format('Y');
                    $new_markup->status = 1;
                    $new_markup->save();
                    $this->storeRatePlan($new_client->id);
                }

                DB::commit();
                return Response::json(['success' => true, 'message' => '']);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json([
                'success' => false,
                'message' => $e->getMessage() . ' - ' . $e->getLine()
            ]);
        }
    }

    public function search(Request $request)
    {
        $limit = ($request->has('limit')) ? $request->input('limit') : 20;
        $query = $request->input('queryCustom');
        $clients = Client::with([
            'markets' => function ($query) {
                $query->select(['id', 'name', 'code']);
            }
        ]);

        if ($query) {
            $clients = $clients->search($query);
        }

        $clients = $clients->limit($limit)->orderBy('id', 'desc')->get(['id', 'code', 'name', 'market_id']);

        $data = [
            'data' => $clients,
            'success' => true,
        ];

        return Response::json($data);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getClientsByMarkets(Request $request)
    {
        $markets_id = $request->post('markets_id');
        $querySearch = $request->post('query');
        $limit = $request->has('limit') ? $request->post('limit') : 20;
        $clients = Client::where('status', 1);

        if (!empty($querySearch)) {
            $clients = $clients->where(function ($query) use ($querySearch) {
                $query->orWhere('code', 'like', '%' . $querySearch . '%');
                $query->orWhere('name', 'like', '%' . $querySearch . '%');
            });
        }

        if (count($markets_id) > 0) {
            $clients = $clients->whereIn('market_id', $markets_id);
        }

        $clients = $clients->limit($limit)->get(['id', 'code', 'name']);

        return \response()->json(['success' => true, 'data' => $clients]);
    }

    public function getClientsByIds(Request $request)
    {
        $clients_id = $request->post('clients_id');
        $clients = collect();
        if (!empty($clients_id) and count($clients_id) > 0) {
            $clients = Client::whereIn('id', $clients_id)
                ->where('status', Client::ACTIVE)
                ->get(['id', 'code', 'name']);
        }

        return \response()->json(['success' => true, 'data' => $clients]);
    }

    public function markup(Request $request, $client_id)
    {
        $client = Client::where('id', '=', $client_id)->first();
        $markup = 0;

        if ($client) {
            $markup = $client->general_markup;
        }

        return response()->json(['success' => true, 'markup' => $markup, 'client' => $client]);
    }

    public function update_clients_brasil(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            $ignore = [
                '5PRIVI',
                '5UPSCA',
                '5BRIEF',
                '5CWLIT',
                '5CENTR',
                '5NASCI',
                '5INCO',
                '5INTR',
                '9WAYT',
                '5CANTA',
                '5FENIX',
                '5ADV',
                '5THOUS',
                '5TOPSE',
                '5PERTR',
                '5SANOF',
                '5IMMAG',
                '5FLYTO',
                '5TAVAR',
                '5TSOLU',
                '5INCEN',
                '5CASEI',
                '5GIGRU',
                '5BRCON',
                '5DMCB',
                '5AMBTE',
                '3MMASS',
                '5ETOUR',
                '5LABR',
                '7OPCO',
                'OEMA3',
                'ONRP42',
                'OERP43',
                '7HISBR',
                '5OPUS',
                '8COMPA',
                '3MSC',
                '5EHTL',
                '5LADIF',
                '5LADIT',
                '9TSB',
                '5TBOTS',
                '5VITER',
                '5BARAT',
                '9PROME',
                'FS9603',
                '5HURBA'
            ];
            $executive_code = 'WNH';

            Client::where('country_id', '=', 12)->whereNotIn('code', $ignore)->update([
                'executive_code' => $executive_code,
            ]);

            return response()->json([
                'type' => 'success',
                'message' => 'Clientes de brasil actualizados a ' . $executive_code
            ]);
        } catch (\Exception $ex) {
            return $this->throwError($ex);
        }
    }

    public function saveClientConfiguration(
        $client_id,
        $hotel_allowed_on_request = true,
        $service_allowed_on_request = true
    ) {
        try {
            $clientConfig = ClientConfiguration::where('client_id', $client_id)->first();
            if ($clientConfig) {
                $clientConfig->hotel_allowed_on_request = $hotel_allowed_on_request;
                $clientConfig->service_allowed_on_request = $service_allowed_on_request;
                $clientConfig->save();
            } else {
                $newClientConfig = new ClientConfiguration();
                $newClientConfig->client_id = $client_id;
                $newClientConfig->hotel_allowed_on_request = $hotel_allowed_on_request;
                $newClientConfig->service_allowed_on_request = $service_allowed_on_request;
                $newClientConfig->save();
            }
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    public function byCodes(Request $request)
    {
        try {
            $codes = $request->input('codes');

            $clients = Client::with(['languages'])
                ->where("code", $codes)
                ->get();

            return Response::json(['success' => true, 'data' => $clients]);
        } catch (\Exception $ex) {
            return response()->json([
                'success' => false,
                'error' => $ex->getMessage()
            ]);
        } catch (\Error $ex) {
            return response()->json([
                'success' => false,
                'error' => $ex->getMessage()
            ]);
        }
    }

    public function executivesByClientId($client_id)
    {
        try {

            $executivesClient = ClientExecutive::where('client_id', $client_id)
                ->where('use_email_reserve', 1)
                ->whereHas('user', function ($query) {
                    $query->where('user_type_id', 3)
                        ->where('status', 1);
                })
                ->with([
                    'user:id,name,email'
                ])
                ->get()
                ->pluck('user')
                ->map(function ($user) {
                    return [
                        'name'  => $user->name,
                        'email' => $user->email,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $executivesClient
            ]);
        } catch (\Throwable $ex) {
            return response()->json([
                'success' => false,
                'error' => $ex->getMessage()
            ]);
        }
    }
}

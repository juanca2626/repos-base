<?php

namespace App\Http\Controllers;

use App\BusinessRegionsCountry;
use App\Client;
use App\ClientRatePlan;
use App\Hotel;
use App\HotelClient;
use App\RatesPlans;
use App\Markup;
use App\MarkupHotel;
use App\TypeClass;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use App\Http\Traits\ClientHotels;

class ClientHotelsController extends Controller
{
    use  ClientHotels;

    public function index(Request $request)
    {
        $page        = (int) $request->input('page', 1);
        $limit       = (int) $request->input('limit', 10);
        $querySearch = $request->input('query');
        $client_id   = $request->input('client_id');
        $period      = $request->input('period');

     

        $hotel_client_database = HotelClient::select(['id', 'hotel_id', 'client_id', 'period'])->with([
                'hotel' => function ($q) {
                    $q->select(['id', 'name', 'status', 'country_id'])
                        ->with([
                            'country' => function ($c) {
                                $c->select(['id', 'ISO']); // <-- ajusta a 'iso' si tu DB lo tiene en minúscula
                            }
                        ]);
                }
        ])->whereHas('hotel', function ($query) use ($querySearch) {
            if (!empty($querySearch)) {
                $query->where('name', 'like', '%' . $querySearch . '%');
            }
            $query->where('status', 1); 
        })->where(['client_id' => $client_id, 'period' => $period, 'business_region_id' => $request->input('region_id')]);

        $paginator = $hotel_client_database->paginate($limit, ['*'], 'page', $page);

        $hotel_client_frontend = $paginator->getCollection()->map(function ($hc) {
            return [
                'markup'          => $hc->markup,
                'hotel_client_id' => $hc->id,
                'client_id'       => $hc->client_id,
                'name'            => optional($hc->hotel)->name,
                'hotel_id'        => $hc->hotel_id,
                'selected'        => false,

                // NUEVO:
                'country_iso'     => optional(optional($hc->hotel)->country)->ISO,
            ];
        })->values();

        return Response::json([
            'data'    => $hotel_client_frontend,
            'count'   => $paginator->total(),
            'success' => true
        ]);
    }

    public function clientLocked(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $hotel_id = $request->input('hotel_id');
        $period = $request->input('period');


 
        // extraemos el region_id 
        $hotels_database = Hotel::select(['id', 'country_id'])->find($hotel_id);
        $businessRegion = BusinessRegionsCountry::where('country_id', $hotels_database->country_id)->first();
        $region_id = $businessRegion->business_region_id;
 
        $hotel_client_frontend = [];

        $hotel_client_database = HotelClient::select(['id', 'hotel_id', 'client_id', 'period'])->
        whereHas('client', function ($query) use ($querySearch) {
            $query->where('name', 'like', '%' . $querySearch . '%');
        })->where(['hotel_id' => $hotel_id, 'period' => $period, 'business_region_id'=> $region_id]);

        $count = $hotel_client_database->count();

        if ($paging === 1) {
            $hotel_client_database = $hotel_client_database->take($limit)->get();
        } else {
            $hotel_client_database = $hotel_client_database->skip($limit * ($paging - 1))->take($limit)->get();
        }

        if ($count > 0) {
            for ($j = 0; $j < count($hotel_client_database); $j++) {
                $hotel_client_frontend[$j]["markup"] = $hotel_client_database[$j]["markup"];
                $hotel_client_frontend[$j]["hotel_client_id"] = $hotel_client_database[$j]["id"];
                $hotel_client_frontend[$j]["client_id"] = $hotel_client_database[$j]["client_id"];
                $hotel_client_frontend[$j]["name"] = $hotel_client_database[$j]["client"]["name"];
                $hotel_client_frontend[$j]["hotel_id"] = $hotel_client_database[$j]["hotel_id"];
                $hotel_client_frontend[$j]["selected"] = false;
            }
        }

        $data = [
            'data' => $hotel_client_frontend,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }


    public function selectPeriod(Request $request)
    {
        $client_id = $request->input('client_id');
        $markups = Markup::select('id', 'period', 'hotel')->where(['status' => 1, 'client_id' => $client_id, 'business_region_id' => $request->input('region_id')])->get();
        $result = [];
        foreach ($markups as $markup) {
            array_push($result, ['text' => $markup->period, 'value' => $markup->id, 'porcen_hotel' => $markup->hotel]);
        }
        return Response::json(['success' => true, 'data' => $result]);
    }

    public function store(Request $request)
    {
        $data = $request->input('data');
        $markup = $request->input('porcentage');
        $period = $request->input('period');
        $region_id = $request->input('region_id', NULL);

        if($region_id == NULL)
        {
            // se esta guardando desde el modulo de hoteles
            $hotels_database = Hotel::select(['id', 'country_id'])->find($data['hotel_id']);
            $businessRegion = BusinessRegionsCountry::where('country_id', $hotels_database->country_id)->first();
            $region_id = $businessRegion->business_region_id;
        }

        
        $store_hotel_client = $this->storeHotelClient($markup, $period, $data['client_id'], $data['hotel_id'], $region_id);
        /*
        if (!empty($store_hotel_client) && $store_hotel_client->count() > 0) {
            $this->insertRatePlans($data['hotel_id'], $data['client_id'], $markup, $period);
        }*/

        return Response::json(['success' => true, 'hotel_client_id' => $store_hotel_client->id]);
    }

    public function storeAll(Request $request)
    {
        $client_id = $request->input('client_id');
        $markup = $request->input('porcentage');
        $period = $request->input('period');
        $region_id = $request->input('region_id');

        $this->storeAllHotel($client_id, $markup, $period, $region_id);

        return Response::json(['success' => true]);
    }

    public function storeClientAll(Request $request)
    {
        DB::transaction(function () use ($request) {

            $hotel_id = $request->input('hotel_id');
            $markup = $request->input('porcentage');
            $period = $request->input('period');
            $market = $request->input('market');
            $region_id = $request->input('region_id');



            $client_database = Client::select(['id'])->where(['status' => 1]);

            if($market){
                $client_database->where('market_id' , $market);
            }

            $client_database = $client_database->get();


            $clientsIds = [];
            foreach ($client_database as $key => $client) {
                $clientsIds[$client['id']] = ['period' => $period];
            }

            Hotel::find($hotel_id)->clients()->wherePivot('period', $period)->sync($clientsIds);


        });

        return Response::json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        $data = $request->input('data');
        $period = $request->input('period');
        $client_id = $data['client_id'];
        $client_rate_plan = ClientRatePlan::where(['id' => $id, 'client_id' => $client_id, 'period' => $period])->first();
        $client_rate_plan->delete();

        return Response::json(['success' => true]);
    }

    public function inverse(Request $request)
    {
        $data = $request->input('data');
        $client_id = $data['client_id'];
        $period = $request->input('period');
        $hotel_id = $data['hotel_id'];
        /*
        DB::transaction(function () use ($client_id, $period, $hotel_id) {
            ClientRatePlan::where(['client_id' => $client_id, 'period' => $period])
            ->whereHas('ratePlan', function ($query) use ($hotel_id) {
                $query->where('hotel_id', $hotel_id);
            })->delete();
        });
    */

        HotelClient::find($data['hotel_client_id'])->delete();

        return Response::json(['success' => true]);
    }

    public function inverseAll(Request $request)
    {
        $client_id = $request->input('client_id');
        $period = $request->input('period');

        /*
        DB::transaction(function () use ($client_id, $period) {
            ClientRatePlan::where(['client_id' => $client_id, 'period' => $period])->delete();
        });
        */

        DB::transaction(function () use ($client_id, $period) {
            HotelClient::where(['client_id' => $client_id, 'period' => $period])->each(function ($hotelClient) {
                $hotelClient->delete();
            });
        });

        return Response::json(['success' => true]);
    }


    public function inverseClientAll(Request $request)
    {
        $hotel_id = $request->input('hotel_id');
        $period = $request->input('period');

        /*
        DB::transaction(function () use ($client_id, $period) {
            ClientRatePlan::where(['client_id' => $client_id, 'period' => $period])->delete();
        });
        */

        DB::transaction(function () use ($hotel_id, $period) {
            HotelClient::where(['hotel_id' => $hotel_id, 'period' => $period])->delete();
        });

        return Response::json(['success' => true]);
    }

    public function update(Request $request)
    {
        $hotel_id = $request->input('hotel_id');
        $client_id = $request->input('client_id');
        $markup = $request->input('markup');
        $period = $request->input('period');


        $hotelMarkup = MarkupHotel::where('client_id', $client_id)->where('hotel_id', $hotel_id)->where('period', $period)->first();

        if (is_object($hotelMarkup)) {
            $hotelMarkup->markup = $markup;
            $hotelMarkup->save();
        } else {
            $hotelMarkup = new MarkupHotel();
            $hotelMarkup->client_id = $client_id;
            $hotelMarkup->hotel_id = $hotel_id;
            $hotelMarkup->period = $period;
            $hotelMarkup->markup = $markup;
            $hotelMarkup->save();
        }


        /*
        $hotel_client = HotelClient::where(['id' =>$data['hotel_client_id'], 'period' => $period])->first();
        $hotel_client->markup = $markup;

        if ($hotel_client->save()) {
            $client_rate_plan = ClientRatePlan::where([
                'period' => $period,
                'client_id' => $hotel_client->client_id
            ])->get();

            foreach ($client_rate_plan as $key => $value) {
                $client_rate_plan = ClientRatePlan::find($value->id);
                $client_rate_plan->markup = $markup;
                $client_rate_plan->save();
            }
        } */

        return Response::json(['success' => true]);
    }


    public function getClientsByPeriod(Request $request)
    {
        $clients = Client::select('id', 'name')
            ->where('status', 1)
            ->whereDoesntHave('fromHotel', function ($query) use ($request) {
                $query->where(['hotel_id' => $request->input('hotel_id'), 'period' => $request->input('period')]);
            })
            ->get();

        $result = [];
        foreach ($clients as $i => $client) {

            $result[$i]["client_rate_plan_id"] = "";
            $result[$i]["id"] = $client->id;
            $result[$i]["name"] = $client->name;
            $result[$i]["markup"] = 0;
            $result[$i]["selected"] = false;

        }

        return Response::json(['success' => true, 'data' => $result]);
    }


    public function getClientsByPeriod__________(Request $request)
    {
        $hotel_id = $request->input('hotel_id');
        $period = $request->input('period');

        $hotel_client_database = HotelClient::select(['id', 'hotel_id', 'client_id', 'markup', 'period'])
            ->with('client')
            ->where(['hotel_id' => $hotel_id, 'period' => $period])->get();

        $hotels_database = Hotel::select(['id']);

        if ($hotel_client_database->count() > 0) {
            $hotels_database->whereNotIn('id', $hotel_client_database);
        }

        $hotel_client_frontend = [];

        for ($i = 0; $i < $hotel_client_database->count(); $i++) {
            $hotel_client_frontend[$i]["client_rate_plan_id"] = "";
            $hotel_client_frontend[$i]["id"] = $hotel_client_database[$i]["client"]["id"];
            $hotel_client_frontend[$i]["name"] = $hotel_client_database[$i]["client"]["name"];
            $hotel_client_frontend[$i]["markup"] = $hotel_client_database[$i]["markup"];
            $hotel_client_frontend[$i]["selected"] = false;
        }
        $data = [
            'data' => $hotel_client_frontend,
            'success' => true
        ];

        return Response::json($data);
    }

    public function clientsByHotel(Request $request)
    {
        $hotel_id = $request->get('hotel_id');
        $year = $request->get('year');

        $clients = HotelClient::select('client_id')
            ->whereHas('client', function ($query) {
                $query->where('status', 1);
            })
            ->with(['client' => function ($query) {
                $query->select('id', 'name');
                $query->where('status', 1);

                $query->with('allotments');
            }])
            ->where('hotel_id', $hotel_id)
            ->where('period', $year)->get();

        return Response::json(['success' => true, 'clients' => $clients]);
    }

    public function classHotelByClient(Request $request)
    {

        try {
            $period = Carbon::now('America/Lima')->year;

            $lang = ( $request->input('lang') != '' ) ? $request->input('lang') : 'ES';
            $client_id = $request->post('client_id');
            // Traer los id de los hoteles que no trabaja el cliente para filtrar las clases que pueda usar
//            $hotels_ids = HotelClient::select('client_id', 'hotel_id')
//                ->whereHas('client', function ($query) {
//                    $query->where('status', 1);
//                })
//                ->where('client_id', $client_id)
//                ->where('period', $period)->distinct()->pluck('hotel_id');

            /** @var Collection $TypeClasses */
            $TypeClasses = TypeClass::select('id')
//                ->whereHas('hotels', function ($query) use ($hotels_ids) {
//                    $query->whereNotIn('hotels.id', $hotels_ids);
//                })
                ->with(['translations' => function ($query)use ($lang) {
                    $query->select('object_id', 'value');
                    $query->where('type', 'typeclass');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }])
                ->whereHas('hotels', function ($query) {
                    $query->where('status', '=', 1);
                    $query->whereNull('deleted_at');
                })
                ->orderBy('order', 'ASC')
                ->get();

            if ($TypeClasses->count() > 0) {
                $TypeClasses->transform(function ($TypeClass) {
                    return [
                        "class_id" => $TypeClass["id"],
                        "class_name" => $TypeClass["translations"][0]["value"]
                    ];
                });

                return Response::json(['success' => true, 'data' => $TypeClasses]);
            } else {
                return Response::json(['success' => true, 'data' => []]);
            }
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'error' => $exception->getMessage()]);
        }
    }
}

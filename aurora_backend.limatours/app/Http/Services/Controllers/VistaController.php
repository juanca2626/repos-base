<?php

namespace App\Http\Services\Controllers;

use App\City;
use App\Client;
use App\ClientEcommerce;
use App\Galery;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchFilterVista;
use App\Http\Services\Traits\ClientTrait;
use App\Http\Traits\ApiResponse;
use App\Language;
use App\PackageDestination;
use App\PackageRateSaleMarkup;
use App\State;
use App\Translation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class VistaController extends Controller
{
    use ClientTrait, ApiResponse;

    public function searchDestinationsAll(SearchFilterVista $request)
    {
        try {

//            $client_id = $this->getClientId($request);
//            $this->setClient($client_id);
//            $filter = $request->input('filter');
//            $lang = $request->input('lang');
//            $language = Language::where('iso', $lang)->first();
//
//            $destinations = $this->getDistinctServicesDestinations();
//
//            $countries = $destinations->pluck('country_id')->unique();
//            $states = $destinations->pluck('state_id')->unique();
//            $cities = $destinations->pluck('city_id')->unique();
//
//            //Todo obtengo los filtro por el nombre del estado
//            $query_states_ids = Translation::where('type', 'state')
//                ->where('slug', 'state_name')
//                ->where('language_id', $language->id)
//                ->where('value', 'like', '%'.$filter.'%')
//                ->whereIn('object_id', $states)
//                ->whereHas('state', function ($query) use ($countries) {
//                    $query->whereIn('country_id', $countries);
//                })->get(['value', 'object_id']);
//
//            //Todo obtengo los filtro por el nombre de la ciudad
//            $query_cities_ids = Translation::where('type', 'city')
//                ->where('slug', 'city_name')
//                ->where('language_id', $language->id)
//                ->where('value', 'like', '%'.$filter.'%')
//                ->whereIn('object_id', $cities)
//                ->whereHas('city', function ($query) use ($states) {
//                    $query->whereIn('state_id', $states);
//                })->get(['value', 'object_id']);


//            //Todo obtengo los filtro por el nombre de las zonas
//            $query_zones_ids = Translation::where('type', 'zone')
//                ->where('slug', 'zone_name')
//                ->where('language_id', $language->id)
//                ->where('value', 'like', '%'.$filter.'%')
//                ->whereHas('zone', function ($query) use ($cities) {
//                    $query->whereIn('city_id', $cities);
//                })->get(['value', 'object_id']);

//            $states_data = [];
//            $cities_data = [];
//
//            if ($query_states_ids->count() > 0) {
//                $states_data = $this->getStatesNames($query_states_ids->pluck('object_id'));
//            }
//
//            if ($query_cities_ids->count() > 0) {
//                $cities_data = $this->getCitiesNames($query_cities_ids->pluck('object_id'));
//            }
//
//            $experiences_destination = array_merge($states_data, $cities_data);

//            $packages_destination = $this->getDestinationPackage($client_id, $filter, $language->id);

            $data = [
                'experiences' => [],
                'packages' => [],
            ];
            return Response::json(['success' => true, 'data' => $data]);
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'data' => $exception->getMessage()]);
        }
    }

    public function getStatesNames($query_states_ids)
    {
        $states = [];
        $data_states = State::whereIn('id', $query_states_ids)
            ->with([
                'translations' => function ($query) {
                    $query->select(['object_id', 'value']);
                    $query->where('language_id', 1);
                }
            ])
            ->with([
                'country' => function ($query) {
                    $query->select(['id']);
                    $query->with([
                        'translations' => function ($query) {
                            $query->select(['object_id', 'value']);
                            $query->where('language_id', 1);
                        }
                    ]);
                }
            ])
            ->with([
                'galleries' => function ($query) {
                    $query->select(['object_id', 'url']);
                    $query->where('position', 1);
                    $query->where('state', 1);
                }
            ])->get();

        foreach ($data_states as $state) {
            $image_state = '';
            if (count($state["galleries"]) > 0) {
                $image_state = $state["galleries"][0]["url"];
            }
            if (count($states) === 0) {

                array_push($states, [
                    "id" => $state["country"]["id"].','.$state["id"],
                    "state" => $state["translations"][0]["value"],
                    "display_destiny" => $state["country"]["translations"][0]["value"].", ".$state["translations"][0]["value"],
                    "image" => verifyCloudinaryImg($image_state, 150, 150, ''),
                ]);
            } else {
                $exists = false;
                for ($i = 0; $i < count($data_states); $i++) {
                    if ($data_states[$i]["ids"] == ($state["country"]["id"].','.$state["id"])) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    array_push($states, [
                        "id" => $state["country"]["id"].','.$state["id"],
                        "state" => $state["translations"][0]["value"],
                        "display_destiny" => $state["country"]["translations"][0]["value"].", ".$state["translations"][0]["value"],
                        "image" => verifyCloudinaryImg($image_state, 150, 150, ''),
                    ]);
                }
            }
        }
        return $states;
    }

    public function getCitiesNames($query_states_ids)
    {
        $cities = [];
        $data_cities = City::whereIn('id', $query_states_ids)
            ->with([
                'translations' => function ($query) {
                    $query->select(['object_id', 'value']);
                    $query->where('language_id', 1);
                }
            ])
            ->with([
                'state' => function ($query) {
                    $query->select(['id', 'country_id']);
                    $query->with([
                        'country' => function ($query) {
                            $query->select(['id']);
                            $query->with([
                                'translations' => function ($query) {
                                    $query->select(['object_id', 'value']);
                                    $query->where('language_id', 1);
                                }
                            ]);
                        }
                    ]);
                    $query->with([
                        'translations' => function ($query) {
                            $query->select(['object_id', 'value']);
                            $query->where('language_id', 1);
                        }
                    ]);
                    $query->with([
                        'galleries' => function ($query) {
                            $query->select(['object_id', 'url']);
                            $query->where('position', 1);
                            $query->where('state', 1);
                        }
                    ]);
                }
            ])
            ->get();

        foreach ($data_cities as $city) {
            $image_state = '';
            if (count($city['state']["galleries"]) > 0) {
                $image_state = $city['state']["galleries"][0]["url"];
            }
            if (count($cities) === 0) {
                array_push($cities, [
                    "id" => $city['state']["country_id"].','.$city["state"]["id"].','.$city["id"],
                    "state" => $city['state']["translations"][0]["value"],
                    "display_destiny" => $city['state']["country"]["translations"][0]["value"].", ".$city['state']["translations"][0]["value"].", ".$city["translations"][0]["value"],
                    "image" => verifyCloudinaryImg($image_state, 150, 150, ''),
                ]);
            } else {
                $exists = false;
                for ($i = 0; $i < count($data_cities); $i++) {
                    if ($data_cities[$i]["ids"] == ($city['state']["country_id"].','.$city["state"]["id"].','.$city["id"])) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    array_push($cities, [
                        "id" => $city['state']["country_id"].','.$city["state"]["id"].','.$city["id"],
                        "state" => $city['state']["translations"][0]["value"],
                        "display_destiny" => $city['state']["country"]["translations"][0]["value"].", ".$city['state']["translations"][0]["value"].", ".$city["translations"][0]["value"],
                        "image" => verifyCloudinaryImg($image_state, 150, 150, ''),
                    ]);
                }
            }
        }
        return $cities;
    }

    public function getZonesNames($query_states_ids)
    {
        $states = [];
        $data_states = State::whereIn('id', $query_states_ids)
            ->with([
                'translations' => function ($query) {
                    $query->select(['object_id', 'value']);
                    $query->where('language_id', 1);
                }
            ])
            ->with([
                'country' => function ($query) {
                    $query->select(['id']);
                    $query->with([
                        'translations' => function ($query) {
                            $query->select(['object_id', 'value']);
                            $query->where('language_id', 1);
                        }
                    ]);
                }
            ])
            ->with([
                'galleries' => function ($query) {
                    $query->select(['object_id', 'url']);
                    $query->where('position', 1);
                    $query->where('state', 1);
                }
            ])->get();

        foreach ($data_states as $state) {
            $image_state = '';
            if (count($state["galleries"]) > 0) {
                $image_state = $state["galleries"][0]["url"];
            }
            if (count($states) === 0) {

                array_push($states, [
                    "ids" => $state["country"]["id"].','.$state["id"],
                    "label" => $state["country"]["translations"][0]["value"].", ".$state["translations"][0]["value"],
                    "image" => verifyCloudinaryImg($image_state, 150, 150, ''),
                ]);
            } else {
                $exists = false;
                for ($i = 0; $i < count($data_states); $i++) {
                    if ($data_states[$i]["ids"] == ($state["country"]["id"].','.$state["id"])) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    array_push($states, [
                        "ids" => $state["country"]["id"].','.$state["id"],
                        "label" => $state["country"]["translations"][0]["value"].", ".$state["translations"][0]["value"],
                        "image" => verifyCloudinaryImg($image_state, 150, 150, ''),
                    ]);
                }
            }
        }
        return $states;
    }

    public function getDestinationPackage($client_id, $filter, $language_id)
    {
//        $market = Client::find($client_id);
//        // obtengo los destinos de paquetes
//        $packages = PackageRateSaleMarkup::select(['id', 'seller_type', 'seller_id', 'package_plan_rate_id'])
//            ->where(function ($query) use ($client_id, $market) {
//                $query->orWhere('seller_id', $client_id);
//                $query->orWhere('seller_id', $market->market_id);
//            })
//            ->with([
//                'plan_rate' => function ($query) {
//                    $query->select(['id', 'package_id']);
//                }
//            ])->where('status', 1)->get();
//
//        $package_client = $packages->pluck('plan_rate.package_id')->unique();
//
//        $data = [];
//        $destinations = PackageDestination::whereIn('package_id', $package_client)->get();
////        $countries = $destinations->pluck('country_id')->unique();
//        $states = $destinations->pluck('state_id')->unique();
//
//        //Todo obtengo los filtro por el nombre del estado
//        $query_states_ids = Translation::where('type', 'state')
//            ->where('slug', 'state_name')
//            ->where('language_id', $language_id)
//            ->where('value', 'like', '%'.$filter.'%')
//            ->whereIn('object_id', $states)
//            ->whereHas('state', function ($query) {
//                $query->where('country_id', 89);
//            })->get(['value', 'object_id']);
//
//
//        if ($query_states_ids->count() > 0) {
//            $states_data = $this->getStatesNames($query_states_ids->pluck('object_id'));
//            $data = $states_data;
//        }
//
////        if ($query_cities_ids->count() > 0) {
////            $cities_data = $this->getCitiesNames($query_cities_ids->pluck('object_id'));
////        }

        return [];
//        return $data;
    }

    public function getDestinationNames($package_clients)
    {

        $package_destinations = DB::table('package_destinations as d')
            ->join('translations as t', 't.object_id', '=', 'd.state_id')
            ->join('state as c', 'd.object_id', '=', 'd.state_id')
            ->where('t.language_id', 1)
            ->where('t.type', 'state')
            ->where('t.slug', 'state_name')
            ->whereIn('d.package_id', $package_clients)
            ->select('d.state_id as state_id', 't.value as state')
            ->groupBy('d.state_id')
            ->get();
        $ids = $package_destinations->pluck('state_id');

        $galeries = Galery::whereIn('object_id', $ids)
            ->where('slug', 'state_gallery')
            ->where('type', 'state')
            ->where('position', 1)
            ->where('state', 1)
            ->whereNull('deleted_at')
            ->get(['object_id', 'url']);

//        $package_destinations = $package_destinations->transform(function ($item) use ($galeries) {
//            $img = $galeries->first(function ($value) use ($item) {
//                return $value->object_id == $item->state_id;
//            });
//            if ($img) {
//                $states = [
//                    'id' => $item->state_id,
//                    'state' => $item->state,
//                    'image' => $img->url,
//                ];
//            } else {
//                $states = [
//                    'state_id' => $item->state_id,
//                    'state' => $item->state,
//                    'image' => '',
//                ];
//            }
//            return $states;
//        });


        return $package_destinations;
    }

    public function getContactEcommerceByClient($client_id)
    {
        try {
            $contact = ClientEcommerce::where('client_id', $client_id)->first();
            return Response::json(['success' => true, 'data' => $contact]);
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'data' => $exception->getMessage()]);
        }
    }
}

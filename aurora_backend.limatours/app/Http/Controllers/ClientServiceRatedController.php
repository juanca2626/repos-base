<?php

namespace App\Http\Controllers;

use App\ClientServiceRated;
use App\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ClientServiceRatedController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'client_id' => 'required',
            'service_id' => 'required',
            'rated' => 'required',
            'period' => 'required',
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'message' => $validator->getMessageBag()]);
        } else {
            try {

                $client_id = $request->input('client_id');
                $service_id = $request->input('service_id');
                $rated = $request->input('rated');
                $period = $request->input('period');
                $find = ClientServiceRated::where('client_id', $client_id)
                    ->where('service_id', $service_id)
                    ->where('period', $period)
                    ->get();
                if (count($find) == 0) {
                    $new = new ClientServiceRated();
                    $new->client_id = $client_id;
                    $new->service_id = $service_id;
                    $new->rated = $rated;
                    $new->period = $period;
                    $new->save();
                } else {
                    $find = ClientServiceRated::find($find[0]['id']);
                    $find->rated = $rated;
                    $find->save();
                }

                return Response::json(['success' => true]);
            } catch (\Exception $e) {
                return Response::json(['success' => false, 'message' => $e->getMessage()]);
            }
        }

    }

    public function storeByFilters(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'client_id' => 'required',
                'rated' => 'required|between:1,10'
            ]);
            if ($validator->fails()) {
                return Response::json(['success' => false, 'message' => $validator->getMessageBag()]);
            } else {
                DB::transaction(function () use ($request) {
                    $client_id = $request->input('client_id');
                    $categories = $request->input('categories');
                    $experiences = $request->input('experiences');
                    $destiny = $request->input('destination');
                    $period = $request->input('period');
                    $rated = $request->input('rated');
                    $destiny_codes = explode(",", $destiny);
                    $country_id = "";
                    $state_id = "";
                    $city_id = "";
                    $zone_id = "";
                    for ($i = 0; $i < count($destiny_codes); $i++) {
                        if ($i == 0) {
                            $country_id = $destiny_codes[$i];
                        }
                        if ($i == 1) {
                            $state_id = $destiny_codes[$i];
                        }
                        if ($i == 2) {
                            $city_id = $destiny_codes[$i];
                        }
                        if ($i == 3) {
                            $zone_id = $destiny_codes[$i];
                        }
                    }

                    Service::select([
                        'id',
                        'name',
                        'aurora_code'
                    ])->when(($experiences != ""), function ($query) use ($experiences) {
                        return $query->whereHas('experience', function ($query) use ($experiences) {
                            $query->where('experience_id', $experiences);
                        });
                    })->when(($categories != ""), function ($query) use ($categories) {
                        return $query->whereHas('serviceSubCategory.serviceCategories',
                            function ($query) use ($categories) {
                                $query->where('id', $categories);
                            });
                    })->when(($country_id != ""), function ($query) use ($country_id) {
                        return $query->whereHas('serviceDestination', function ($query) use ($country_id) {
                            $query->where('country_id', $country_id);
                        });
                    })->when(($state_id != ""), function ($query) use ($state_id) {
                        return $query->whereHas('serviceDestination', function ($query) use ($state_id) {
                            $query->where('state_id', $state_id);
                        });
                    })->when(($city_id != ""), function ($query) use ($city_id) {
                        return $query->whereHas('serviceDestination', function ($query) use ($city_id) {
                            $query->where('city_id', $city_id);
                        });
                    })->when(($zone_id != ""), function ($query) use ($zone_id) {
                        return $query->whereHas('serviceDestination', function ($query) use ($zone_id) {
                            $query->where('zone_id', $zone_id);
                        });
                    })->whereDoesntHave('service_clients', function ($query) use ($period, $client_id) {
                        $query->where('client_id', $client_id);
                        $query->where('period', $period);
                    })->chunk(50, function ($service) use ($client_id, $period, $rated) {
//                        dd($service[0]->id);
                        $find = ClientServiceRated::where('client_id', $client_id)
                            ->where('service_id', $service[0]->id)
                            ->where('period', $period)
                            ->get();
                        if (count($find) == 0) {
                            $new = new ClientServiceRated();
                            $new->client_id = $client_id;
                            $new->service_id = $service[0]->id;
                            $new->rated = $rated;
                            $new->period = $period;
                            $new->save();
                        } else {
                            $find = ClientServiceRated::find($find[0]['id']);
                            $find->rated = $rated;
                            $find->save();
                        }
                    });
                });
                return Response::json(['success' => true]);
            }
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

}

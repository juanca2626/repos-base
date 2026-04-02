<?php

namespace App\Http\Controllers;

use App\Client;
use App\Country;
use App\Jobs\StoreServiceRateClientsAssociations;
use App\Language;
use App\Market;
use App\ServiceRateAssociation;
use App\Http\Traits\JobStatusRegister;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ServiceRateAssociationController extends Controller
{
    use JobStatusRegister;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($service_rate_id, Request $request)
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

        $rate_associations = ServiceRateAssociation::where('service_rate_id', $service_rate_id)->get([
            'id',
            'service_rate_id',
            'entity',
            'object_id',
            'except'
        ]);

        foreach ($rate_associations as $association) {
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

                $country['name'] = '['.$country['iso'].'] - '.$country['translations'][0]['value'];
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


    /**
     * Store a newly created resource in storage.
     *
     * @param $service_rate_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($service_rate_id, Request $request)
    {
        $regions = $request->input('regions');
        $countries = $request->input('countries');
        $except_country = $request->input('except_country');
        $clients = $request->input('clients');
        $except_client = $request->input('except_client');
        $year = Carbon::now()->format('Y');

        $job_status = $this->checkStatusJobExecute('ServiceRateAssociation', $service_rate_id, null, $year);

        if (!$job_status) {
            $find_rows = ServiceRateAssociation::where('service_rate_id', $service_rate_id)->get(['id']);


            if ($find_rows->count() > 0) {
                DB::table('service_rate_associations')->whereIn('id', $find_rows->pluck('id'))->delete();
            }

            if (count($regions) > 0) {
                $regions = collect($regions)->unique();
                foreach ($regions as $region) {
                    $new_rate_plan_association = new ServiceRateAssociation();
                    $new_rate_plan_association->service_rate_id = $service_rate_id;
                    $new_rate_plan_association->entity = 'Market';
                    $new_rate_plan_association->object_id = $region['value'];
                    $new_rate_plan_association->save();
                }
            }

            if (count($countries) > 0) {
                $countries = collect($countries)->unique();
                foreach ($countries as $country) {
                    $new_rate_plan_association = new ServiceRateAssociation();
                    $new_rate_plan_association->service_rate_id = $service_rate_id;
                    $new_rate_plan_association->entity = 'Country';
                    $new_rate_plan_association->except = $except_country;
                    $new_rate_plan_association->object_id = $country['id'];
                    $new_rate_plan_association->save();
                }
            }

            if (count($clients) > 0) {
                $clients = collect($clients)->unique();
                foreach ($clients as $client) {
                    $new_rate_plan_association = new ServiceRateAssociation();
                    $new_rate_plan_association->service_rate_id = $service_rate_id;
                    $new_rate_plan_association->entity = 'Client';
                    $new_rate_plan_association->except = $except_client;
                    $new_rate_plan_association->object_id = $client['code'];
                    $new_rate_plan_association->save();
                }
            }

            $this->store_job('ServiceRateAssociation', $service_rate_id, $request->all(), null, $year);

            StoreServiceRateClientsAssociations::dispatch($service_rate_id, $year, Auth::id());


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

}

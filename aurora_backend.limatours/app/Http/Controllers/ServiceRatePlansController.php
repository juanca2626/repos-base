<?php

namespace App\Http\Controllers;

use App\Jobs\DuplicateServiceClients;
use App\ServiceClient;
use App\ServiceClientRatePlan;
use App\ServiceRate;
use App\ServiceRatePlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ServiceRatePlansController extends Controller
{

    /**
     * @param $service_rate
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($service_rate, $year, Request $request)
    {

        $period = ($year != '') ? $year : Carbon::now()->format('Y');
        $service_rate_plans = ServiceRatePlan::with(['user', 'policy'])
            ->where('service_rate_id', $service_rate)
            ->whereYear('date_from', '>=', $period)
            ->whereYear('date_from', '<=', $period)
            ->orderBy('pax_from')
            ->orderBy('date_from')
            ->get();

        $data = [
            'data' => $service_rate_plans,
            'success' => true
        ];

        return Response::json($data);
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rates_plan' => 'required',
        ]);

        $has_client = $request->has('has_client') ? $request->input('has_client') : false;
        if ($validator->fails()) {
            $response = ['success' => false];
        } else {

            $countUpdateIds = count($request->input('updateIds'));
            if ($countUpdateIds > 0) {
                foreach ($request->input('updateIds') as $idForDelete) {
                    ServiceRatePlan::find($idForDelete)->forceDelete();
                }
            }

            $validate_range = $this->validateRangeRate($request->input('data'),
                $request->input('rates_plan'), $request->input('dates_from'),
                $request->input('dates_to'));

            if ($validate_range) {
                $response = ['success' => false, 'message' => 'RATE_RANGE_EXIST'];
                return Response::json($response);
            }

            foreach ($request->input('data') as $s) {

                $service_rate_plan = new ServiceRatePlan();
                $service_rate_plan->service_rate_id = $request->input('rates_plan');
                // si lo crea un cliente se pone por default proveedor Lito Lima
                $service_rate_plan->user_id = ($has_client) ? 3182: $request->input('user_id');
                // si lo crea un cliente se pone por default la politica general lito
                $service_rate_plan->service_cancellation_policy_id =  ($has_client) ? 1: $s['service_cancellation_policy_id'];
                $service_rate_plan->monday = (int)$request->input('monday');
                $service_rate_plan->tuesday = (int)$request->input('tuesday');
                $service_rate_plan->wednesday = (int)$request->input('wednesday');
                $service_rate_plan->thursday = (int)$request->input('thursday');
                $service_rate_plan->friday = (int)$request->input('friday');
                $service_rate_plan->saturday = (int)$request->input('saturday');
                $service_rate_plan->sunday = (int)$request->input('sunday');
                $service_rate_plan->date_from =
                    date("Y-m-d", strtotime(str_replace('/', '-', $request->input('dates_from'))));
                $service_rate_plan->date_to =
                    date("Y-m-d", strtotime(str_replace('/', '-', $request->input('dates_to'))));
                $service_rate_plan->pax_from = $s['pax_from'];
                $service_rate_plan->pax_to = $s['pax_to'];
                $service_rate_plan->price_adult = $s['adult'];
                $service_rate_plan->price_child = $s['child'];
                $service_rate_plan->price_infant = $s['infant'];
                $service_rate_plan->price_guide = $s['guide'];
                $service_rate_plan->status = 1;
                $service_rate_plan->flag_migrate = 0;
                $service_rate_plan->save();
            }

            $response = ['success' => true];
        }

        return Response::json($response);
    }

    public function update_ranges(Request $request, $service_rate_id)
    {
        $range_from = $request->input('range_from');
        $range_to = $request->input('range_to');
        $policy_id = $request->input('policy_id');
        $provider_id = $request->input('provider_id');
        $year = $request->input('year');
        $update = [];
        if (empty($provider_id) and !empty($policy_id)) {
            $update = [
                "service_cancellation_policy_id" => $policy_id
            ];
        }

        if (empty($policy_id) and !empty($provider_id)) {
            $update = [
                "user_id" => $provider_id
            ];
        }

        if (!empty($provider_id) and !empty($policy_id)) {
            $update = [
                "service_cancellation_policy_id" => $policy_id,
                "user_id" => $provider_id
            ];
        }

        if (count($update) > 0) {
            ServiceRatePlan::where('service_rate_id', $service_rate_id)
                ->whereYear('date_from', '>=', $year)
                ->whereYear('date_from', '<=', $year)
                ->where('pax_from', '>=', $range_from)
                ->where('pax_to', '<=', $range_to)
                ->update($update);
            return Response::json(['success' => true]);

        } else {
            return Response::json(['success' => false]);
        }


    }

    public function validateRangeRate($range_rate, $service_rate_id, $date_from, $date_to)
    {
        $response = false;
        $date_from = date("Y-m-d", strtotime(str_replace('/', '-', $date_from)));
        $date_to = date("Y-m-d", strtotime(str_replace('/', '-', $date_to)));
        foreach ($range_rate as $s) {
            $pax_from = $s['pax_from'];
            $pax_to = $s['pax_to'];
            $count = DB::select(
                DB::raw("select * from service_rate_plans where service_rate_id = '$service_rate_id'
                        and (
                            (
                                ('$date_from' >= date_from and '$date_from' <= date_to) or
		                        ('$date_to' >= date_from and '$date_to' <= date_to)
                            ) or
                            (
                                (date_from >= '$date_from' and date_to >= '$date_from') and
		                        (date_from <= '$date_to' and date_to <= '$date_to')
                            )
                        )and (
                            ('$pax_from' >= pax_from and '$pax_from' <= pax_to) or
	                        ('$pax_to' >= pax_from and '$pax_to' <= pax_to)
                        ) and deleted_at is null limit 1
                        "));
            if (count($count) > 0) {
                $response = true;
                break;
            }
        }
        return $response;
    }

    public function ratesByService(Request $request)
    {
        $service_id = $request->input('service_id');
        $period = $request->input('period');

        $rates = ServiceRate::select('id', 'name')
            ->where('service_id', $service_id)
            ->where('status', 1)
            ->with([
                'clients_rate_plan' => function ($query) use ($period) {
                    $query->where('period', $period);
                }
            ])
            ->get();

        $rates_frontend = [];

        for ($i = 0; $i < $rates->count(); $i++) {
            $rates_frontend[$i]["service_rate_id"] = $rates[$i]["id"];
            $rates_frontend[$i]["name"] = $rates[$i]["name"];
            $rates_frontend[$i]["selected"] = false;
            $rates_frontend[$i]["clients_rate_plan"] = $rates[$i]["clients_rate_plan"];
        }
        $data = [
            'data' => $rates_frontend,
            'success' => true
        ];

        return Response::json($data);
    }

    public function storeClientRatePlan(Request $request)
    {
        $service_clients = $request->input('service_clients');

        $service_rate_id = $request->input('service_rate_id');

        $period = $request->input('period');

        date_default_timezone_set("America/Lima");

        DB::transaction(function () use ($service_clients, $service_rate_id, $period) {

            foreach ($service_clients as $client) {

                if ($client["selected"] && $client["service_client_rate_plan_id"] === null) {
                    DB::table('service_client_rate_plans')->insert([
                        'service_rate_id' => $service_rate_id,
                        'client_id' => $client["id"],
                        'markup' => $client["markup"],
                        'period' => $period,
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s")
                    ]);
                }
                if ($client["selected"] && $client["service_client_rate_plan_id"] !== "") {
                    DB::table('service_client_rate_plans')
                        ->where('id', $client["service_client_rate_plan_id"])
                        ->update([
                            'markup' => $client["markup"],
                            'updated_at' => date("Y-m-d H:i:s")
                        ]);
                }
                if (!$client["selected"] && $client["service_client_rate_plan_id"] !== "") {
                    ServiceClientRatePlan::where('id', $client["service_client_rate_plan_id"])->each(function ($rate) {
                        $rate->delete();
                    });
                }
            }
        });

        $data = [
            'success' => true
        ];

        return Response::json($data);
    }

    public function ratesPlansByService(Request $request)
    {
        $service_id = $request->input('service_id');

        $rates_plans = ServiceRate::where('service_id', $service_id)->where('status', 1)->get();

        $data = [
            'data' => $rates_plans,
            'success' => true
        ];

        return Response::json($data);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($service_rate_plan_id)
    {
        $plan = ServiceRatePlan::find($service_rate_plan_id);
        $plan->delete();
        return Response::json(['success' => true]);
    }

    public function duplicateSaleRate($service_id, Request $request)
    {
        set_time_limit(0);
        $year_from = $request->post('year_from');
        $year_to = $request->post('year_to');

        $rate_plans = ServiceRate::where('service_id', $service_id)->get();

        foreach ($rate_plans as $rate_plan) {
            //Eliminar todos client rate plans from del year_to si existe
            ServiceClientRatePlan::where('service_rate_id', $rate_plan->id)->where('period', $year_to)->delete();
            //listado de client_rate_plan de year from
            $client_rate_plans = ServiceClientRatePlan::where('service_rate_id', $rate_plan->id)->where('period',
                $year_from)->get();

            //Insertar los client_rate_plan con year_to nuevos
            foreach ($client_rate_plans as $client_rate_plan) {
                $new_service_client_rate_plan = new ServiceClientRatePlan();
                $new_service_client_rate_plan->period = $year_to;
                $new_service_client_rate_plan->client_id = $client_rate_plan->client_id;
                $new_service_client_rate_plan->service_rate_id = $client_rate_plan->service_rate_id;
                $new_service_client_rate_plan->save();
            }
        }
        //Eliminar todos los hotel_clients del year_to si existe
        ServiceClient::where('service_id', $service_id)->where('period', $year_to)->delete();
        //listado de hotel_clients de year_from
        ServiceClient::where('service_id', $service_id)->where('period', $year_from)
            ->chunk(1000, function ($service_clients) use ($service_id, $year_to) {
                DuplicateServiceClients::dispatch($service_id, $service_clients,
                    $year_to)->onQueue('duplicate_client_service');
            });
        return Response::json(['success' => true]);
    }

}

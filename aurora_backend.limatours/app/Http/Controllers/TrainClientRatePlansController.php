<?php

namespace App\Http\Controllers;

use App\Client;
use App\TrainClientRatePlan;
use App\TrainMarkupRatePlan;
use App\TrainRate;
//use App\ServiceRatePlanCalendar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class TrainClientRatePlansController extends Controller
{
    public function index(Request $request)
    {
        $client_id = $request->input('client_id');
        $train_template_id = $request->input('train_template_id');
        $period = $request->input('period');

        $client_rate_ids = TrainClientRatePlan::where([
            'client_id' => $client_id,
            'period' => $period
        ])->pluck('train_rate_id');

        $rate_plans = TrainRate::select('id','name')->where('train_template_id', $train_template_id)->whereIn('id', $client_rate_ids)->get();

        $tarifas = array();
        foreach($rate_plans as $rate_plan){
            $rate_plan->selected = false;
            $tarifas[] = $rate_plan;
        }

        return Response::json(['success' => true, 'data' => $tarifas]);
    }

    public function consultSelected(Request $request)
    {
        $train_template_id = $request->input('train_template_id');
        $client_id = $request->input('client_id');
        $period = $request->input('period');

        $client_rate_ids = TrainClientRatePlan::where([
            'client_id' => $client_id,
            'period' => $period
        ])->pluck('train_rate_id');

        $rate_plans = TrainRate::select('id','name')->where('train_template_id', $train_template_id)->whereNotIn('id', $client_rate_ids)->get();

        $markupRates = TrainMarkupRatePlan::select(['markup','train_rate_id'])->where(['client_id' => $client_id, 'period' => $period])->get();

        $tarifas = array();
        foreach($rate_plans as $rate_plan){

            $markup = "";
            foreach($markupRates as $markupRate){
                if($markupRate->train_rate_id == $rate_plan->id){
                    $markup = $markupRate->markup;
                }
            }
            $rate_plan->markup = $markup;
            $rate_plan->selected = false;
            $tarifas[] = $rate_plan;
        }
        return Response::json(['success' => true, 'data' => $tarifas]);
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $period = $request->input('period');
            $client_id = $request->input('client_id');
            $train_rate_id = $request->input('train_rate_id');

            $rate_plans = new TrainClientRatePlan();
            $rate_plans->train_rate_id = $train_rate_id;
            $rate_plans->period = $period;
            $rate_plans->client_id = $client_id;
            $rate_plans->save();

            $this->deleteMarkupRatePlans($client_id, $train_rate_id, $period);

        });

        return Response::json(['success' => true]);
    }

    public function storeAll(Request $request)
    {
        DB::transaction(function () use ($request) {
            $train_template_id = $request->input('train_template_id');
            $client_id = $request->input('client_id');
            $period = $request->input('period');

            $client_rate_ids = TrainClientRatePlan::where([
                'client_id' => $client_id,
                'period' => $period
            ])->pluck('train_rate_id');

            $rate_plans = TrainRate::select('id', 'name')->where('train_template_id', $train_template_id)->whereNotIn('id',
                $client_rate_ids)->get();

            foreach ($rate_plans as $rate_plan) {
                $rate_plans = new TrainClientRatePlan();
                $rate_plans->train_rate_id = $rate_plan->id;
                $rate_plans->period = $period;
                $rate_plans->client_id = $client_id;
                $rate_plans->save();
                $this->deleteMarkupRatePlans($client_id, $rate_plan->id, $period);
            }
        });

        return Response::json(['success' => true]);
    }

    public function inverse(Request $request)
    {
        $client_id = $request->input('client_id');
        $period = $request->input('period');
        $train_rate_plan_id = $request->input('rate_plan_id');
        $client = Client::find($client_id);
        $client->train_rate_plans()->wherePivot('period', '=', $period)->detach($train_rate_plan_id);
        return Response::json(['success' => true]);
    }

    public function inverseAll(Request $request)
    {
        $client_id = $request->input('client_id');
        $train_template_id = $request->input('train_template_id');
        $period = $request->input('period');
        $clientRatePlans = TrainClientRatePlan::whereHas('trainRate', function ($query) use ($train_template_id) {
            $query->where('train_template_id', $train_template_id);
        })->where('period', $period)->where('client_id', $client_id)->get();
        foreach ($clientRatePlans as $clientRatePlan) {
            $client = Client::find($client_id);
            $client->train_rate_plans()
                ->wherePivot('period', '=', $period)->detach($clientRatePlan->train_rate_id);
//                ->wherePivot('period', '=', $period)->detach($clientRatePlan->rate_plan_id);
        }
        return Response::json(['success' => true]);
    }

    public function deleteMarkupRatePlans($client_id, $train_rate_id, $period)
    {
        $ratesMarkup = TrainMarkupRatePlan::where('client_id', $client_id)->where('train_rate_id',
            $train_rate_id)->where('period', $period)->first();
        if (is_object($ratesMarkup)) {
            $ratesMarkup->delete();
        }
    }

//    public function destroy(Request $request)
//    {
//        $id = $request->input('id');
//
//        ServiceClientRatePlan::where('id', $id)->delete();
//
//        return Response::json(['success' => true,]);
//    }

    public function update(Request $request)
    {
        $rate_plan_id = $request->input('rate_plan_id');
        $client_id = $request->input('client_id');
        $markup = $request->input('markup');
        $period = $request->input('period');

        $ratesMarkup = TrainMarkupRatePlan::where('client_id', $client_id)->where('train_rate_id',
            $rate_plan_id)->where('period', $period)->first();

        if (is_object($ratesMarkup)) {
            $ratesMarkup->markup = $markup;
            $ratesMarkup->save();
        } else {
            $ratesMarkup = new TrainMarkupRatePlan();
            $ratesMarkup->client_id = $client_id;
            $ratesMarkup->train_rate_id = $rate_plan_id;
            $ratesMarkup->period = $period;
            $ratesMarkup->markup = $markup;
            $ratesMarkup->save();
        }

        return Response::json(['success' => true]);
    }

}

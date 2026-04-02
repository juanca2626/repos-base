<?php

namespace App\Http\Controllers;

use App\TrainRate;
use App\TrainRatePlan;
use App\TrainType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class TrainRatePlansController extends Controller
{
    public function index($train_rate_id, $year, Request $request)
    {

        $train_type = $request->input('train_type');
        $period = ($year != '') ? $year : Carbon::now()->format('Y');
        $train_rate_plans = TrainRatePlan::with(['train_type','policy'])
            ->where('train_rate_id', $train_rate_id)
            ->whereHas('train_type', function ($q) use ($train_type) {
                $q->where('abbreviation', $train_type);
            })
            ->whereYear('date_from', '>=', $period)
            ->orderBy('pax_from')
            ->orderBy('date_from')
            ->get();

        $data = [
            'data' => $train_rate_plans,
            'success' => true
        ];

        return Response::json($data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rates_plan' => 'required'
        ]);

        if ($validator->fails()) {
            $response = ['success' => false];
        } else {

            $countUpdateIds = count($request->input('updateIds'));
            if ($countUpdateIds > 0) {
                foreach ($request->input('updateIds') as $idForDelete) {
                    TrainRatePlan::find($idForDelete)->forceDelete();
                }
            }

            $train_type_id = TrainType::where('abbreviation',$request->input('typeTrain'))->first()->id;

            foreach ($request->input('data') as $s) {

                $train_rate_plan = new TrainRatePlan();
                $train_rate_plan->train_rate_id = $request->input('rates_plan');
                $train_rate_plan->train_type_id = $train_type_id;
                $train_rate_plan->train_cancellation_policy_id = $request->input('policy_id');
                $train_rate_plan->date_from =
                    date("Y-m-d", strtotime(str_replace('/', '-', $request->input('dates_from'))));
                $train_rate_plan->date_to =
                    date("Y-m-d", strtotime(str_replace('/', '-', $request->input('dates_to'))));
                $train_rate_plan->monday = $request->input('monday');
                $train_rate_plan->tuesday = $request->input('tuesday');
                $train_rate_plan->wednesday = $request->input('wednesday');
                $train_rate_plan->thursday = $request->input('thursday');
                $train_rate_plan->friday = $request->input('friday');
                $train_rate_plan->saturday = $request->input('saturday');
                $train_rate_plan->sunday = $request->input('sunday');
                $train_rate_plan->pax_from = $s['pax_from'];
                $train_rate_plan->pax_to = $s['pax_to'];
                $train_rate_plan->price_adult = $s['adult'];
                $train_rate_plan->price_child = $s['child'];
                $train_rate_plan->price_infant = $s['infant'];
                $train_rate_plan->price_guide = $s['guide'];
                $train_rate_plan->status = 1;
                $train_rate_plan->frequency_code = $request->input('frequency_code');
                $train_rate_plan->equivalence_code = $request->input('equivalence_code');
                $train_rate_plan->save();
            }

            $response = ['success' => true];
        }

        return Response::json($response);
    }

    public function ratesPlansByTrain(Request $request)
    {
        $train_template_id = $request->input('train_template_id');

        $rates_plans = TrainRate::where('train_template_id', $train_template_id)->where('status', 1)->get();

        $data = [
            'data' => $rates_plans,
            'success' => true
        ];

        return Response::json($data);
    }

    public function destroy($train_rate_plan_id)
    {
        $plan = TrainRatePlan::find($train_rate_plan_id);
        $plan->delete();
        return Response::json(['success' => true]);
    }

    public function updateTrainTypeId($train_rate_id, Request $request)
    {
        $train_type_abbreviation = $request->input('train_type_abbreviation');

        $train_type_id = TrainType::where('abbreviation',$train_type_abbreviation)->first()->id;

        $update = TrainRatePlan::where('train_rate_id', $train_rate_id)->update(
            [
                "train_type_id"=>$train_type_id,
                "train_type_id_undefined"=>0
            ]
        );
        return Response::json(['success' => $update]);
    }

}

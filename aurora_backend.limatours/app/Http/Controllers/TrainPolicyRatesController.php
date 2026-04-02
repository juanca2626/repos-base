<?php

namespace App\Http\Controllers;

use App\TrainPolicyRate;
use App\Http\Traits\Translations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TrainPolicyRatesController extends Controller
{
    use Translations;

    public function index(Request $request)
    {
        $lang = $request->input("lang");
        $train_template_id = $request->input("train_template_id");
        $policiesRates = TrainPolicyRate::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'trainratepolicy');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])
        ->with('cancellation_policies')
        ->where('train_template_id', $train_template_id)
        ->orWhereNull('train_template_id')->get();

        return Response::json(['success' => true, 'data' => $policiesRates]);
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
            'train_template_id' => 'required|integer|numeric',
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
            $policyRate = new TrainPolicyRate();
            $policyRate->name = $request->input("name");
            $policyRate->status = 1;
            $policyRate->description = $request->input('translDesc')[1]["policy_description"];
            $policyRate->days_apply = $request->input("select_day");
            $policyRate->train_template_id = $request->input("train_template_id");
            $policyRate->save();

            $cancellation_policies = $request->input('cancellation_policies');
            $created_at = date("Y-m-d H:i:s");
            foreach ( $cancellation_policies as $p_c ){
                DB::table('train_cancellation_policy_train_policy_rate')->insert([
                    "train_policy_rate_id" =>$policyRate->id,
                    "train_cancellation_policy_id" =>$p_c["code"],
                    "created_at" => $created_at,
                    "updated_at" => $created_at
                ]);
            }

            $this->saveTranslation($request->input('translDesc'), 'trainratepolicy', $policyRate->id);

            return Response::json(['success' => true, 'object_id' => $policyRate->id]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $policiesRates = TrainPolicyRate::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'trainratepolicy');
                $query->where('object_id', $id);
            }
        ])
        ->with(['cancellation_policies'=> function($query){
            $query->select('train_cancellation_policies.id as code','train_cancellation_policies.name as label');
        }])
            ->where('id', $id)->get();

        return Response::json(['success' => true, 'data' => $policiesRates]);
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
        $policyRate = TrainPolicyRate::find($request->input("id"));
        $policyRate->name = $request->input("name");
        $policyRate->days_apply = $request->input("select_day");
        $policyRate->description = $request->input("description");
        $policyRate->cancellation_policies()->sync(array_column($request->input("cancellation_policies",[]),'code'));
        $policyRate->save();

        $this->saveTranslation($request->input('translDesc'), 'trainratepolicy', $policyRate->id);

        return Response::json(['success' => true]);
    }

    public function updateStatus($id, Request $request)
    {
        $train = TrainPolicyRate::find($id);
        if ($request->input("status")) {
            $train->status = false;
        } else {
            $train->status = true;
        }
        $train->save();
        return Response::json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
//        $rataPlanCalendaries = RatesPlansCalendarys::where('policies_rate_id', $id)->limit(5)->get();
//
//        if(count($rataPlanCalendaries)>0){
//            return Response::json(['success' => false, 'message' => 'El registro que esta eliminando tiene tarifas relacionadas']);
//        }else {

        DB::table('train_cancellation_policy_train_policy_rate')->where([
            "train_policy_rate_id" =>$id
        ])->delete();

        $policiesRates = TrainPolicyRate::find($id);
        $policiesRates->delete();

        return Response::json(['success' => true]);
//        }
    }

    public function selectBox(Request $request)
    {
        $trainTemplateID = $request->input('train_template_id');
        $policies = TrainPolicyRate::select('id', 'name', 'days_apply')
            ->where('train_template_id', $trainTemplateID)->orWhereNull('train_template_id')
            ->get();

        return Response::json(['success' => true, 'data' => $policies]);
    }

}

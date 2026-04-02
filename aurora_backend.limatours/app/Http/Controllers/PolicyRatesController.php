<?php

namespace App\Http\Controllers;

use App\PoliciesRates;
use App\RatesPlansCalendarys;
use App\Http\Traits\Translations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class PolicyRatesController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:policyrates.read')->only('index');
        $this->middleware('permission:policyrates.create')->only('store');
        $this->middleware('permission:policyrates.update')->only('update');
        $this->middleware('permission:policyrates.delete')->only('delete');
    }


    public function index(Request $request)
    {
        $lang = $request->input("lang");
        $hotel_id = $request->input("hotel_id");
        $policiesRates = PoliciesRates::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'rate_policies');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
            ,
            'policiesCancelation'
        ])->with('policiesCancelation')->where('hotel_id', $hotel_id)->get();

        return \response()->json(['success' => true, 'data' => $policiesRates]);
    }


    public function store(Request $request)
    {
        $arrayErrors = [];
        $countErrors = 0;

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'min_length_stay' => 'required|integer|numeric',
            'max_length_stay' => 'required|integer|numeric',
            'max_occupancy' => 'required|integer|numeric',
            'hotel_id' => 'required|integer|numeric',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                array_push($arrayErrors, $error);
            }

            $countErrors++;
        }
        if ($countErrors > 0) {
           return  \response()->json(['success' => false, 'errors' => $arrayErrors]);
        } else {
            $policyRate = new PoliciesRates();
            $policyRate->name = $request->input("name");
            $policyRate->status = 1;
            $policyRate->description = $request->input("description");
            $policyRate->min_length_stay = $request->input("min_length_stay");
            $policyRate->max_length_stay = $request->input("max_length_stay");
            $policyRate->max_occupancy = 100;
            $policyRate->days_apply = $request->input("select_day");
            $policyRate->max_ab_offset = 0;
            $policyRate->min_ab_offset = 0;
            $policyRate->hotel_id = $request->input("hotel_id");
            $policyRate->save();

            $policyRate->policies_cancelation()->sync(array_column($request->input("policies_cancelation",[]),'code'));

            $this->saveTranslation($request->input('translDesc'), 'rate_policies', $policyRate->id);

            return \response()->json(['success' => true, 'object_id' => $policyRate->id]);
        }
    }


    public function show($id)
    {
        $policiesRates = PoliciesRates::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'rate_policies');
                $query->where('object_id', $id);
            }
            ,
            'policiesCancelation'
        ])->with(['policiesCancelation'=> function($query){
            $query->select('policies_cancelations.id as code','policies_cancelations.name as label');
        }])->where('id', $id)->get();

        return \response()->json(['success' => true, 'data' => $policiesRates]);
    }


    public function update(Request $request, $id)
    {
        $policyRate = PoliciesRates::find($request->input("id"));
        $policyRate->name = $request->input("name");
        $policyRate->min_length_stay = $request->input("min_length_stay");
        $policyRate->max_length_stay = $request->input("max_length_stay");
        $policyRate->max_occupancy = 100;
        $policyRate->days_apply = $request->input("select_day");
        $policyRate->description = $request->input("description");
        $policyRate->max_ab_offset = 0;
        $policyRate->min_ab_offset = 0;
        $policyRate->policies_cancelation()->sync(array_column($request->input("policies_cancelation",[]),'code'));
        $policyRate->save();

        $this->saveTranslation($request->input('translDesc'), 'rate_policies', $policyRate->id);

        return \response()->json(['success' => true]);
    }

    public function updateStatus($id, Request $request)
    {
        $hotel = PoliciesRates::find($id);
        if ($request->input("status")) {
            $hotel->status = false;
        } else {
            $hotel->status = true;
        }
        $hotel->save();
        return \response()->json(['success' => true] , 200);
    }


    public function destroy($id)
    {


        $rataPlanCalendaries = RatesPlansCalendarys::where('policies_rate_id', $id)->limit(5)->get();

        if(count($rataPlanCalendaries)>0){
            return Response::json(['success' => false, 'message' => 'El registro que esta eliminando tiene tarifas relacionadas']);
        }else {

            $policiesRates = PoliciesRates::find($id);
            $policiesRates->delete();

            return \response()->json(['success' => true]);
        }
    }

    public function selectBox(Request $request)
    {
        $hotelID = $request->input('hotel_id');
        $policies = PoliciesRates::select('id', 'name', 'days_apply')
            ->where('hotel_id', $hotelID)
            ->get();

        return  \response()->json(['success' => true, 'data' => $policies]);
    }
}

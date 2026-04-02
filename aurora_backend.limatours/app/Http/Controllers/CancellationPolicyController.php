<?php

namespace App\Http\Controllers;

use App\CancellationPolicy;
use App\PolicyCancellationParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CancellationPolicyController extends Controller
{
    public function __construct()
    {
        // $this->middleware('permission:contacts.read')->only('index');
        // $this->middleware('permission:contacts.create')->only('store');
        // $this->middleware('permission:contacts.update')->only('update');
        // $this->middleware('permission:contacts.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $lang = $request->input("lang");
        $hotel_id = $request->input("hotel_id");
        $cancellations = CancellationPolicy::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'cancellationpolicy');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->where('hotel_id', $hotel_id)->get();

        return Response::json(['success' => true, 'data' => $cancellations]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $form = $request->input('form');
        $parameters = $request->input('parameters');

        $cancellationPolicy = new CancellationPolicy();
        $cancellationPolicy->name = $form['name'];
        $cancellationPolicy->hotel_id = $form['hotel_id'];

        if ($cancellationPolicy->save()) {
            foreach ($parameters as $key => $value) {
                $dataInsert[] = [
                    'min_day' => $value['min_day'],
                    'max_day' => $value['max_day'],
                    'policy_cancellation_id' => $cancellationPolicy->id,
                    'penalty_id' => $value['penalty_id'],
                    'amount' => $value['amount'],
                    'tax' => $value['tax'],
                    'service' => $value['service'],
                    'created_at' => date('Y-m-d H:m:s')
                ];
            }
            $cancelationParameter = PolicyCancellationParameter::insert($dataInsert);
        }
        return Response::json(['success' => true]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $cancellation = CancellationPolicy::with('translations')->where('id', "=", $id)->get();

        return Response::json(['success' => true, 'data' => $cancellation]);
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
        $form = $request->input('form');
        $parameters = $request->input('parameters');

        $cancellationPolicy = CancellationPolicy::find($form['id']);
        $cancellationPolicy->name = $form['name'];

        if ($cancellationPolicy->save()) {
            foreach ($parameters as $key => $value) {
//                \Log::debug('valor fuera del empty');
//                \Log::debug($value['id']);
                if (!empty($value['id'])) {
                    $updateParam = PolicyCancellationParameter::where('id', $value['id'])->first();
                    $updateParam->min_day = $value['min_day'];
                    $updateParam->max_day = $value['max_day'];
                    $updateParam->penalty_id = $value['penalty_id'];
                    $updateParam->amount = $value['amount'];
                    $updateParam->tax = $value['tax'];
                    $updateParam->service = $value['service'];
                    $updateParam->min_day = $value['min_day'];
                    $updateParam->save();
                } else {
//                    \Log::debug('ingreso al insert');
//                    \Log::debug($value['id']);
                    $insertParam = new PolicyCancellationParameter();
                    $insertParam->min_day = $value['min_day'];
                    $insertParam->max_day = $value['max_day'];
                    $insertParam->penalty_id = $value['penalty_id'];
                    $insertParam->amount = $value['amount'];
                    $insertParam->tax = $value['tax'];
                    $insertParam->service = $value['service'];
                    $insertParam->policy_cancellation_id = $cancellationPolicy->id;
                    $insertParam->save();
                }
            }
        }

        return Response::json(['success' => true]);
    }

    public function selectBox()
    {
        $canellationPolicies = CancellationPolicy::select('id', 'name')->get();
        $result = [];
        foreach ($canellationPolicies as $policy) {
            array_push($result, ['text' => $policy->name, 'value' => $policy->id]);
        }
        return Response::json(['success' => true, 'data' => $result]);
    }

    public function searchParameters(Request $request)
    {
        $parameters = PolicyCancellationParameter::where('policy_cancellation_id', $request->id)->get();
        $result = [];

        foreach ($parameters as $key => $param) {
            array_push($result, [
                'id' => $param->id,
                'min_day' => $param->min_day,
                'max_day' => $param->max_day,
                'amount' => $param->amount,
                'penalty_id' => $param->penalty_id,
                'service' => $param->service,
                'tax' => $param->tax,
                'count' => ($key + 1),
                'details' => '',
            ]);
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
        $deleteParam = PolicyCancellationParameter::where('policy_cancellation_id', $id)->delete();
        $cancellationPolicy = CancellationPolicy::find($id);

        $cancellationPolicy->delete();

        return Response::json(['success' => true]);
    }
}

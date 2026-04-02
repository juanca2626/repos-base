<?php

namespace App\Http\Controllers;

use App\PoliciesCancelations;
use App\PolicyCancellationParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class PolicyCancelationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:policycancelations.read')->only('index');
        $this->middleware('permission:policycancelations.create')->only('store');
        $this->middleware('permission:policycancelations.update')->only('update');
        $this->middleware('permission:policycancelations.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $lang = $request->input("lang");
        $hotel_id = $request->input("hotel_id");
        $cancellations = PoliciesCancelations::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'cancellationpolicy');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->where('hotel_id', $hotel_id)
            ->orWhereNull('hotel_id')->where('code','!=','CANCELLATION_POLICY_CHANNELS')->get();

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
        try {
            DB::transaction(function () use ($request) {
                $form = $request->input('form');
                $parameters = $request->input('parameters');

                $type_fit = $form['type_fit'];
                $min_num = $form['min_num'];
                $max_num = $form['max_num'];

                $type = $form['type'];

                $cancellationPolicy = new PoliciesCancelations();
                $cancellationPolicy->name = $form['name'];
                $cancellationPolicy->status = 1;
                $cancellationPolicy->hotel_id = $form['hotel_id'];

                if ($type_fit) {
                    $cancellationPolicy->type_fit = $type_fit;
                    $cancellationPolicy->min_num = $min_num;
                    $cancellationPolicy->max_num = $max_num;
                } else {
                    $cancellationPolicy->type_fit = null;
                }
                if ($type)
                {
                    $cancellationPolicy->type = $type;
                }else{
                    $cancellationPolicy->type = 'cancellations';
                }

                if ($cancellationPolicy->save()) {
                    $this->savePenalties($cancellationPolicy->id, $parameters);
                } else {
                    throw new \Exception('There is a problem saving cancellation policy record');
                }
            });
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'error' => $exception->getMessage()]);
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
        $cancellation = PoliciesCancelations::with('translations')->where('id', "=", $id)->get();

        return Response::json(['success' => true, 'data' => $cancellation]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(Request $request, $id)
    {
//        try {
//            DB::transaction(function () use ($request, $id) {
                $form = $request->input('form');
                $parameters = $request->input('parameters');

                $type_fit = $form['type_fit'];
                $min_num = $form['min_num'];
                $max_num = $form['max_num'];

                $cancellationPolicy = PoliciesCancelations::find($form['id']);
                $cancellationPolicy->name = $form['name'];

                $type = $form['type'];

                if ($type_fit) {
                    $cancellationPolicy->type_fit = $type_fit;
                    $cancellationPolicy->min_num = $min_num;
                    $cancellationPolicy->max_num = $max_num;
                } else {
                    $cancellationPolicy->type_fit = null;
                }

                if ($type)
                {
                    $cancellationPolicy->type = $type;
                }else{
                    $cancellationPolicy->type = 'cancellations';
                }

                if ($cancellationPolicy->save()) {
                    $this->savePenalties($cancellationPolicy->id, $parameters);
                } else {
                    throw new \Exception('There is a problem saving cancellation policy record');
                }
//            });
//        } catch (\Exception $exception) {
//            return Response::json(['success' => false, 'error' => $exception->getMessage()]);
//        }

        return Response::json(['success' => true]);
    }

    /**
     * @param $policy_cancelation_id
     * @param $parameters
     * @throws \Exception
     */
    private function savePenalties($policy_cancelation_id, $parameters)
    {
        foreach ($parameters as $key => $value) {
            if ($value['penalty_id'] == '1' or $value['penalty_id'] == '2') {
                if (is_null($value['amount']) or $value['amount'] === "") {

                    throw new \Exception('field "amount" required');
                }

                switch ($value['penalty_id']) {
                    case '1';
                        $validator = Validator::make($value, [
                            'amount' => 'numeric|integer'
                        ]);

                        if ($validator->fails()) {
                            throw new \Exception('field "amount" must be integer');
                        }
                        break;
                    case '2';
                        $validator = Validator::make($value, [
                            'amount' => 'numeric'
                        ]);

                        if ($validator->fails()) {
                            throw new \Exception('field "amount" must be numeric');
                        }
                        break;
                }
            }

            if (!empty($value['id'])) {
                $parameter = PolicyCancellationParameter::find($value['id']);
            } else {
                $parameter = new PolicyCancellationParameter();
                $parameter->policy_cancelation_id = $policy_cancelation_id;
            }

            $parameter->min_day = $value['min_day'];
            $parameter->max_day = $value['max_day'];
            $parameter->penalty_id = $value['penalty_id'];
            $parameter->amount = $value['amount'];
            $parameter->tax = $value['tax'];
            $parameter->service = $value['service'];
            $parameter->save();
        }
    }

    public function updateStatus($id, Request $request)
    {
        $hotel = PoliciesCancelations::find($id);
        if ($request->input("status")) {
            $hotel->status = false;
        } else {
            $hotel->status = true;
        }
        $hotel->save();
        return Response::json(['success' => true]);
    }

    public function selectBox(Request $request)
    {
        $hotel_id = $request->input('hotel_id');
        $policiesCancelations = PoliciesCancelations::select('id', 'name')
            ->where(['status' => 1, 'hotel_id' => $hotel_id])
            ->orWhereNull('hotel_id')->where('code','!=','CANCELLATION_POLICY_CHANNELS')->get();

        $result = [];
        foreach ($policiesCancelations as $policy) {
            array_push($result, ['text' => $policy->name, 'value' => $policy->id]);
        }
        return Response::json(['success' => true, 'data' => $result]);
    }

    public function searchParameters(Request $request)
    {
        $parameters = PolicyCancellationParameter::where('policy_cancelation_id', $request->id)->get();
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

        $cancellationPolicy = PoliciesCancelations::with('policy_rates')->find($id);

        if (count($cancellationPolicy->policy_rates) > 0) {
            return Response::json(['success' => false, 'message' => 'El registro que esta eliminando tiene politicas de tarifas relacionadas']);
        } else {
            $deleteParam = PolicyCancellationParameter::where('policy_cancelation_id', $id)->delete();
            $cancellationPolicy = PoliciesCancelations::find($id);
            $cancellationPolicy->delete();
            return Response::json(['success' => true]);
        }


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroyParameters($id)
    {

        $parameter = PolicyCancellationParameter::find($id);

        $parameter->delete();

        return Response::json(['success' => true]);
    }
}

<?php

namespace App\Http\Controllers;

use App\TrainCancellationPolicy;
use App\TrainCancellationParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class TrainCancellationPoliciesController extends Controller
{
    public function index(Request $request)
    {
        $cancellations = TrainCancellationPolicy::all();

        return Response::json(['success' => true, 'data' => $cancellations]);
    }

    public function show($id)
    {
        $cancellation = TrainCancellationPolicy::where('id', "=", $id)->get();

        return Response::json(['success' => true, 'data' => $cancellation]);
    }

    public function updateStatus($id, Request $request)
    {
        $policy = TrainCancellationPolicy::find($id);
        if ($request->input("status")) {
            $policy->status = false;
        } else {
            $policy->status = true;
        }
        $policy->save();
        return Response::json(['success' => true]);
    }

    private function savePenalties($train_cancellation_id, $parameters)
    {
        foreach ($parameters as $key => $value) {
            if ($value['penalty_id'] == '1' or $value['penalty_id'] == '2') {
                if (!$value['amount']) {
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
                $parameter = TrainCancellationParameter::find($value['id']);
            } else {
                $parameter = new TrainCancellationParameter();
                $parameter->train_cancellation_id = $train_cancellation_id;
            }

            $parameter->min_day = $value['min_day'];
            $parameter->max_day = $value['max_day'];
            $parameter->service_penalty_id = $value['penalty_id'];
            $parameter->amount = $value['amount'];
            $parameter->tax = $value['tax'];
            $parameter->service = $value['service'];
            $parameter->save();
        }
    }

    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $form = $request->input('form');
                $parameters = $request->input('parameters');

                $min_num = $form['min_num'];
                $max_num = $form['max_num'];

                $cancellationPolicy = new TrainCancellationPolicy();
                $cancellationPolicy->name = $form['name'];
                $cancellationPolicy->status = 1;
                $cancellationPolicy->min_num = $min_num;
                $cancellationPolicy->max_num = $max_num;

                if ($cancellationPolicy->save()) {
                    $this->savePenalties($cancellationPolicy->id, $parameters); // REVISAR
                } else {
                    throw new \Exception('There is a problem saving cancellation policy record');
                }
            });
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'error' => $exception->getMessage()]);
        }

        return Response::json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        try {
            DB::transaction(function () use ($request, $id) {
                $form = $request->input('form');
                $parameters = $request->input('parameters');

                $min_num = $form['min_num'];
                $max_num = $form['max_num'];

                $cancellationPolicy = TrainCancellationPolicy::find($form['id']);
                $cancellationPolicy->name = $form['name'];
                $cancellationPolicy->min_num = $min_num;
                $cancellationPolicy->max_num = $max_num;

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

    public function destroy($id)
    {

        $cancellationPolicy = TrainCancellationPolicy::with('policy_rates')->find($id);

        if (count($cancellationPolicy->policy_rates) > 0) {
            return Response::json(['success' => false, 'message' => 'El registro que esta eliminando tiene politicas de tarifas relacionadas']);
        } else {
            TrainCancellationParameter::where('train_cancellation_id', $id)->delete();
            $cancellationPolicy = TrainCancellationPolicy::find($id);
            $cancellationPolicy->delete();
            return Response::json(['success' => true]);
        }


    }

    public function selectBox(Request $request)
    {
        $policiesCancelations = TrainCancellationPolicy::select('id', 'name')
            ->where(['status' => 1])->get();
//            ->orWhereNull('hotel_id')->where('code','!=','CANCELLATION_POLICY_CHANNELS')->get();

        $result = [];
        foreach ($policiesCancelations as $policy) {
            array_push($result, ['text' => $policy->name, 'value' => $policy->id]);
        }
        return Response::json(['success' => true, 'data' => $result]);
    }

    public function searchParameters(Request $request)
    {
        $parameters = TrainCancellationParameter::where('train_cancellation_id', $request->id)->get();
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

    public function destroyParameter($id)
    {

        $parameter = TrainCancellationParameter::find($id);

        $parameter->delete();

        return Response::json(['success' => true]);
    }
}

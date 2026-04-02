<?php

namespace App\Http\Controllers;

use App\ProgressBar;
use App\ServiceCancellationPolicies;
use App\ServicePoliticsParameter;
use App\ServiceRatePlan;
use App\Http\Traits\Translations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ServiceCancellationPoliciesController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:cancellationpolicies.read')->only('index');
        $this->middleware('permission:cancellationpolicies.create')->only('store');
        $this->middleware('permission:cancellationpolicies.update')->only('update');
        $this->middleware('permission:cancellationpolicies.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $lang = $request->input("lang");
        $cancellations = ServiceCancellationPolicies::with(['provider'])->get();
        return Response::json(['success' => true, 'data' => $cancellations]);
    }

    public function store(Request $request)
    {
        $form = $request->input('form');
        $parameters = $request->input('parameters');
        $cancellationPolicy = new ServiceCancellationPolicies();
        $cancellationPolicy->name = $form['name'];
        $cancellationPolicy->user_id = $form['user_id'];
        $cancellationPolicy->min_num = $form['min_num'];
        $cancellationPolicy->max_num = $form['max_num'];
        $cancellationPolicy->status = 1;
        if ($cancellationPolicy->save()) {
            foreach ($parameters as $key => $value) {
                $dataInsert[] = [
                    'min_hour' => $value['min_hour'],
                    'max_hour' => $value['max_hour'],
                    'unit_duration' => $value['unit_duration'],
                    'service_politics_id' => $cancellationPolicy->id,
                    'service_penalty_id' => $value['service_penalty_id'],
                    'amount' => $value['amount'],
                    'tax' => $value['tax'],
                    'service' => $value['service'],
                ];
            }
            $cancelationParameter = ServicePoliticsParameter::insert($dataInsert);
        }

        $this->saveTranslation($request->input('translDesc'), 'service_cancellation_policies', $cancellationPolicy->id);

        return Response::json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        $form = $request->input('form');
        $parameters = $request->input('parameters');

        $cancellationPolicy = ServiceCancellationPolicies::find($form['id']);
        $cancellationPolicy->name = $form['name'];
        $cancellationPolicy->user_id = $form['user_id'];
        $cancellationPolicy->min_num = $form['min_num'];
        $cancellationPolicy->max_num = $form['max_num'];
        if ($cancellationPolicy->save()) {
            foreach ($parameters as $key => $value) {
                if (!empty($value['id'])) {
                    $updateParam = ServicePoliticsParameter::where('id', $value['id'])->first();
                    $updateParam->min_hour = $value['min_hour'];
                    $updateParam->max_hour = $value['max_hour'];
                    $updateParam->unit_duration = $value['unit_duration'];
                    $updateParam->service_penalty_id = $value['service_penalty_id'];
                    $updateParam->amount = $value['amount'];
                    $updateParam->tax = $value['tax'];
                    $updateParam->service = $value['service'];
                    $updateParam->save();
                } else {
                    $insertParam = new ServicePoliticsParameter();
                    $insertParam->min_hour = $value['min_hour'];
                    $insertParam->max_hour = $value['max_hour'];
                    $insertParam->unit_duration = $value['unit_duration'];
                    $insertParam->service_penalty_id = $value['service_penalty_id'];
                    $insertParam->amount = $value['amount'];
                    $insertParam->tax = $value['tax'];
                    $insertParam->service = $value['service'];
                    $insertParam->service_politics_id = $cancellationPolicy->id;
                    $insertParam->save();
                }
            }
        }

        $this->saveTranslation($request->input('translDesc'), 'service_cancellation_policies', $cancellationPolicy->id);

        return Response::json(['success' => true]);
    }

    public function searchParameters(Request $request)
    {
        $id = $request->id;
        $policy = ServiceCancellationPolicies::with([
            'translations' => function ($query) use ($id) {
                $query->where('type', 'service_cancellation_policies');
                $query->where('object_id', $id);
            }
        ])->with(['provider'])->where('id',$id)->get();
        $parameters = ServicePoliticsParameter::where('service_politics_id', $request->id)->orderBy('min_hour')->get();
        $result = [];

        foreach ($parameters as $key => $param) {
            array_push($result, [
                'id' => $param->id,
                'min_hour' => $param->min_hour,
                'max_hour' => $param->max_hour,
                'unit_duration' => $param->unit_duration,
                'amount' => $param->amount,
                'service_penalty_id' => $param->service_penalty_id,
                'service' => $param->service,
                'tax' => $param->tax,
                'count' => ($key + 1),
                'details' => '',
            ]);
        }

        $data = array(
            'policy' => $policy,
            'parameters' => $parameters,
        );
        return Response::json(['success' => true, 'data' => $data]);
    }

    public function updateStatus($id, Request $request)
    {
        $hotel = ServiceCancellationPolicies::find($id);
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
        $policies = ServiceCancellationPolicies::select('id', 'name')
//            ->where('user_id',$provider_id)
            ->where('status', 1)->get();
        $result = [];
        foreach ($policies as $p) {
            array_push($result, ['text' => $p->name, 'value' => $p->id]);
        }
        return Response::json(['success' => true, 'data' => $result]);
    }

    public function destroy($id)
    {
        $policy_used = ServiceRatePlan::where('service_cancellation_policy_id',$id)->get();
        if($policy_used->count() === 0){
            $deleteParam = ServicePoliticsParameter::where('service_politics_id', $id)->delete();
            $cancellationPolicy = ServiceCancellationPolicies::find($id);
            $cancellationPolicy->delete();
            $used = false;
            $success = true;
        }else{
            $used = true;
            $success = false;
        }
        return Response::json(['success' => $success, 'used' => $used]);
    }

    public function destroyParameters($id)
    {
        $parameter = ServicePoliticsParameter::find($id);
        $parameter->delete();
        return Response::json(['success' => true]);
    }

    public function supplier(Request $request)
    {
        
        $code_supplier = $request->input("supplier");
        $persons = $request->input("persons");

        $servicePolitics = ServiceCancellationPolicies::whereHas('provider', function ($q) use ($code_supplier) {
            $q->where('code', $code_supplier);
        })->with(['provider', 'parameters.penalty'])
        ->where('min_num','<=',$persons)
        ->where('max_num','>=',$persons)
        ->first();

        $results = [];
        if(isset($servicePolitics->parameters) and count($servicePolitics->parameters)>0){
            foreach($servicePolitics->parameters as $parameter){
                
                $parameter['policy_cancelations_parameter_id'] = $parameter->id;
                $parameter['policy_cancelation_id'] = $servicePolitics->id;
                $parameter['service_id'] = $servicePolitics->service_id;
                $parameter['name'] = $servicePolitics->name;
                $parameter['min_pax'] = $servicePolitics->min_num;
                $parameter['max_pax'] = $servicePolitics->max_num;
                $parameter['penalty_id'] = $parameter->service_penalty_id;
                $parameter['penalty_name'] = $parameter->penalty->name; 
                unset($parameter['id']);
                unset($parameter['penalty']);
                array_push($results, $parameter);
            }
        }

        return Response::json(['success' => true , 'data' => $results]);
    }
    
}

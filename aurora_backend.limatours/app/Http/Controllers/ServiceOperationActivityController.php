<?php

namespace App\Http\Controllers;

use App\Mail\NotificationOperationService;
use App\ServiceOperation;
use App\ServiceOperationActivity;
use App\ServiceTypeActivity;
use App\Http\Traits\Translations;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ServiceOperationActivityController extends Controller
{
    use Translations;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $lang = $request->input('lang');
        $id = $request->input('id');

        if (!empty($id)) {
            $operations = ServiceOperationActivity::with([
                'service_type_activities.translations' => function ($query) use ($lang) {
                    $query->where('type', 'servicetypeactivity');
                    $query->whereHas('language', function ($q) use ($lang) {
                        $q->where('iso', $lang);
                    });
                }
            ])->with('service_operations');
            if ($querySearch) {
                $operations->where(function ($query) use ($querySearch, $lang) {
                    $query->whereHas('service_type_activities.translations',
                        function ($query) use ($querySearch, $lang) {

                            $query->where('type', 'servicetypeactivity');
                            $query->whereHas('language', function ($q) use ($lang) {
                                $q->where('iso', $lang);
                            });

                            $filters = explode(" ", $querySearch);

                            foreach($filters as $key => $value)
                            {
                                $query->where('value', 'like', '%' . $value . '%');
                            }
                        });
                });
            }

            $operations = $operations->where('service_operation_id', $id)->get();
            $count = $operations->count();
            if ($count > 0) {
                $get_time = ServiceOperation::find($operations[0]->service_operation_id);
                $start_time = $get_time->start_time;
                foreach ($operations as $key => $item) {
                    if ($key > 0) {
                        $start_time = $operations[$key - 1]->operativity_end;
                    }
                    $start_end = Carbon::createFromFormat('H:i:s',
                        $start_time)->addMinutes($item->minutes)->toTimeString();
                    $item->operativity_start = $start_time;
                    $item->operativity_end = $start_end;
                }
            }

        } else {
            $operations = [];
            $count = 0;
        }


        $data = [
            'data' => $operations,
            'count' => $count,
            'success' => true
        ];
        return Response::json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $arrayErrors = [];
        $countErrors = 0;
        $validator = Validator::make($request->all(), [
            'activity_id' => 'required',
            'service_operation_id' => 'required',
            'minutes' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $error) {
                array_push($arrayErrors, $error);
            }
            $countErrors++;
        }
        if ($countErrors > 0) {
            return Response::json(['success' => false, 'error' => $arrayErrors]);
        } else {

            foreach ( $request->input('service_operation_id') as $service_operation_id ){

                $operation = new ServiceOperationActivity();
                $operation->service_type_activity_id = $request->input('activity_id');
                $operation->service_operation_id = $service_operation_id;
                $operation->minutes = $request->input('minutes');
                $operation->save();

                $serviceOpe = ServiceOperation::where('id', $request->input('service_operation_id'))
                    ->with([
                        'services' => function ($query) {
                            $query->select('id', 'aurora_code');
                        }
                    ])->first();
                $operation_dirty['day'] = $serviceOpe->day;
                $operation_dirty['service_type_activity_id'] = $operation->service_type_activity_id;
                $operation_dirty['service_operation_id'] = $operation->service_operation_id;
                $operation_dirty['minutes'] = $operation->minutes;

                // $this->buildDataNotification(
                //     'create',
                //     $serviceOpe->services->id,
                //     $serviceOpe->services->aurora_code,
                //     ['mlu@limatours.com.pe'],
                //     '',
                //     [],
                //     $operation_dirty
                // );

                $operation_dirty = [];
                if (($request->has('hasNotify') and $request->input('hasNotify')) and
                    ($request->has('emails') and count($request->input('emails')) > 0)
                ) {
                    $serviceOpe = ServiceOperation::where('id', $request->input('service_operation_id'))
                        ->with([
                            'services' => function ($query) {
                                $query->select('id', 'aurora_code');
                            }
                        ])->first();
                    $operation_dirty['day'] = $serviceOpe->day;
                    $operation_dirty['service_type_activity_id'] = $operation->service_type_activity_id;
                    $operation_dirty['service_operation_id'] = $operation->service_operation_id;
                    $operation_dirty['minutes'] = $operation->minutes;
                    $this->buildDataNotification(
                        'create',
                        $serviceOpe->services->id,
                        $serviceOpe->services->aurora_code,
                        $request->input('emails'),
                        $request->input('message'),
                        [],
                        $operation_dirty
                    );
                }
            }


            return Response::json(['success' => true, 'data' => ['id' => $operation->id]]);
        }
    }

    public function buildDataNotification(
        $action,
        $service_id,
        $aurora_code,
        $emails,
        $message,
        $operation,
        $operation_details
    ) {
        if (count($operation_details) > 0) {
            if (isset($operation_details['service_type_activity_id'])) {
                $activity = ServiceTypeActivity::where('id',$operation_details['service_type_activity_id'])
                    ->with([
                        'translations' => function ($query) {
                            $query->where('type', 'servicetypeactivity');
                            $query->where('language_id', 1);
                        }
                    ])->first();
                $operation_details['service_type_activity'] = $activity->translations[0]->value;
            }
        }
        $data = [
            'action' => $action,
            'service_id' => $service_id,
            'aurora_code' => $aurora_code,
            'operations' => $operation,
            'operation_details' => $operation_details,
            'message' => $message,
        ];
        Mail::to($emails)->send(new NotificationOperationService($data));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $arrayErrors = [];
        $countErrors = 0;
        $validator = Validator::make($request->all(), [
            'activity_id' => 'required',
            'minutes' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $error) {
                array_push($arrayErrors, $error);
            }
            $countErrors++;
        }
        if ($countErrors > 0) {
            return Response::json(['success' => false]);
        } else {
            $operation = ServiceOperationActivity::find($id);
            $operation->service_type_activity_id = $request->input('activity_id');
            $operation->minutes = $request->input('minutes');
            $operation->save();

            $serviceOpe = ServiceOperation::where('id', $operation->service_operation_id)
                ->with([
                    'services' => function ($query) {
                        $query->select('id', 'aurora_code');
                    }
                ])->first();
            $operation_dirty['day'] = $serviceOpe->day;
            $operation_dirty['service_type_activity_id'] = $operation->service_type_activity_id;
            $operation_dirty['id'] = $operation->id;
            $operation_dirty['service_operation_id'] = $operation->service_operation_id;
            $operation_dirty['minutes'] = $operation->minutes;
            // $this->buildDataNotification(
            //     'update',
            //     $serviceOpe->services->id,
            //     $serviceOpe->services->aurora_code,
            //     ['mlu@limatours.com.pe'],
            //     '',
            //     [],
            //     $operation_dirty
            // );

            if (($request->has('hasNotify') and $request->input('hasNotify')) and
                ($request->has('emails') and count($request->input('emails')) > 0)
            ) {
                $serviceOpe = ServiceOperation::where('id', $operation->service_operation_id)
                    ->with([
                        'services' => function ($query) {
                            $query->select('id', 'aurora_code');
                        }
                    ])->first();
                $operation_dirty['day'] = $serviceOpe->day;
                $operation_dirty['service_type_activity_id'] = $operation->service_type_activity_id;
                $operation_dirty['id'] = $operation->id;
                $operation_dirty['service_operation_id'] = $operation->service_operation_id;
                $operation_dirty['minutes'] = $operation->minutes;
                $this->buildDataNotification(
                    'update',
                    $serviceOpe->services->id,
                    $serviceOpe->services->aurora_code,
                    $request->input('emails'),
                    $request->input('message'),
                    [],
                    $operation_dirty
                );
            }
            return Response::json(['success' => true]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $operation = ServiceOperationActivity::find($id);
        $operation->delete();
        return Response::json(['success' => true]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Mail\NotificationOperationService;
use App\ProgressBar;
use App\Service;
use App\ServiceOperation;
use App\ServiceSchedule;
use App\Http\Traits\Translations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ServiceOperationController extends Controller
{
    use Translations;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'service_id' => 'required',
            'start_time' => 'required',
            'shifts_available' => 'required',
            'sshh_available' => 'required',
            'day' => 'required',
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
            $operation = new ServiceOperation();
            $operation->service_id = $request->input('service_id');
            $operation->service_schedule_id = $request->input('service_schedule_id');
            $operation->day = $request->input('day');
            $operation->start_time = $request->input('start_time');
            $operation->shifts_available = $request->input('shifts_available');
            $operation->sshh_available = (int)$request->input('sshh_available');
            $operation->save();
            $operation_dirty = [];
            $operation_dirty['day'] = $operation->day;
            $operation_dirty['start_time'] = $operation->start_time;
            $operation_dirty['shifts_available'] = $operation->shifts_available;
            $operation_dirty['sshh_available'] = $operation->sshh_available;

            if (($request->has('hasNotify') and $request->input('hasNotify')) and
                ($request->has('emails') and count($request->input('emails')) > 0)
            ) {
                $service = Service::find($request->input('service_id'));
                $this->buildDataNotification(
                    'create',
                    $service->id,
                    $service->aurora_code,
                    $request->input('emails'),
                    $request->input('message'),
                    $operation_dirty,
                    []
                );
            }

            ProgressBar::firstOrCreate(
                [
                    'slug' => 'service_progress_operability',
                    'value' => 10,
                    'type' => 'service',
                    'object_id' => $operation->service_id
                ]
            );
            return Response::json(['success' => true, 'data' => ['id' => $operation->id, 'operation'=>$operation]]);
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
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $serviceOperation = ServiceSchedule::where('service_id', $id)->get();

        $serviceOperation->transform(function($data)use($id){
            $data['data'] = ServiceOperation::where('service_id', $id)->where('service_schedule_id', $data['id'])->get();
            return $data;
        });

        $duration = Service::find($id);
        if ($duration->unit_duration_id == 2) {
            $duration = $duration->duration;
        } else {
            $duration = 1;
        }
        $data = array(
            "service_operations" => $serviceOperation,
            "duration" => $duration,
        );
        return Response::json(['success' => true, 'data' => $data]);
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
            'id' => 'required',
            'start_time' => 'required',
            'shifts_available' => 'required',
            'sshh_available' => 'required',
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
            $operation = ServiceOperation::find($request->input('id'));
            $operation->start_time = $request->input('start_time');
            $operation->shifts_available = $request->input('shifts_available');
            $operation->sshh_available = (int)$request->input('sshh_available');
            $operation_dirty = [];
            if ($operation->isDirty('day')) {
                $operation_dirty['day'] = $operation->day;
            }
            if ($operation->isDirty('start_time')) {
                $operation_dirty['start_time'] = $operation->start_time;
            }
            if ($operation->isDirty('shifts_available')) {
                $operation_dirty['shifts_available'] = $operation->shifts_available;
            }
            if ($operation->isDirty('sshh_available')) {
                $operation_dirty['sshh_available'] = $operation->sshh_available;
            }
            $operation->save();

            if (($request->has('hasNotify') and $request->input('hasNotify')) and
                ($request->has('emails') and count($request->input('emails')) > 0) and
                (count($operation_dirty) > 0)
            ) {
                $service = Service::find($operation->service_id);
                $this->buildDataNotification(
                    'update',
                    $service->id,
                    $service->aurora_code,
                    $request->input('emails'),
                    $request->input('message'),
                    $operation_dirty,
                    []
                );
            }

            ProgressBar::firstOrCreate(
                [
                    'slug' => 'service_progress_operability',
                    'value' => 10,
                    'type' => 'service',
                    'object_id' => $operation->service_id
                ]
            );
            $data = array(
                "id" => $operation->id,
                'operation' => $operation
            );
            return Response::json(['success' => true, 'data' => $data]);
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
        //
    }
}

<?php

namespace App\Http\Controllers;

use App\ProgressBar;
use App\Service;
use App\ServiceOperation;
use App\ServiceOperationActivity;
use App\ServiceSchedule;
use App\ServiceScheduleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ServiceScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $service_id = $request->input('id');
        $service = Service::find($service_id,['affected_schedule', 'service_sub_category_id', 'service_type_id']);
        $schedules = ServiceSchedule::with([
            'servicesScheduleDetail'
        ])->where('service_id', $service_id)->get();

        //Tipo de servicio
        /*$flag_featured = 0;
        $service_sub_categories = [6, 7, 25, 26];

        if ($service->service_type_id == 1 || in_array($service->service_sub_category_id, $service_sub_categories)) {
            $flag_featured = 1;
        }*/
        $flag_featured = 1;
        $data = [
            'data' => $schedules,
            'affected_schedule' => $service->affected_schedule,
            'flag_featured' => $flag_featured,
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
        try {
            $validator = Validator::make($request->all(), [
                'service_id' => 'required'
            ]);
            if ($validator->fails()) {
                return Response::json(['success' => false]);
            } else {
                DB::beginTransaction();

                $service_id = $request->input('service_id');
                $schedules_ = ServiceSchedule::where('service_id', $service_id)->get();
                $operations_ = ServiceOperation::where('service_id', $service_id)->get();

                if ($request->input('featured') == 1) {
                    if (count($schedules_) > 0) {
                        forEach ($schedules_ as $s) {
                            $s->featured = 0;
                            $s->save();
                        }
                    }
                }          

                $schedule = new ServiceSchedule();
                $schedule->service_id = $service_id;
                $schedule->featured = $request->input('featured') ? $request->input('featured') : 0;
                $schedule->save();

                $schedule->servicesScheduleDetail()->create(
                    $request->input('schedule_to')
                );
                $schedule->servicesScheduleDetail()->create(
                    $request->input('schedule_from')
                );

                if( count($schedules_) === 0 && count($operations_)>0){
                    foreach ( $operations_ as $operation ){
                        $operation->service_schedule_id = $schedule->id;
                        $operation->save();
                    }
                } else { // crear encabezado

                    $schedule_details = ServiceScheduleDetail::where('service_schedule_id', $schedule->id)->get();
//                    var_export( $schedule_details ); die;
                    $time_ = null;
                    $start_time_ = null;
                    $end_time_ = null;
                    foreach ( $schedule_details as $schedule_detail ){
                        if( $schedule_detail->monday === null ){
                            if( $schedule_detail->tuesday === null ){
                                if( $schedule_detail->wednesday === null ){
                                    if( $schedule_detail->thursday === null ){
                                        if( $schedule_detail->friday === null ){
                                            if( $schedule_detail->saturday === null ){
                                                $time_ = $schedule_detail->sunday;
                                            } else {
                                                $time_ = $schedule_detail->saturday;
                                            }
                                        } else {
                                            $time_ = $schedule_detail->friday;
                                        }
                                    } else {
                                        $time_ = $schedule_detail->thursday;
                                    }
                                } else {
                                    $time_ = $schedule_detail->wednesday;
                                }
                            } else {
                                $time_ = $schedule_detail->tuesday;
                            }
                        } else {
                            $time_ = $schedule_detail->monday;
                        }
                        if($schedule_detail->type === "I"){
                            $start_time_ = $time_;
                        } else{ // F
                            $end_time_ = $time_;
                        }
                    }

                    $shifts_available_ = \Carbon\Carbon::parse($start_time_)->format('A');

                    if( $end_time_ !== null && $shifts_available_==='AM' ){
                        $shift_end_time_ = \Carbon\Carbon::parse($end_time_)->format('A');
                        if( $shift_end_time_ === 'PM' ){
                            $shifts_available_.= '/PM';
                        }
                    }

//                        var_export( $schedule->id. ' - ' .$start_time_ . '  -  ' . $end_time_ . '  -  ' . $shifts_available_  . '>>>' );

                    $operations = [];
                    if( count($operations_) > 0 ){
                        $operations = ServiceOperation::where('service_id', $service_id)
                            ->where('service_schedule_id', $operations_[count($operations_)-1]->service_schedule_id)
                            ->with(['services_operation_activities'])->get();
                    }

                    if( count($operations) > 0 ){
                        foreach ( $operations as $operation ){

                            $new_service_operation = new ServiceOperation();
                            $new_service_operation->service_id = $service_id;
                            $new_service_operation->service_schedule_id = $schedule->id;
                            $new_service_operation->day = $operation->day;
                            $new_service_operation->start_time = $start_time_;
                            $new_service_operation->shifts_available = $shifts_available_;
                            $new_service_operation->sshh_available = $operation->sshh_available;
                            $new_service_operation->save();

                            foreach ( $operation->services_operation_activities as $activity ){
                                $new_operation_activity = new ServiceOperationActivity();
                                $new_operation_activity->service_type_activity_id = $activity->service_type_activity_id;
                                $new_operation_activity->service_operation_id = $new_service_operation->id;
                                $new_operation_activity->minutes = $activity->minutes;
                                $new_operation_activity->save();
                            }
                        }
                    } else {
                        $new_service_operation = new ServiceOperation();
                        $new_service_operation->service_id = $service_id;
                        $new_service_operation->service_schedule_id = $schedule->id;
                        $new_service_operation->day = 1;
                        $new_service_operation->start_time = $start_time_;
                        $new_service_operation->shifts_available = $shifts_available_;
                        $new_service_operation->sshh_available = 0;
                        $new_service_operation->save();
                    }
                }

                ProgressBar::firstOrCreate(
                    [
                        'slug' => 'service_progress_schedules',
                        'value' => 10,
                        'type' => 'service',
                        'object_id' => $schedule->service_id
                    ]
                );
                DB::commit();
                return Response::json(['success' => true]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(['success' => false, 'message' => $e]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\ServiceSchedule $horaryService
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'service_id' => 'required',
                'schedule_to' => 'required',
                'schedule_from' => 'required'
            ]);
            if ($validator->fails()) {
                return Response::json(['success' => false]);
            } else {

                $service_schedules = ServiceSchedule::where('service_id', $request->input('service_id'))->get();

                if ($request->input('featured') == 1) {
                    if (count($service_schedules) > 0) {
                        forEach ($service_schedules as $s) {
                            $s->featured = 0;
                            $s->save();
                        }
                    }    
                }   
                $service_schedule = ServiceSchedule::find($request->input('schedule_id'));
                $service_schedule->featured = $request->input('featured') ? $request->input('featured') : 0;
                $service_schedule->save();

                $service_id = $request->input('service_id');
                DB::beginTransaction();
                $to = $request->input('schedule_to');
                $from = $request->input('schedule_from');
                $scheduleTo = ServiceScheduleDetail::find($to['id']);
                $scheduleTo->monday = $to['monday'];
                $scheduleTo->tuesday = $to['tuesday'];
                $scheduleTo->wednesday = $to['wednesday'];
                $scheduleTo->thursday = $to['thursday'];
                $scheduleTo->friday = $to['friday'];
                $scheduleTo->saturday = $to['saturday'];
                $scheduleTo->sunday = $to['sunday'];
                $scheduleTo->save();
                $scheduleFrom = ServiceScheduleDetail::find($from['id']);
                $scheduleFrom->monday = $from['monday'];
                $scheduleFrom->tuesday = $from['tuesday'];
                $scheduleFrom->wednesday = $from['wednesday'];
                $scheduleFrom->thursday = $from['thursday'];
                $scheduleFrom->friday = $from['friday'];
                $scheduleFrom->saturday = $from['saturday'];
                $scheduleFrom->sunday = $from['sunday'];
                $scheduleFrom->save();
                ProgressBar::updateOrCreate(
                    [
                        'slug' => 'service_progress_schedules',
                        'value' => 10,
                        'type' => 'service',
                        'object_id' => $service_id
                    ]
                );
                DB::commit();
                return Response::json(['success' => true]);
            }

        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(['success' => false, 'message' => $e]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\ServiceSchedule $horaryService
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, $service_id)
    {
        try {
            DB::beginTransaction();
            $schedule = ServiceSchedule::find($id);
            $schedule->delete();
            $schedule->servicesScheduleDetail()->delete();

            $operations = ServiceOperation::where('service_id', $service_id)
                ->where('service_schedule_id', $id)->get();
            forEach( $operations as $operation ){
                ServiceOperationActivity::where('service_operation_id', $operation->id )->delete();
                $operation->delete();
            }

            $count = ServiceSchedule::where('service_id', $service_id)->count();
            if ($count == 0) {
                ProgressBar::updateOrCreate(
                    [
                        'slug' => 'service_progress_schedules',
                        'value' => 0,
                        'type' => 'service',
                        'object_id' => $service_id
                    ]
                );
            }
            DB::commit();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(['success' => false, 'message' => $e]);
        }
    }

    public function updateAffectedSchedule($service_id, Request $request)
    {
        try {
            DB::beginTransaction();
            $affected_schedule = $request->input('affected_schedule');
            $service = Service::find($service_id);
            $service->affected_schedule = $affected_schedule;
            $service->save();
            DB::commit();
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollback();
            return Response::json(['success' => false, 'message' => $e]);
        }

    }
}

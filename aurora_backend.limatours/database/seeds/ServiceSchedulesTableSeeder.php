<?php

use App\Service;
use App\ServiceSchedule;
use App\ServiceOperation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSchedulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {

            $services = Service::withTrashed()
//                ->where('id', 2884)
                ->get();

            foreach ($services as $service) {

                $schedules = ServiceSchedule::where('service_id', $service->id)
                    ->with(['servicesScheduleDetail'])->get();
                $operations = ServiceOperation::where('service_id', $service->id)
                    ->with(['services_operation_activities'])->get();

//                var_export( json_encode( $schedules ) );

                foreach ($schedules as $i => $schedule) {
                    if( count($operations) > 0 && $i === 0 ){ // Siempre encontraría 1 en un inicio
                        foreach ($operations as $operation) {
                            $operation->service_schedule_id = $schedule->id;
                            $operation->save();
                        }
                    } else {

                        $time_ = null;
                        $start_time_ = null;
                        $end_time_ = null;
                        foreach ( $schedule->servicesScheduleDetail as $schedule_detail ){
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

                        if( count($operations) > 0 ){
                            foreach ( $operations as $operation ){

                                $new_service_operation = new ServiceOperation();
                                $new_service_operation->service_id = $service->id;
                                $new_service_operation->service_schedule_id = $schedule->id;
                                $new_service_operation->day = $operation->day;
                                $new_service_operation->start_time = $start_time_;
                                $new_service_operation->shifts_available = $shifts_available_;
                                $new_service_operation->sshh_available = $operation->sshh_available;
                                $new_service_operation->save();

                                foreach ( $operation->services_operation_activities as $activity ){
                                    $new_operation_activity = new \App\ServiceOperationActivity();
                                    $new_operation_activity->service_type_activity_id = $activity->service_type_activity_id;
                                    $new_operation_activity->service_operation_id = $new_service_operation->id;
                                    $new_operation_activity->minutes = $activity->minutes;
                                    $new_operation_activity->save();
                                }
                            }
                        } else {
                            $new_service_operation = new ServiceOperation();
                            $new_service_operation->service_id = $service->id;
                            $new_service_operation->service_schedule_id = $schedule->id;
                            $new_service_operation->day = 1;
                            $new_service_operation->start_time = $start_time_;
                            $new_service_operation->shifts_available = $shifts_available_;
                            $new_service_operation->sshh_available = 0;
                            $new_service_operation->save();
                        }

                    }

                }
            }

        });
    }
}

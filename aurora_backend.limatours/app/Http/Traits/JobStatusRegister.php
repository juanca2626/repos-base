<?php

namespace App\Http\Traits;

use App\JobStatusList;
use Illuminate\Support\Facades\Auth;

trait JobStatusRegister
{
    /**
     * @param $entity
     * @param $object_id
     * @param  null  $data
     * @param  null  $client_id
     * @param  null  $year
     * @param  null  $error_message
     * @param  int  $job_status
     * @return void
     */
    public function store_job(
        $entity,
        $object_id,
        $data = null,
        $client_id = null,
        $year = null,
        $error_message = null,
        $job_status = 0
    ) {
        $new_job_list = new JobStatusList();
        $new_job_list->entity = $entity;
        $new_job_list->object_id = $object_id;
        $new_job_list->user_id = Auth::id();
        $new_job_list->client_id = $client_id;
        $new_job_list->email_notification = Auth::user()->email;
        $new_job_list->year = $year;
        $new_job_list->data = json_encode($data);
        $new_job_list->error_message = $error_message;
        $new_job_list->job_status = $job_status;
        $new_job_list->save();
    }

    public function checkStatusJobExecute(
        $entity,
        $object_id,
        $user_id = null,
        $year = null,
        $job_status = 0
    ) {
        $status_job = false;
        $query = JobStatusList::where('entity', $entity)
            ->where('object_id', $object_id)
            ->where('job_status', $job_status);
        if (!empty($user_id)) {
            $query = $query->where('user_id', $user_id);
        }
        if (!empty($year)) {
            $query = $query->where('year', $year);
        }
        $query = $query->get(['job_status']);
        if($query->count() > 0){
            $status_job = true;
        }

        return $status_job;
    }
}

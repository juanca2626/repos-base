<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrainPolicyRate extends Model
{
    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'trainratepolicy');
    }

    public function cancellation_policies()
    {
        return $this->belongsToMany('App\TrainCancellationPolicy', null, null, 'train_cancellation_policy_id');
    }

//    public function rates_plans_calendary()
//    {
//        return $this->hasMany('App\RatesPlansCalendarys', 'policies_rate_id', 'id');
//    }

}

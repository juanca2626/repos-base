<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrainRatePlan extends Model
{

    public function train_type()
    {
        return $this->belongsTo('App\TrainType', 'train_type_id', 'id');
    }

    public function policy()
    {
        return $this->belongsTo('App\TrainCancellationPolicy', 'train_cancellation_policy_id', 'id');
    }
}

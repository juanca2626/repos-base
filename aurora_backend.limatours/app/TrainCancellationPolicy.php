<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrainCancellationPolicy extends Model
{
    protected $fillable = [
        'name'
    ];

    public function cancellation_parameters()
    {
        return $this->hasMany('App\TrainCancellationParameter','train_cancellation_id','id');
    }

    public function policy_rates()
    {
        return $this->hasMany('App\TrainPolicyRate' , 'train_cancellation_policy_id' ,'id');
    }

}

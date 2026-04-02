<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainTemplate extends Model
{
    use SoftDeletes;

    public function train_rail_route()
    {
        return $this->belongsTo('App\TrainRailRoute');
    }

    public function train_train_class()
    {
        return $this->belongsTo('App\TrainTrainClass');
    }

    public function city()
    {
        return $this->belongsTo('App\City');
    }

    public function country()
    {
        return $this->belongsTo('App\Country');
    }

    public function state()
    {
        return $this->belongsTo('App\State');
    }

    public function zone()
    {
        return $this->belongsTo('App\Zone');
    }

    public function district()
    {
        return $this->belongsTo('App\District');
    }

}

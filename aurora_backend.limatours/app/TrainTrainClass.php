<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrainTrainClass extends Model
{

    public function train_templates()
    {
        return $this->hasMany('App\TrainTemplates');
    }
}

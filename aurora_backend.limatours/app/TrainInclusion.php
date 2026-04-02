<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainInclusion extends Model
{
    use SoftDeletes;

    public function train_templates()
    {
        return $this->belongsTo('App\TrainTemplate','train_template_id');
    }

    public function inclusions()
    {
        return $this->belongsTo('App\Inclusion','inclusion_id');
    }
}

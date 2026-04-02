<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MarkupTrain extends Model
{
    protected $fillable = ['period', 'markup', 'train_template_id', 'client_id'];

    public function train_template()
    {
        return $this->belongsTo('App\TrainTemplate', 'train_template_id');
    }

    public function client()
    {
        return $this->belongsTo('App\Client', 'client_id');
    }
}

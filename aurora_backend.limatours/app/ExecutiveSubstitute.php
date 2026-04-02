<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExecutiveSubstitute extends Model
{
    use SoftDeletes;

    public function user(){
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function substitute_clients(){
        return $this->hasMany('App\ExecutiveSubstituteClient');
    }

    public function executive(){
        return $this->belongsTo('App\User','executive_id', 'id');
    }

}

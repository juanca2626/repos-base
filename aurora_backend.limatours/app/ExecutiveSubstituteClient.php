<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExecutiveSubstituteClient extends Model
{
    use SoftDeletes;

    public function executive_substitute(){
        return $this->belongsTo('App\ExecutiveSubstitute');
    }

    public function client(){
        return $this->belongsTo('App\Client');
    }
}

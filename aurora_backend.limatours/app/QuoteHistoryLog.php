<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuoteHistoryLog extends Model
{
    use SoftDeletes;

    public function quote(){
        return $this->belongsTo('App\Quote');
    }

    public function user(){
        return $this->belongsTo('App\User');
    }

}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReservationBilling extends Model
{
    public function document_type(){
        return $this->belongsTo('App\Doctype','document_type_id','id');
    }

    public function country(){
        return $this->belongsTo('App\Country','country_id','id');
    }

    public function state(){
        return $this->belongsTo('App\State','state_id','id');
    }
}

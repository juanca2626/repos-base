<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuotePassenger extends Model
{
    protected $table = 'quote_passengers';

    public function doctype(){
        return $this->belongsTo('App\Doctype','iso','doctype_iso');
    }

    public function getIsDirectClientAttribute($value)
    {
        return (boolean)$value;
    }

    public function age_child()
    {
        return $this->belongsTo('App\QuoteAgeChild','quote_age_child_id','id');
    }
}

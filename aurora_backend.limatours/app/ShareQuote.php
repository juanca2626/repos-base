<?php

namespace App;

use Carbon\Carbon;
use Carbon\Exceptions\Exception;
use Illuminate\Database\Eloquent\Model;

class ShareQuote extends Model
{
    protected $fillable = ['quote_id','view_permission','edit_permission','client_id','user_id','seller_id'];


    public function user(){
        return $this->belongsTo('App\User');
    }
    public function seller(){
        return $this->belongsTo('App\User','seller_id');
    }

    public function client(){
        return $this->belongsTo('App\Client');
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }
}

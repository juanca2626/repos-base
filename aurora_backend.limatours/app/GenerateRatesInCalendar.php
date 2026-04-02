<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class GenerateRatesInCalendar extends Model
{
    protected  $fillable = ['hotel_id','rates_plans_id','room_id','perido','status','status_message','user_add'];

}

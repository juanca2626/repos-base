<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SerieCompanion extends Model
{
    use SoftDeletes;

    public function user_type()
    {
        return $this->belongsTo('App\UserTypes');
    }

    public function pay_mode()
    {
        return $this->belongsTo('App\PayMode');
    }


}

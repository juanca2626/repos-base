<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use SoftDeletes;

    public function client()
    {
        return $this->belongsTo('App\Client');
    }

    public function reservation()
    {
        return $this->belongsTo('App\Reservation');
    }

    public function services()
    {
        return $this->hasMany('App\FileService');
    }

}

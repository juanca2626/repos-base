<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceAvailableDay extends Model
{
    public function services()
    {
        return $this->belongsTo('App\Service');
    }
}

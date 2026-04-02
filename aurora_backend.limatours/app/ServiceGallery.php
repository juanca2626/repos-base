<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceGallery extends Model
{
    public function services()
    {
        return $this->belongsTo('App\Service');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OpeNotification extends Model
{
    use SoftDeletes;

    public function template()
    {
        return $this->belongsTo('App\OpeTemplate', 'template_id', 'id')->with(['contents']);
    }
}

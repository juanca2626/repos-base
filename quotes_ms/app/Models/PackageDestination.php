<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageDestination extends Model
{
    protected $fillable = ['package_id','state_id'];

    public function state()
    {
        return $this->belongsTo('App\Models\State');
    }

    public function package()
    {
        return $this->belongsTo('App\Models\Package');
    }
}

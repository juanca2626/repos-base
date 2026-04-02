<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PackageDestination extends Model
{
    protected $fillable = ['package_id','state_id'];

    public function state()
    {
        return $this->belongsTo('App\State');
    }

    public function package()
    {
        return $this->belongsTo('App\Package');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackageCustomer extends Model
{
    use SoftDeletes;

    public function package()
    {
        return $this->belongsTo('App\Package', 'package_id');
    }

    public function client()
    {
        return $this->belongsTo('App\Client', 'client_id');
    }
}

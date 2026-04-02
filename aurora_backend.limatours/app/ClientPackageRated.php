<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientPackageRated extends Model
{
    protected $table = 'client_package_rated';


    public function package()
    {
        return $this->belongsTo('App\Package','package_id','id');
    }

}

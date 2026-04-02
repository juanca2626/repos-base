<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VirtualClassName extends Model
{
    public function clients()
    {
        return $this->hasMany('App\VirtualClassNameClient');
    }
    public function type_class()
    {
        return $this->hasOne('App\TypeClass','id','type_class_id');
    }
}

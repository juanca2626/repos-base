<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chain extends Model
{
    use SoftDeletes;

    protected $fillable = ['name','status','created_at','updated_at','deleted_at'];

    public function hotels()
    {
        return $this->hasMany('App\Hotel');
    }
}

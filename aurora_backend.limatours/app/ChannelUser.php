<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChannelUser extends Model
{
    use SoftDeletes;

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id');
    }

    public function users()
    {
        return $this->hasMany('App\User');
    }

    public function channels()
    {
        return $this->belongsToMany('App\Channel');
    }
}

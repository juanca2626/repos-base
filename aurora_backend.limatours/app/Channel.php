<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Channel extends Model
{
    use SoftDeletes;

    public function rooms()
    {
        return $this->belongsToMany('App\Room')
            ->withPivot('code', 'state');
    }

    public function hotels()
    {
        return $this->belongsToMany('App\Hotel')
            ->withPivot('code', 'state');
    }

    public function logs()
    {
        return $this->hasMany('App\ChannelsLogs');
    }

}

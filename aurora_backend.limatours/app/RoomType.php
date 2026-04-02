<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomType extends Model
{
    use SoftDeletes;

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'roomtype');
    }

    public function rooms()
    {
        return $this->hasMany('App\Room');
    }

    public function type_room()
    {
        return $this->belongsTo('App\TypeRoom');
    }

}

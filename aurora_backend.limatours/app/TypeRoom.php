<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeRoom extends Model
{
    use SoftDeletes;

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'typeroom');
    }

    public function room_ypes()
    {
        return $this->hasMany('App\RoomType');
    }
}

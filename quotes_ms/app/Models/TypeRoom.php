<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeRoom extends Model
{
    use SoftDeletes;

    public function translations()
    {
        return $this->hasMany('App\Models\Translation', 'object_id', 'id')
            ->where('translations.type', 'typeroom');
    }

    public function room_ypes()
    {
        return $this->hasMany('App\Models\RoomType');
    }
}

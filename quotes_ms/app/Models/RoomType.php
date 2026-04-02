<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomType extends Model
{
    use SoftDeletes;

    public function translations(): HasMany
    {
        return $this->hasMany('App\Models\Translation', 'object_id', 'id')
            ->where('translations.type', 'roomtype');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany('App\Models\Room');
    }

    public function type_room(): BelongsTo
    {
        return $this->belongsTo('App\Models\TypeRoom');
    }
}

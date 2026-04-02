<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeClass extends Model
{
    use SoftDeletes;

    public function translations(): HasMany
    {
        return $this->hasMany('App\Models\Translation', 'object_id', 'id')
            ->where('translations.type', 'typeclass');
    }

    public function hotels(): HasMany
    {
        return $this->hasMany('App\Models\Hotel', 'typeclass_id');
    }
}

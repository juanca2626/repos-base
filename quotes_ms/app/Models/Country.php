<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use SoftDeletes;

    protected $casts = [
        'name' => 'code',
    ];

    public function states(): HasMany
    {
        return $this->hasMany('App\Models\State');
    }

    public function taxes(): HasMany
    {
        return $this->hasMany('App\Models\Tax');
    }

    public function translations(): HasMany
    {
        return $this->hasMany('App\Models\Translation', 'object_id', 'id')
            ->where('translations.type', 'country');
    }

    public function hotels(): HasMany
    {
        return $this->hasMany('App\Models\Hotel');
    }

    public function clients(): HasMany
    {
        return $this->hasMany('App\Models\Client');
    }
}

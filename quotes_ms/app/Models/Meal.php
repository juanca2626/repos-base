<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Meal extends Model
{
    use SoftDeletes;

    public function translations(): HasMany
    {
        return $this->hasMany('App\Models\Translation', 'object_id', 'id')
            ->where('translations.type', 'meal');
    }

    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo('App\Models\RatesPlans', 'meal_id');
    }
}

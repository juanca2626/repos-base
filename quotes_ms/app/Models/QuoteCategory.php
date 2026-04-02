<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuoteCategory extends Model
{
    public function type_class(): BelongsTo
    {
        return $this->belongsTo('App\Models\TypeClass');
    }

    public function services(): HasMany
    {
        return $this->hasMany('App\Models\QuoteService');
    }

    public function ranges(): HasMany
    {
        return $this->hasMany('App\Models\QuoteDynamicSaleRate');
    }
}

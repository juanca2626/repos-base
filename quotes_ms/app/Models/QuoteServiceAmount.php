<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteServiceAmount extends Model
{
    public function category(): BelongsTo
    {
        return $this->belongsTo('App\Models\QuoteCategory', 'quote_category_id', 'id');
    }
}

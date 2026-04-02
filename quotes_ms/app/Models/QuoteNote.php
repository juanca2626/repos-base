<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteNote extends Model
{
    protected $table = 'quote_notes';

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }
}

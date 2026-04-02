<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotePassenger extends Model
{
    protected $table = 'quote_passengers';

    public function doctype(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Doctype', 'iso', 'doctype_iso');
    }

    public function getIsDirectClientAttribute($value): bool
    {
        return (bool) $value;
    }

    public function age_child(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\QuoteAgeChild', 'quote_age_child_id', 'id');
    }
}

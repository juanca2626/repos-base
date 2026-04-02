<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Markup extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['client'];
    }

    public function scopeSearch($query, $target)
    {
        if ($target != '') {
            return $query->where('period', $target);
        }
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo('App\Models\Client');
    }

    public function businessRegion()
    {
        return $this->belongsTo(BusinessRegion::class);
    }
}

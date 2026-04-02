<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class HotelClient extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['client'];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo('App\Models\Hotel', 'hotel_id');
    }

    public function scopePeriod($query, $period): void
    {
        if ($period != '') {
            $query->where('period', $period);
        }
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo('App\Models\Client', 'client_id');
    }
}

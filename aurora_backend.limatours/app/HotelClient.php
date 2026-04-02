<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class HotelClient extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['client'];
    }

    public function hotel()
    {
        return $this->belongsTo('App\Hotel', 'hotel_id');
    }

    public function scopePeriod($query, $period)
    {
        if ($period != '') {
            $query->where('period', $period);
        }
    }

    public function client()
    {
        return $this->belongsTo('App\Client', 'client_id');
    }

    public function businessRegion()
    {
        return $this->belongsTo(BusinessRegion::class);
    }
}

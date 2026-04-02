<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Markup extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    protected $fillable = [
        'period',
        'hotel',
        'service',
        'status',
        'clone',
        'business_region_id',
        'client_id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

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

    public function client()
    {
        return $this->belongsTo('App\Client');
    }

    public function businessRegion()
    {
        return $this->belongsTo(BusinessRegion::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class MarkupService extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['period', 'markup', 'service_id', 'client_id'];

    public function generateTags(): array
    {
        return ['client'];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo('App\Models\Service', 'service_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo('App\Models\Client', 'client_id');
    }
}

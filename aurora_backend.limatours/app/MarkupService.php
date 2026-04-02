<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class MarkupService extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['client'];
    }

    protected $fillable = ['period', 'markup', 'service_id', 'client_id'];

    public function service()
    {
        return $this->belongsTo('App\Service', 'service_id');
    }

    public function client()
    {
        return $this->belongsTo('App\Client', 'client_id');
    }
}

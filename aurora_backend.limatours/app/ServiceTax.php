<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ServiceTax extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    protected $table = 'service_taxes';

    protected $fillable = [
        'service_id',
        'tax_id',
        'amount',
        'state'
    ];

    public function generateTags(): array
    {
        return ['service'];
    }

    public function service()
    {
        return $this->belongsTo('App\Service');
    }

    public function tax()
    {
        return $this->belongsTo('App\Tax','tax_id');
    }

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'servicetaxes');
    }
}

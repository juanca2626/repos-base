<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ServiceCancellationPolicies extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['service'];
    }

    protected $fillable = [
        'name',
    ];

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'service_cancellation_policies');
    }

    public function service()
    {
        return $this->belongsTo('App\Service');
    }

    public function parameters()
    {
        return $this->hasMany('App\ServicePoliticsParameter','service_politics_id');
    }

    public function provider()
    {
        return $this->belongsTo('App\User','user_id');
    }

}

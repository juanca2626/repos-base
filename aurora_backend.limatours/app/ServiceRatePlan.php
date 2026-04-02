<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ServiceRatePlan extends Model implements Auditable
{
    use SoftDeletes,\OwenIt\Auditing\Auditable;

    /*
     * Column: flag_migrate
     */
    const HAS_PROTECTED_RATE = 1;// Son tarifas con margen de protección
    const NOT_HAVE_PROTECTED_RATE = 0; // Son tarifas reales


    protected $table = 'service_rate_plans';
    protected $fillable = [];

    public function generateTags(): array
    {
        return ['service'];
    }


    public function service_rate()
    {
        return $this->belongsTo('App\ServiceRate','service_rate_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function policy()
    {
        return $this->belongsTo('App\ServiceCancellationPolicies', 'service_cancellation_policy_id', 'id');
    }
}

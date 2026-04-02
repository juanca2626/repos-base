<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Model;

class DetailServiceZeroEloquentModel extends Model
{
    protected $table = 'file_detail_service_zero';

    protected $fillable = [
        'days_before_cancellation', 'penalty_amount', 'min_passengers', 'max_passengers',
        'min_age', 'max_age', 'child_age_min', 'child_age_max', 'infant_age_min', 'infant_age_max',
        'file_service_zero_id'
    ];

    public function service()
    {
        return $this->belongsTo(ServiceZeroEloquentModel::class);
    }
}
<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Model;

class RatesServiceZeroEloquentModel extends Model
{
    protected $table = 'file_zero_service_rates';

    protected $fillable = [
        'type_passenger','passenger_range_min', 'passenger_range_max', 'net_cost', 'service_tax','general_tax', 'file_service_zero_id'
    ];

    public function service()
    {
        return $this->belongsTo(ServiceZeroEloquentModel::class);
    }
}

<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceZeroEloquentModel extends Model
{
    use HasFactory;

    protected $table = 'file_service_zero';

    protected $fillable = [
        'id',
        'time',
        'type',
        'privacy',
        'name',
        'skeleton',
        'itinerary',
        'supplier_code',
        'supplier',
        'status',

    ];



    // Relación con la tabla detail_service
    public function detailServiceZero()
    {
        return $this->hasMany(DetailServiceZeroEloquentModel::class, 'file_service_zero_id');
    }

   
    public function rates()
    {
        return $this->hasMany(RatesServiceZeroEloquentModel::class, 'file_service_zero_id');
    }

    public function operationServiceZero()
    {
        return $this->hasMany(OperationServiceZeroEloquentModel::class, 'file_service_zero_id');
    }

    

}
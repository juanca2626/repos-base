<?php
namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Model;

class OperationServiceZeroEloquentModel extends Model
{
    protected $table = 'file_operation_service';

    protected $fillable = [
        'city_in', 'city_out', 'start_date', 'end_date', 
        'start_validity', 'end_validity', 'days_operation',
        'operating_hours', 'code_country','country', 'file_service_zero_id'
    ];

   
    public function serviceZero()
    {
        return $this->belongsTo(ServiceZeroEloquentModel::class);
    }
}
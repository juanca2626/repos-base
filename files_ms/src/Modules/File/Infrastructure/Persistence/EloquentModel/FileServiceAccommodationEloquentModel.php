<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileServiceAccommodationEloquentModel extends Model
{
    use HasFactory;

    protected $table = 'file_service_accommodations';

    protected $fillable = [
        'id',
        'file_service_unit_id',
        'file_passenger_id',
        'room_key',       
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function filePassenger(): BelongsTo
    {
        return $this->belongsTo(FilePassengerEloquentModel::class);
    }
    
    public function file_service_unit(): BelongsTo
    {
        return $this->belongsTo(FileServiceUnitEloquentModel::class);
    }
}

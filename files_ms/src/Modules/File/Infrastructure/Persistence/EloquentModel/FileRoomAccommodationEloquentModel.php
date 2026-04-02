<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileRoomAccommodationEloquentModel extends Model
{
    use HasFactory;

    protected $table = 'file_room_accommodations';

    protected $fillable = [
        'id',
        'file_hotel_room_unit_id',
        'file_passenger_id',
        'room_key',         
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function fileHotelRoomUnit(): BelongsTo
    {
        return $this->belongsTo(FileHotelRoomUnitEloquentModel::class);
    }

    public function filePassenger(): BelongsTo
    {
        return $this->belongsTo(FilePassengerEloquentModel::class);
    }

}

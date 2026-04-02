<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileItineraryFlightAccommodationEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_itinerary_flight_accommodations';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'file_passenger_id',
        'file_itinerary_flight_id', 
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function filePassenger(): BelongsTo
    {
        return $this->belongsTo(FilePassengerEloquentModel::class);
    }

 
}

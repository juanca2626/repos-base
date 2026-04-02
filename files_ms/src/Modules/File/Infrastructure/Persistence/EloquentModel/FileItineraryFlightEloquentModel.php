<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FileItineraryFlightEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_itinerary_flights';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'file_itinerary_id',
        'airline_name',
        'airline_code',
        'airline_number',
        'pnr',
        'departure_time',
        'arrival_time',
        'nro_pax',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function fileItinerary(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FileItineraryEloquentModel::class);
    }

    public function accommodations(): HasMany
    {
        return $this->hasMany(FileItineraryFlightAccommodationEloquentModel::class, 'file_itinerary_flight_id');
    }
}

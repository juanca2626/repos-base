<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileNoteExternalHousingEloquentModel extends Model{
    use HasFactory, SoftDeletes;

    protected $table = 'file_notes_external_housing';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'date_check_in',
        'date_check_out',
        'accommodation_name',
        'accommodation_code_phone',
        'accommodation_number_phone',
        'accommodation_address',
        'accommodation_lat',
        'accommodation_lng',
        'city',
        'file_id',
        'file_itinerary_id',
        'created_by',
        'created_by_code',
        'created_by_name',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function passengers(): HasMany
    {
        return $this->hasMany(FileNoteExternalHousingPassengersEloquentModel::class, 'file_notes_external_housing_id');
    }
}

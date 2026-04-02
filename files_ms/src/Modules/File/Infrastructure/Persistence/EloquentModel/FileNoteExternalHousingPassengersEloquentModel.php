<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileNoteExternalHousingPassengersEloquentModel extends Model{
    use HasFactory, SoftDeletes;

    protected $table = 'file_notes_external_housing_passengers';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'passengers_id',
        'file_notes_external_housing_id'
    ];


    /**
     * Relación con el alojamiento externo
     */
    public function externalHousing(): BelongsTo
    {
        return $this->belongsTo(FileNoteExternalHousingEloquentModel::class, 'file_notes_external_housing_id');
    }
}

<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FileTemporaryServiceDetailEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_temporary_service_details';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'file_temporary_service_id',
        'language_id',
        'itinerary',
        'skeleton',
        'service_notes',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function fileTemporaryService(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FileTemporaryServiceEloquentModel::class);
    }
}

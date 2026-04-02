<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileNoteGeneralEloquentModel extends Model{

    use HasFactory, SoftDeletes;

    protected $table = 'file_notes_general';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'date_event',
        'type_event',
        'description_event',
        'description_client',
        'description_note_general',
        'image_logo',
        'classification_code',
        'classification_name',
        'file_id',
        'created_by',
        'created_by_code',
        'created_by_name',
        'created_at',
        'updated_at',
        'deteled_at'
    ];
}

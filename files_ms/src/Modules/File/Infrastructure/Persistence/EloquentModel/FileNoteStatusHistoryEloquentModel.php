<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Src\Modules\File\Domain\Enums\FileNoteStatusHistoryEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FileNoteStatusHistoryEloquentModel extends Model{
    use HasFactory, SoftDeletes;

    protected $table = 'file_note_status_history';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $casts = [
        'status' => FileNoteStatusHistoryEnum::class, // Cast automático
    ];

    protected $dates = [
        'date'
    ];

    protected $fillable = [
        'id',
        'status',
        'date',
        'comment',
        'file_note_mascara_id',
        'file_note_id',
        'user_id',
        'user_by_code',
        'user_by_name',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}

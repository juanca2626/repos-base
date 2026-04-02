<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Src\Modules\File\Domain\Enums\FileNoteStatusEnum;

class FileNoteMaskEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_notes_mascara';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $casts = [
        'status' => FileNoteStatusEnum::class
    ];

    protected $fillable = [
        'id',
        'type_note',
        'record_type',
        'assignment_mode',
        'dates',
        'description',
        'file_classification_code',
        'file_classification_name',
        'file_id',
        'status',
        'created_by',
        'created_by_code',
        'created_by_name',
        'created_at',
        'updated_at',
        'deleted_at'
    ];


    public function statusHistory(): hasMany
    {
        return $this->hasMany(FileNoteStatusHistoryEloquentModel::class,'file_note_mascara_id');
    }
}

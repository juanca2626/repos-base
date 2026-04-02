<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Model;
use Src\Modules\File\Domain\Enums\FileNoteStatusEnum;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FileNoteEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_notes';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $casts = [
        'status' => FileNoteStatusEnum::class, // Conversión automática
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
        'file_itinerary_id',
        'file_mascara_id',
        'file_id',
        'status',
        'created_by',
        'created_by_code',
        'created_by_name',
        'created_at',
        'updated_at',
        'deleted_at'
    ];


    public function file(): BelongsTo
    {
        return $this->belongsTo(FileEloquentModel::class, 'file_id');
    }

    public function itinerary(): BelongsTo
    {
        return $this->belongsTo(FileItineraryEloquentModel::class,'file_itinerary_id');
    }

    public function classifications(): hasMany
    {
        return $this->hasMany(FileClassificationEloquentModel::class,'file_classification_id');
    }

    public function statusHistory(): hasMany
    {
        return $this->hasMany(FileNoteStatusHistoryEloquentModel::class,'file_note_id');
    }

    public function getCreatedDateAttribute()
    {
        return $this->created_at->format('d/m/Y');
    }

    public function getCreatedTimeAttribute()
    {
        return $this->created_at->format('H:i:s');
    }
}

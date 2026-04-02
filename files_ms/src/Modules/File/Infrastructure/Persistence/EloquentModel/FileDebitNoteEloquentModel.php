<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileDebitNoteEloquentModel extends Model
{
    use HasFactory, SoftDeletes;
 

    protected $table = 'file_debit_notes';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'file_id',  
        'date',  
        'total',    
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(FileEloquentModel::class);
    }
      
    public function details(): HasMany
    {
        return $this->hasMany(FileDebitNoteDetailEloquentModel::class,'file_debit_note_id');
    }
}

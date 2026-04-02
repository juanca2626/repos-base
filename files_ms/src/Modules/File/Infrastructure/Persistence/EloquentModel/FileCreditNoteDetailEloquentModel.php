<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileCreditNoteDetailEloquentModel extends Model
{
    use HasFactory, SoftDeletes;
 

    protected $table = 'file_credit_note_details';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'file_credit_note_id',
        'description',
        'quantity',        
        'unit_price',   
        'amount',     
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(FileEloquentModel::class);
    }
    
}

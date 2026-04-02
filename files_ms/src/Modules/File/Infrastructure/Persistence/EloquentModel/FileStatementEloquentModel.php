<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileStatementEloquentModel extends Model
{
    use HasFactory, SoftDeletes;
 

    protected $table = 'file_statements';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'file_id',
        'client_id',
        'client_code',        
        'client_name',   
        'date',   
        'deadline',   
        'total',  
        'file_statement_reason_modification_id', 
        'file_statement_reason_modification_others', 
        'user_id',
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
        return $this->hasMany(FileStatementDetailEloquentModel::class,'file_statement_id');
    }
    
}

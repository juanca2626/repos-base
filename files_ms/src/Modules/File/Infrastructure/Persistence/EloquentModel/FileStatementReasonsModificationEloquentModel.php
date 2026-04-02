<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileStatementReasonsModificationEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_statement_reason_modifications';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'name', 
        'created_at',
        'updated_at',
        'deleted_at'
    ];

}

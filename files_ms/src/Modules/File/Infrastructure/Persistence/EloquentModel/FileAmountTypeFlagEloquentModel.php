<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileAmountTypeFlagEloquentModel extends Model
{
    use HasFactory;

    protected $table = 'file_amount_type_flags';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'name',
        'description',
        'icon',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}

<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatusReasonEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'status_reasons';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'status_iso',
        'user_id',
        'name',
        'visible',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

}

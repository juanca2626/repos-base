<?php

namespace Src\Shared\Infrastructure\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceTimeEloquentModel extends Model
{
    use HasFactory;

    protected $table = 'file_service_time';

    protected $connection = 'mysql'; // Conexión principal para escritura
    //protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'name',
        'code',
        'status',
        'created_at',
        'updated_at'
        ];
}
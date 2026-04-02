<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VipEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vips';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'user_id',
        'entity',
        'name',
        'iso',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}

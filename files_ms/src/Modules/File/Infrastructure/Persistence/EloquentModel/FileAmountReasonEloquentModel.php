<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileAmountReasonEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_amount_reasons';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'name',
        'influences_sale',
        'area',
        'visible',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

}

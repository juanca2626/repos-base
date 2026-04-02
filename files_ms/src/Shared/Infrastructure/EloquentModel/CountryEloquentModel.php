<?php

namespace Src\Shared\Infrastructure\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CountryEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'countries';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'name',
        'code',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}

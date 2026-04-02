<?php

namespace Src\Shared\Infrastructure\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CityEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cities';

    protected $connection = 'mysql'; // Conexión principal para escritura
    //protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'name',
        'country_id',
        'created_at',
        'updated_at'
        ];
}
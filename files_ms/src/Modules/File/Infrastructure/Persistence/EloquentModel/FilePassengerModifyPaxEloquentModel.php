<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FilePassengerModifyPaxEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_passenger_modify_paxs';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'file_passenger_id',
        'name',
        'surnames',
        'date_birth',
        'type',
        'suggested_room_type',
        'accommodation',
        'cost_by_passenger', 
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function filePassenger(): BelongsTo
    {
        return $this->belongsTo(FilePassengerEloquentModel::class);
    }
    
}

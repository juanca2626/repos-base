<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileStatusReasonEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_status_reasons';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'file_id',
        'status',
        'status_reason_id', 
        'user_id',       
        'created_at',
        'updated_at',
        'deleted_at'
    ];

 
    public function statusReason(): BelongsTo
    {
        return $this->belongsTo(StatusReasonEloquentModel::class);
    }

}

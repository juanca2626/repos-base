<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileServiceAmountLogEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_service_amount_logs';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'file_amount_type_flag_id',
        'file_amount_reason_id',
        'file_service_id',
        'user_id',
        'amount_previous',
        'amount',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function fileAmountReason(): BelongsTo
    {
        return $this->belongsTo(FileAmountReasonEloquentModel::class);
    }

    public function fileAmountTypeFlag(): BelongsTo
    {
        return $this->belongsTo(FileAmountTypeFlagEloquentModel::class);
    }
}

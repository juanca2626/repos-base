<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\hasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileTemporaryMasterServiceEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_temporary_master_services';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'file_temporary_service_id',
        'master_service_id',
        'name',
        'code',
        'type_ifx',
        'status',
        'confirmation_status',
        'date_in',
        'date_out',
        'start_time',
        'departure_time',
        'amount_cost',
        'is_in_ope',
        'sent_to_ope',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function fileTemporaryService(): BelongsTo
    {
        return $this->belongsTo(FileTemporaryServiceEloquentModel::class);
    }

    public function temporaryService(): BelongsTo
    {
        return $this->belongsTo(FileTemporaryServiceEloquentModel::class);
    }

    public function compositions(): HasMany
    {
        return $this->hasMany(FileTemporaryServiceCompositionEloquentModel::class, 'file_temporary_master_service_id')
            ->orderBy('start_time', 'ASC');
    }

 
}

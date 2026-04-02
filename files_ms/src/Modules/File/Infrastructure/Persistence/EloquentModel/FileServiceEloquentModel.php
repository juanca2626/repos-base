<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\hasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileServiceEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_services';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'file_itinerary_id',
        'master_service_id',
        'name',
        'code',
        'type_ifx',
        'status',
        'confirmation_status',
        'date_in',
        'date_out',
        'start_time',
        'frecuency_code',
        'departure_time',
        'amount_cost',
        'is_in_ope',
        'sent_to_ope',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function fileItinerary(): BelongsTo
    {
        return $this->belongsTo(FileItineraryEloquentModel::class);
    }

    public function itinerary(): BelongsTo
    {
        return $this->belongsTo(FileItineraryEloquentModel::class);
    }

    public function compositions(): HasMany
    {
        return $this->hasMany(FileServiceCompositionEloquentModel::class, 'file_service_id')
            ->orderBy('start_time', 'ASC');
    }

    public function fileServiceAmount(): hasOne
    {
        return $this->hasOne(FileServiceAmountLogEloquentModel::class, 'file_service_id');
    }

    public function fileServiceAmountLogs(): HasMany
    {
        return $this->hasMany(FileServiceAmountLogEloquentModel::class, 'file_service_id')->withTrashed();
    }
}

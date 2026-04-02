<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileItineraryRoomAmountLogEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_itinerary_room_amount_logs';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'file_itinerary_id',
        'file_room_amount_log_id',
        'value',
        'markup',
        'file_amount_reason_id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function fileItinerary(): BelongsTo
    {
        return $this->belongsTo(FileItineraryEloquentModel::class);
    }

    public function fileRoomAmountLog(): BelongsTo
    {
        return $this->belongsTo(FileRoomAmountLogEloquentModel::class)->withTrashed();
    }

}

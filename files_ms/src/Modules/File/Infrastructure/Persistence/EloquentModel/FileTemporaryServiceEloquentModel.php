<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileTemporaryServiceEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    const ENTITY_SERVICE = 'service'; 

    protected $table = 'file_temporary_services';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'file_id',
        'entity',
        'object_id',
        'name',
        'category',
        'object_code',
        'country_in_iso',
        'country_in_name',
        'city_in_iso',
        'city_in_name',
        'zone_in_iso',
        'zone_in_id',
        'zone_in_airport',
        'country_out_iso',
        'country_out_name',
        'city_out_iso',
        'city_out_name',
        'zone_out_iso',
        'zone_out_id',
        'zone_out_airport',
        'start_time',
        'departure_time',
        'date_in',
        'date_out',
        'total_adults',
        'total_children',
        'total_infants',
        'markup_created',
        'status',
        'confirmation_status',
        'total_amount',
        'total_cost_amount',
        'profitability',
        'serial_sharing',
        'data_passengers',
        'policies_cancellation_service',
        'service_rate_id',
        'is_in_ope',
        'sent_to_ope',
        'hotel_origin',
        'hotel_destination',
        'service_supplier_code',
        'service_supplier_name',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(FileEloquentModel::class);
    }
   
    public function details(): HasMany
    {
        return $this->hasMany(FileTemporaryServiceDetailEloquentModel::class, 'file_temporary_service_id');
    }
  
    public function services(): HasMany
    {
        return $this->hasMany(FileTemporaryMasterServiceEloquentModel::class, 'file_temporary_service_id')
            ->orderBy('start_time', 'ASC');
    }
  

}

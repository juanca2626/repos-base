<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; 
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileTemporaryServiceUnitEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_temporary_service_units';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'file_temporary_service_composition_id',
        'status',
        'amount_sale',
        'amount_cost',
        'amount_sale_origin',
        'amount_cost_origin',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function composition(): BelongsTo
    {
        return $this->belongsTo(FileServiceCompositionEloquentModel::class);
    }
    

    public function accommodations(): HasMany
    {
        return $this->hasMany(FileServiceAccommodationEloquentModel::class,'file_service_unit_id');
    }
}

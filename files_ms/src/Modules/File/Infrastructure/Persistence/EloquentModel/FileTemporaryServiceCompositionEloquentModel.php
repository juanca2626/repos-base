<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FileTemporaryServiceCompositionEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_temporary_service_compositions';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'file_temporary_master_service_id',
        'file_classification_id',
        'type_composition_id',
        'type_component_service_id',
        'composition_id',
        'code',
        'name',
        'item_number',
        'duration_minutes',
        'rate_plan_code',
        'total_adults',
        'total_children',
        'total_infants',
        'total_extra',
        'is_programmable',
        'is_in_ope',
        'sent_to_ope',
        'country_in_iso',
        'country_in_name',
        'city_in_iso',
        'city_in_name',
        'country_out_iso',
        'country_out_name',
        'city_out_iso',
        'city_out_name',
        'start_time',
        'departure_time',
        'date_in',
        'date_out',
        'currency',
        'amount_sale',
        'amount_cost',
        'amount_sale_origin',
        'amount_cost_origin',
        'markup_created',
        'taxes',
        'total_services',
        'use_voucher',
        'use_itinerary',
        'voucher_sent',
        'voucher_number',
        'use_ticket',
        'use_accounting_document',
        'ticket_sent',
        'accounting_document_sent',
        'branch_number',
        'document_skeleton',
        'document_purchase_order',
        'status',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function fileTemporaryMasterService(): BelongsTo
    {
        return $this->belongsTo(FileTemporaryMasterServiceEloquentModel::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(FileTemporaryServiceUnitEloquentModel::class, 'file_temporary_service_composition_id');
    }

    public function supplier(): HasOne
    {
        return $this->hasOne(FileTemporaryServiceCompositionSupplierEloquentModel::class, 'file_temporary_service_composition_id');
    }


}

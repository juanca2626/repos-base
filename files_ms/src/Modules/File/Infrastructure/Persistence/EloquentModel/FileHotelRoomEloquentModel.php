<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\hasOne;

class FileHotelRoomEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_hotel_rooms';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'file_itinerary_id',
        'item_number',
        'total_rooms',
        'status',
        'confirmation_status',
        'rate_plan_id',
        'rate_plan_name',
        'rate_plan_code',
        'room_id',
        'room_name',
        'room_type',
        'occupation',
        'channel_id',
        'additional_information',
        'total_adults',
        'total_children',
        'total_infants',
        'total_extra',
        'currency',
        'amount_sale',
        'amount_cost',
        'taxed_sale',
        'taxed_cost',
        'total_amount',
        'markup_created',
        'total_amount_created',
        'total_amount_provider',
        'total_amount_invoice',
        'total_amount_taxed',
        'total_amount_exempt',
        'taxes',
        'use_voucher',
        'use_itinerary',
        'voucher_sent',
        'voucher_number',
        'use_accounting_document',
        'accounting_document_sent',
        'branch_number',
        'document_skeleton',
        'document_purchase_order',
        'executive_code',
        'protected_rate',
        'file_amount_type_flag_id',
        'confirmation_code',
        'channel_reservation_code_master',
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
        return $this->belongsTo(FileItineraryEloquentModel::class, 'file_itinerary_id', 'id');
    }

    public function fileRoomAmount(): hasOne
    {
        return $this->hasOne(FileRoomAmountLogEloquentModel::class, 'file_hotel_room_id');
    }

    public function fileRoomAmountLogs(): HasMany
    {
        return $this->hasMany(FileRoomAmountLogEloquentModel::class, 'file_hotel_room_id')->withTrashed();
    }

    public function hotelRoomUnits(): HasMany
    {
        return $this->hasMany(FileHotelRoomUnitEloquentModel::class, 'file_hotel_room_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(FileHotelRoomUnitEloquentModel::class, 'file_hotel_room_id');
    }

 

}

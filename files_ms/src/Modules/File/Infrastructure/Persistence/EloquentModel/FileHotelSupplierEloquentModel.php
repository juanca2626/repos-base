<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileHotelSupplierEloquentModel extends Model
{
    use HasFactory;

    protected $table = 'file_hotel_suppliers';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'file_itinerary_id',
        'reservation_for_send',
        'for_assign',
        'assigned',
        'code_request_book',
        'code_request_invoice',
        'code_request_voucher',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}

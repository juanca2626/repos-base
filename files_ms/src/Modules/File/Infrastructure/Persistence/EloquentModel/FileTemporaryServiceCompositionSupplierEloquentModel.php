<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileTemporaryServiceCompositionSupplierEloquentModel extends Model
{
    use HasFactory;

    protected $table = 'file_temporary_service_composition_suppliers';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'file_temporary_service_composition_id',
        'reservation_for_send',
        'for_assign',
        'assigned',
        'code_request_book',
        'code_request_invoice',
        'code_request_voucher',
        'policies_cancellation_service', 
        'send_communication',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}

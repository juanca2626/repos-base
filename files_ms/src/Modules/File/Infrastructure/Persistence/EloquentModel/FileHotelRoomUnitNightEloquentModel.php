<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileHotelRoomUnitNightEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_hotel_room_unit_nights';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'file_hotel_room_unit_id',
        'date',
        'number',
        'price_adult_sale',
        'price_adult_cost',
        'price_child_sale',
        'price_child_cost',
        'price_infant_sale',
        'price_infant_cost',
        'price_extra_sale',
        'price_extra_cost',
        'total_amount_sale',
        'total_amount_cost',
        'file_amount_type_flag_id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

}

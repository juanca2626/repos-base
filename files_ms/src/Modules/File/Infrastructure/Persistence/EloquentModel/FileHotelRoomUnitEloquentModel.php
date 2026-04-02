<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileHotelRoomUnitEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_hotel_room_units';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'file_hotel_room_id',
        'channel_id',
        'status',
        'confirmation_code',
        'amount_sale',
        'amount_cost',
        'taxed_unit_sale',
        'taxed_unit_cost',
        'adult_num',
        'child_num',
        'infant_num',
        'extra_num',
        'reservations_rates_plans_rooms_id',
        'rates_plans_rooms_id',
        'channel_reservation_code',
        'confirmation_status',
        'policies_cancellation',
        'taxes_and_services',
        'file_amount_type_flag_id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    protected $appends = ['penalty'];

    public function fileHotelRoom(): BelongsTo
    {
        return $this->belongsTo(FileHotelRoomEloquentModel::class);
    }

    public function accommodations(): HasMany
    {
        return $this->hasMany(FileRoomAccommodationEloquentModel::class, 'file_hotel_room_unit_id');
    }

    public function nights(): HasMany
    {
        return $this->hasMany(FileHotelRoomUnitNightEloquentModel::class,'file_hotel_room_unit_id');
    }
    
    public function getPenaltyAttribute(): array
    {  
        $markup = $this->fileHotelRoom->markup_created;
        $penality = $this->calculatePenalty();

        $penalty_cost = $penality['totalVenta'];
        if($markup > 0){
            $penalty_cost =  ($penality['totalVenta'] / (($markup / 100) + 1));
        }
        return [
            'penalty_cost' => $penalty_cost,
            'penalty_sale' => $penality['totalVenta'],
            'penalty_info' => $penality
        ];
    }

    public function calculatePenalty(): array
    {
 
        $penalidad = [
            'codigoReserva' => $this->reservations_rates_plans_rooms_id,
            'idDetalleSvs' => $this->reservations_rates_plans_rooms_id,
            'totalCosto' => 0,
            'totalIgv' => 0,
            'totalVenta' => 0,
            'notasEjecutiva' => '',
            'tipoCancel' => 'CANCEL',
            'tipo' => 'OK',
        ];

        $igv = [
            'percent' => 0,
            'total_amount' => 0,
        ];
        $extra_fees = json_decode($this->taxes_and_services, true);
        if (isset($extra_fees['apply_fees']) && isset($extra_fees['apply_fees']['t'])) {
            foreach ($extra_fees['apply_fees']['t'] as $extra_fee) {
                if ($extra_fee['name'] == 'IGV') {
                    $igv['percent'] = $extra_fee['value'];
                    $igv['total_amount'] = $extra_fee['total_amount'];
                }
            }
        }

        $policies_cancellation = json_decode($this->policies_cancellation, true);
        if(isset($policies_cancellation) and count($policies_cancellation)>0){
            $policy_cancellation = (object)$policies_cancellation[0];
     
            $_apply_date = explode('-', $policy_cancellation->apply_date);
            $apply_date = $_apply_date[2].'-'.$_apply_date[1].'-'.$_apply_date[0];
            
            // 2021-08-05 < hoy (hoy quieres cancelar, cuando la fecha ya pasó) && ['onRequest'] != 0 (osea es OK) && no fue actualizado el ok desde stella
            if ($apply_date < date("Y-m-d") && $this->confirmation_status != 0) // $this->status->value() != 0 && $this->confirmationStatus->value() == 0
            {         
                $penalty_price = str_replace(',', '', $policy_cancellation->penalty_price);                
                // bug aqui "penalty_price" esta trayendo formateado 1,000.00 cuando son miles y generar errores con suma por es se elimino todas las ","
                $penalty_price = round($penalty_price, 2);
                $igv = round($igv['total_amount'], 2);
    
                $penalidad['totalCosto'] =  $penalty_price;
                $penalidad['totalIgv'] = $igv;
                $penalidad['totalVenta'] = $penalty_price;
                // $penalidad['totalVenta'] = $igv + $penalty_price;
                $penalidad['tipoCancel'] = 'GASCAN';
                
            }
        }
 
        return $penalidad ;
    }
 


}

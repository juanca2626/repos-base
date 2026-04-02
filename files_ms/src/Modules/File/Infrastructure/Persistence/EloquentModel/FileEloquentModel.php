<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\hasOne;

class FileEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_OK = 'OK';
    const STATUS_CANCELED = 'XL';
    const STATUS_LOCKED = 'BL';
    const STATUS_CLOSED = 'CE';
    const STATUS_BY_BILL = 'PF';

    protected $table = 'files';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura
    // protected $appends = array('status_reason', 'status_reason_id', 'total_cost_amount', 'profitability');

    protected $fillable = [
        'serie_reserve_id',
        'client_id',
        'client_have_credit',
        'client_credit_line',
        'reservation_id',
        'order_number',
        'file_number',
        'reservation_number',
        'budget_number',
        'sector_code',
        'group',
        'sale_type',
        'tariff',
        'currency',
        'revision_stages',
        'executive_id',
        'executive_code',
        'executive_code_sale',
        'executive_code_process',
        'applicant',
        'file_code_agency',
        'description',
        'lang',
        'date_in',
        'date_out',
        'adults',
        'children',
        'infants',
        'use_invoice',
        'observation',
        'total_pax',
        'have_quote',
        'have_voucher',
        'have_ticket',
        'have_invoice',
        'status',
        'status_reason_id',
        'processing',
        'promotion',
        'total_amount',
        'type_class_id',
        'suggested_accommodation_sgl',
        'suggested_accommodation_dbl',
        'suggested_accommodation_tpl',
        'generate_statement',
        'reason_statement_id',
        'protected_rate',
        'view_protected_rate',
        'origin',
        'stela_processing',
        'stela_processing_error',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function categories(): HasMany
    {
        return $this->hasMany(FileCategoryEloquentModel::class,'file_id');
    }


    public function vips(): HasMany
    {
        return $this->hasMany(FileVipEloquentModel::class,'file_id');
    }

    public function passengers(): HasMany
    {
        return $this->hasMany(FilePassengerEloquentModel::class,'file_id');
    }

    public function itineraries(): HasMany
    {

        return $this->hasMany(FileItineraryEloquentModel::class,'file_id')
            // ->orderByRaw("
            //      date_in ASC,
            //      CASE
            //          WHEN entity = 'hotel' THEN 2
            //          ELSE 1
            //      END,
            //      start_time ASC
            // ");
            ->orderByRaw("
                date_in ASC,
                CASE
                    WHEN entity = 'flight' AND object_code = 'AEIFLT' AND city_out_iso = 'LIM' THEN 1
                    WHEN entity = 'flight' AND object_code = 'AEIFLT' AND city_out_iso IS NULL AND city_out_iso != 'LIM' THEN 1
                    WHEN entity = 'flight' AND object_code = 'AEIFLT' AND city_out_iso = '' AND city_out_iso != 'LIM' THEN 1
                    WHEN entity = 'service' AND hotel_origin = 1 AND hotel_destination = 0 THEN 2
                    WHEN entity = 'flight' AND object_code = 'AECFLT' THEN 3
                    WHEN entity = 'service' AND hotel_origin = 0 AND hotel_destination = 1 THEN 4
                    WHEN entity = 'flight' AND object_code = 'AEIFLT' AND city_in_iso = 'LIM' THEN 7
                    WHEN entity = 'hotel' THEN 6
                    ELSE 7
                END,
                id asc,
                start_time ASC
            ");

        // return $this->hasMany(FileItineraryEloquentModel::class,'file_id')
        //     ->orderBy('date_in', 'ASC')->orderBy('start_time', 'ASC');

        // return $this->hasMany(FileItineraryEloquentModel::class,'file_id')
        //     ->orderByRaw("
        //          date_in ASC,
        //          CASE
        //              WHEN entity = 'service' and service_type_id = 2 and hotel_origin = 1 and
        //                   hotel_destination = 0 and object_id != 0 THEN 1
        //              WHEN entity = 'flight' and service_type_id IS NULL and hotel_origin is null and
        //                   hotel_destination is null and object_id = 0 THEN 2
        //              WHEN entity = 'service' and service_type_id = 2 and hotel_origin = 0 and
        //                   hotel_destination = 1 and object_id != 0 THEN 3
        //              ELSE 4
        //              END,
        //          id asc,
        //          aurora_reservation_id and
        //          start_time ASC
        //     ");

    }

    public function status_reason(): BelongsTo
    {
        return $this->belongsTo(StatusReasonEloquentModel::class);
    }


    public function fileStatusReason(): HasMany
    {
        return $this->hasMany(FileStatusReasonEloquentModel::class,'file_id')
            ->orderBy('created_at', 'ASC');
    }

    public function fileStatusReasonDesc(): HasMany
    {
        return $this->hasMany(FileStatusReasonEloquentModel::class,'file_id')
            ->orderBy('id', 'DESC');
    }

    public function getStatusReasonAttribute()
    {
        $statusReason = $this->fileStatusReasonDesc()->get();
        if(count($statusReason)>0){
            return $statusReason[0]->statusReason? $statusReason[0]->statusReason->name : '';
        }
        return '';
    }

    public function getStatusReasonIdAttribute()
    {
        $statusReason = $this->fileStatusReasonDesc()->get();
        if(count($statusReason)>0){
            return $statusReason[0]->statusReason? $statusReason[0]->statusReason->id : '';
        }
        return '';
    }

    public function statement(): hasOne
    {
        return $this->hasOne(FileStatementEloquentModel::class, 'file_id');
    }


    public function credit_notes(): hasOne
    {
        return $this->hasOne(FileCreditNoteEloquentModel::class,'file_id');
    }

    public function debit_notes(): hasOne
    {
        return $this->hasOne(FileDebitNoteEloquentModel::class,'file_id');
    }

    public function getTotalCostAmountAttribute(): float
    {
        $totalCost = 0;
        if(count($this->itineraries)>0) {
            foreach($this->itineraries as $itinerary) {
                foreach($itinerary->services as $service) {
                    $totalCost = $totalCost + $service->amount_cost;
                }

                foreach($itinerary->rooms as $room) {
                    $totalCost = $totalCost + $room->amount_cost;
                }
            }
        }

        return round($totalCost, 2) ;
    }

    public function getProfitabilityAttribute(): float
    {
        $import = $this->total_cost_amount;
        $profitability = 0;
        if($import > 0){
            $profitability = round((($this->total_amount - $import ) / $import) * 100, 2);
        }

        return $profitability;
    }



}

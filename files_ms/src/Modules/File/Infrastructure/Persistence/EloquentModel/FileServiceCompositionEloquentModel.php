<?php

namespace Src\Modules\File\Infrastructure\Persistence\EloquentModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FileServiceCompositionEloquentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_service_compositions';

    protected $connection = 'mysql'; // Conexión principal para escritura
    protected $read = 'mysql_read'; // Conexión para lectura

    protected $fillable = [
        'id',
        'file_service_id',
        'file_classification_id',
        'type_composition_id',
        'type_component_service_id',
        'composition_id',
        'code',
        'name',
        'item_number',
        'type_service',
        'requires_confirmation',
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

    protected $appends = ['penalty'];

    public function fileService(): BelongsTo
    {
        return $this->belongsTo(FileServiceEloquentModel::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(FileServiceUnitEloquentModel::class, 'file_service_composition_id');
    }

    public function supplier(): HasOne
    {
        return $this->hasOne(FileCompositionSupplierEloquentModel::class, 'file_service_composition_id');
    }

    public function getPenaltyAttribute(): array
    {
        
        $selected_policies_cancelation = isset($this->supplier) ? json_decode($this->supplier['policies_cancellation_service'] , true) : []; 
        $selected_policies_cancelation = $this->getCancellationPoliticies($selected_policies_cancelation); 
        $penality = [
            'penality_cost' => 0,
            'penality_sale' => 0, 
            'message' => ''
        ];
        // if(count($selected_policies_cancelation)>0){

        //     $current_date = Carbon::now('America/Lima')->format('Y-m-d H:i:s');
        //     $current_date = Carbon::createFromFormat('Y-m-d H:i:s', $current_date, 'America/Lima'); 
    
        //     $total_amount_rate = $this->amountCost->value();
        //     $quantity_persons = $this->totalAdults->value() + $this->totalChildren->value();
            
        //     $startTime = $this->startTime->value() ? $this->startTime->value() : date('H:i:d') ;
        //     $service_start_time = Carbon::createFromFormat('Y-m-d H:i:s', $this->dateIn->value().' '.$startTime, 'America/Lima');

        //     $policies_cancelation = $this->calculateCancellationPoliciesServices($current_date,$total_amount_rate,$selected_policies_cancelation,$quantity_persons,$service_start_time);
    
        //     // $penality = $this->getCancellationStella($policies_cancelation);

        //     $penality['penality_cost'] = $policies_cancelation['penalty_price'];

        //     $penality['penality_sale'] = floatval($policies_cancelation['penalty_price']) * ((floatval($this->markupCreated->value()) / 100) + 1) ;   

        //     // $penality['penality_sale_details'] = [
        //     //     'penalty_price' => floatval($policies_cancelation['penalty_price']),
        //     //     'markup' => floatval($this->markupCreated->value())
        //     // ];

        //     $penality['message'] = $policies_cancelation['message'];                
        // }


        return $penality;

    }

    public function getCancellationPoliticies($selected_policies_cancelation){
    
        $details_params_cancelations = [];
        if(isset($selected_policies_cancelation)){
            foreach ($selected_policies_cancelation as $item) {
                $details_params_cancelations[] = [ 
                    'unit_duration' => $item['unit_duration'],
                    'min_num' => $item['min_pax'],
                    'max_num' => $item['max_pax'],
                    'to' => $item['max_hour'],
                    'from' => $item['min_hour'],
                    'tax' => $item['tax'],
                    'amount' => $item['amount'],
                    'service' => $item['service'],
                    'penalty_id' => $item['penalty_id'],
                    'penalty_name' => $item['penalty_name'],
                ];
            }
        }
        return collect($details_params_cancelations);
    }
}

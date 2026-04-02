<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Src\Shared\Presentation\Resources\BaseResource;

class FileResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {     
        return [
            'id' => $this['id'],
            'serie_reserve_id' => $this['serie_reserve_id'],
            'client_id' => $this['client_id'],
            'client_code' => $this['client_code'],
            'client_name' => $this['client_name'],
            'reservation_id' => $this['reservation_id'],
            'order_number' => $this['order_number'],
            'file_number' => $this['file_number'],
            'reservation_number' => $this['reservation_number'],
            'budget_number' => $this['budget_number'],
            'sector_code' => $this['sector_code'],
            'group' => $this['group'],
            'sale_type' => $this['sale_type'],
            'tariff' => $this['tariff'],
            'currency' => $this['currency'],
            'revision_stages' => $this['revision_stages'],
            'ope_assign_stages' => $this['ope_assign_stages'],            
            'executive_id' => $this['executive_id'],
            'executive_code' => $this['executive_code'],
            'executive_code_sale' => $this['executive_code_sale'],
            'executive_code_process' => $this['executive_code_process'],
            'applicant' => $this['applicant'],
            'file_code_agency' => $this['file_code_agency'],
            'description' => $this['description'],
            'lang' => $this['lang'],
            'date_in' => $this['date_in'],
            'date_out' => $this['date_out'],
            'adults' => $this['adults'],
            'children' => $this['children'],
            'infants' => $this['infants'],
            'use_invoice' => $this['use_invoice'],
            'observation' => $this['observation'],
            'total_pax' => $this['total_pax'],
            'have_quote' => $this['have_quote'],
            'have_voucher' => $this['have_voucher'],
            'have_ticket' => $this['have_ticket'],
            'have_invoice' => $this['have_invoice'],
            'status' => $this['status'],            
            'status_reason_id' => $this['status_reason_id'], // se quito del FileEloquementModel
            'status_reason' =>  $this['status_reason']['name'], // se quito del FileEloquementModel
            'promotion' => $this['promotion'],
            'total_amount' => $this['total_amount'], 
            'statement' => isset($this->resource['statement']) ?  new FileStatementResource($this->resource['statement']) : null,
            'markup_client' => $this['markup_client'],
            'total_cost_amount' => 0, //$this->resource['total_cost_amount'],  // se quito del FileEloquementModel
            'suggested_accommodation_sgl' => $this['suggested_accommodation_sgl'],
            'suggested_accommodation_dbl' => $this['suggested_accommodation_dbl'],
            'suggested_accommodation_tpl' => $this['suggested_accommodation_tpl'], 
            'generate_statement' => $this['generate_statement'],
            'protected_rate' => $this['protected_rate'],
            'view_protected_rate' => $this['view_protected_rate'],
            'file_reason_statement_id' => $this['file_reason_statement_id'],
            'profitability' => 0, //$this->resource['profitability'],  se quito del FileEloquementModel
            'processing' => $this->resource['processing'] ? $this->resource['processing'] : 0,
            // 'deadline' => $this['deadline'], // se elimino de aqui porque esto ya se calculo en statement
            'created_at' => $this['created_at'],            
            'passenger_changes' => $this->resource['passenger_changes'],
            'itinerary_amount_logs' => isset($this->resource['service_amount_logs']) ? FileAmountLogResource::collection($this['service_amount_logs']) : [],  // se quito de la consulta
            'itineraries' => FileItineraryResource::collection($this['itineraries']),
            'passengers' => isset($this['passengers']) ? FilePassengerResource::collection($this['passengers']) : [],
            'vips' => isset($this['vips']) ? FileVipResource::collection($this['vips']) : [],
            'categories' => isset($this['categories']) ? FileCategoriesResource::collection($this['categories']) :[]
        ];
    }


}

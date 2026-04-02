<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileServiceCompositionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // dd($this->units);
        // return $this->resource;
        return [
            'id' => $this->resource['id'],                            
            // 'file_service_id' => $this->resource->fileServiceId->value(),
            // 'file_classification_id' => $this->resource->fileClassificationId->value(),
            // 'type_composition_id' => $this->resource->typeCompositionId->value(),
            // 'type_component_service_id' => $this->resource->typeComponentServiceId->value(),
            // 'composition_id' => $this->resource->compositionId->value(),
            'code' => $this->resource['code'],
            'name' => $this->resource['name'],
            // 'item_number' => $this->resource->itemNumber->value(), 
            // 'rate_plan_code' => $this->resource->ratePlanCode->value(), 
            // 'total_adults' => $this->resource->totalAdults->value(),
            // 'total_children' => $this->resource->totalChildren->value(),
            // 'total_infants' => $this->resource->totalInfants->value(),
            // 'total_extra' => $this->resource->totalExtra->value(),
            'is_programmable' => $this->resource['is_programmable'],
            'is_in_ope' => $this->resource['is_in_ope'],  
            'sent_to_ope' => $this->resource['sent_to_ope'],          
            // 'country_in_iso' => $this->resource->cityInIso->value(), 
            // 'country_in_name' => $this->resource->cityInName->value(), 
            // 'city_in_iso' => $this->resource->cityInIso->value(),
            // 'city_in_name' => $this->resource->cityInName->value(),
            // 'country_out_iso' => $this->resource->cityOutIso->value(), 
            // 'country_out_name' => $this->resource->cityOutName->value(), 
            // 'city_out_iso' => $this->resource->cityOutIso->value(),              
            // 'city_out_name' => $this->resource->cityOutIso->value(),                         
            'start_time' => $this->resource['start_time'],
            'departure_time' => $this->resource['departure_time'],
            'date_in' => $this->resource['date_in'],
            'date_out' => $this->resource['date_out'],
            'currency' => $this->resource['currency'],
            'amount_sale' => $this->resource['amount_sale'],
            'amount_cost' => $this->resource['amount_cost'],
            'amount_sale_origin' => $this->resource['amount_sale_origin'],
            'amount_cost_origin' => $this->resource['amount_cost_origin'],
            // 'taxed_sale' => $this->resource->taxedSale->value(),
            // 'taxed_cost' => $this->resource->taxedCost->value(),
            'markup_created' => $this->resource['markup_created'],
            // 'total_amount_created' => $this->resource->totalAmountCreated->value(), 
            // 'total_amount_invoice' => $this->resource->totalAmountInvoice->value(),
            // 'total_amount_taxed' => $this->resource->totalAmountTaxed->value(),
            // 'total_amount_exempt' => $this->resource->totalAmountExempt->value(),
            // 'taxes' => $this->resource->taxes->value(),
            // 'total_services' => $this->resource->totalServices->value(),
            'use_voucher' => $this->resource['use_voucher'],
            // 'use_itinerary' => $this->resource->useItinerary->value(),
            'voucher_sent' => $this->resource['voucher_sent'],
            'voucher_number' => $this->resource['voucher_number'],
            // 'use_ticket' => $this->resource->useTicket->value(),
            // 'use_accounting_document' => $this->resource->useAccountingDocument->value(),
            // 'ticket_sent' => $this->resource->ticketSent->value(),
            // 'accounting_document_sent' => $this->resource->accountingDocumentSent->value(),
            // 'branch_number' => $this->resource->branchNumber->value(),
            // 'document_skeleton' => $this->resource->documentSkeleton->value(),
            // 'document_purchase_order' => $this->resource->documentPurchaseOrder->value(),             
            'duration_minutes' => $this->resource['duration_minutes'],
            'status' => $this->resource['status'],
            // 'penality' => $this->getPenalty(),
            'units' => isset($this->resource['units']) ? FileServiceUnitResource::collection($this->resource['units']) : [],  
            'supplier' => isset($this->resource['supplier']['id']) ? new FileCompositionSupplierResource($this->resource['supplier']) : [],
            'penality' => $this->resource['penalty']
        ];
    }


}

// 'accommodations' => FileHotelRoomUnitAccommodationResource::collection(
//     $this->accommodations->jsonSerialize()
// )  
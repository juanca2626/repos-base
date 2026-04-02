<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Src\Shared\Presentation\Resources\BaseResource;

class FileResourceOld extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {    
        return [
            'id' => $this->id->value(),
            'serie_reserve_id' => $this->serieReserveId->value(),
            'client_id' => $this->clientId->value(),
            'client_code' => $this->clientCode->value(),
            'client_name' => $this->clientName->value(),
            'reservation_id' => $this->reservationId->value(),
            'order_number' => $this->orderNumber->value(),
            'file_number' => $this->fileNumber->value(),
            'reservation_number' => $this->reservationNumber->value(),
            'budget_number' => $this->budgetNumber->value(),
            'sector_code' => $this->sectorCode->value(),
            'group' => $this->group->value(),
            'sale_type' => $this->saleType->value(),
            'tariff' => $this->tariff->value(),
            'currency' => $this->currency->value(),
            'revision_stages' => $this->revisionStages->value(),
            'ope_assign_stages' => $this->opeAssignStages->value(),
            'executive_code' => $this->executiveCode->value(),
            'executive_code_sale' => $this->executiveCodeSale->value(),
            'executive_code_process' => $this->executiveCodeProcess->value(),
            'applicant' => $this->applicant->value(),
            'file_code_agency' => $this->fileCodeAgency->value(),
            'description' => $this->description->value(),
            'lang' => $this->lang->value(),
            'date_in' => $this->dateIn->value(),
            'date_out' => $this->dateOut->value(),
            'adults' => $this->adults->value(),
            'children' => $this->children->value(),
            'infants' => $this->infants->value(),
            'use_invoice' => $this->useInvoice->value(),
            'observation' => $this->observation->value(),
            'total_pax' => $this->totalPax->value(),
            'have_quote' => $this->haveQuote->value(),
            'have_voucher' => $this->haveVoucher->value(),
            'have_ticket' => $this->haveTicket->value(),
            'have_invoice' => $this->haveInvoice->value(),
            'status' => $this->status->value(),            
            'status_reason_id' => $this->statusReasonId->value(),
            'status_reason' => $this->statusReason->value(),
            'promotion' => $this->promotion->value(),
            'total_amount' => $this->totalAmount->value(),
            'statement' => $this->statement->value(),
            'markup_client' => $this->markupClient->value(),
            'total_cost_amount' => $this->getTotalCostAmount(),
            'suggested_accommodation_sgl' => $this->suggestedAccommodationSgl->value(),
            'suggested_accommodation_dbl' => $this->suggestedAccommodationDbl->value(),
            'suggested_accommodation_tpl' => $this->suggestedAccommodationTpl->value(), 
            'generate_statement' => $this->generateStatement->value(),
            'protected_rate' => $this->protectedRate->value(),
            'view_protected_rate' => $this->viewProtectedRate->value(),
            'file_reason_statement_id' => $this->fileReasonStatementId->value(),
            'profitability' => $this->profitability(),
            'deadline' => $this->deadline(),
            'created_at' => $this->createdAt->value(),            
            'passenger_changes' => $this->passengerChanges->value(),
            'itinerary_amount_logs' => FileAmountLogResource::collection($this->serviceAmountLogs->jsonSerialize()),
            'itineraries' => FileItineraryResource::collection($this->itineraries->jsonSerialize()),
            'passengers' => FilePassengerResource::collection($this->passengers->jsonSerialize()),
            'vips' => FileVipResource::collection($this->vips->jsonSerialize()),
            'categories' => FileCategoriesResource::collection($this->categories->jsonSerialize())
        ];
    }


}

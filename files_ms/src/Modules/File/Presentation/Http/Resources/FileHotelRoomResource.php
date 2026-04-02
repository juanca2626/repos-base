<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileHotelRoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {         
        return [
            'id' => $this->resource['id'],
            'room_id' => $this->resource['room_id'],
            'room_name' => $this->resource['room_name'],
            'room_type' => $this->resource['room_type'],
            'rate_plan_id' => $this->resource['rate_plan_id'],
            'rate_plan_name' => $this->resource['rate_plan_name'],
            'rate_plan_code' => $this->resource['rate_plan_code'],
            'channel_id' => $this->resource['channel_id'],
            'total_rooms' => $this->resource['total_rooms'],
            'status' => $this->resource['status'],
            'confirmation_status' => $this->resource['confirmation_status'],
            'total_adults' => $this->resource['total_adults'],
            'total_children' => $this->resource['total_children'],
            'amount_sale' => $this->resource['amount_sale'],
            'amount_cost' => $this->resource['amount_cost'],
            'markup_created' => $this->resource['markup_created'],
            'room_amount' => isset($this->resource['file_room_amount']) ?  new FileRoomAmountResource($this->resource['file_room_amount']) : [],
            'protected_rate' => $this->resource['protected_rate'],
            'file_room_amount_logs' => isset($this->resource['file_room_amount_logs']) ? FileHotelRoomAmountLogResource::collection(
                $this->resource['file_room_amount_logs']
            ) : [],
            'units' => isset($this->resource['units']) ? FileHotelRoomUnitResource::collection($this->resource['units']) :[],
        ];
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray_bk(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'file_itinerary_id' => $this->resource->fileItineraryId->value(),
            'item_number' => $this->resource->itemNumber->value(),
            'total_rooms' => $this->resource->totalRooms->value(),
            'status' => $this->resource->status->value(),
            'status_hotel' => $this->resource->statusHotel->value(),
            'rate_plan_id' => $this->resource->ratePlanId->value(),
            'rate_plan_name' => $this->resource->ratePlanName->value(),
            'rate_plan_code' => $this->resource->ratePlanCode->value(), 
            'room_name' => $this->resource->roomName->value(),
            'room_type' => $this->resource->roomType->value(),   
            'channel_id' => $this->resource->channelId->value(),         
            'additional_information' => $this->resource->additionalInformation->value(),
            'total_adults' => $this->resource->totalAdults->value(),
            'total_children' => $this->resource->totalChildren->value(),
            'total_infants' => $this->resource->totalInfants->value(),
            'total_extra' => $this->resource->totalExtra->value(),
            'currency' => $this->resource->currency->value(),
            'amount_sale' => $this->resource->amountSale->value(),
            'amount_cost' => $this->resource->amountCost->value(),
            'taxed_sale' => $this->resource->taxedSale->value(),
            'taxed_cost' => $this->resource->taxedCost->value(),
            'total_amount' => $this->resource->totalAmount->value(),
            'markup_created' => $this->resource->markupCreated->value(),
            'total_amount_created' => $this->resource->totalAmountCreated->value(),
            'total_amount_provider' => $this->resource->totalAmountProvider->value(),
            'total_amount_invoice' => $this->resource->totalAmountInvoice->value(),
            'total_amount_taxed' => $this->resource->totalAmountTaxed->value(),
            'total_amount_exempt' => $this->resource->totalAmountExempt->value(),
            'taxes' => $this->resource->taxes->value(),
            'use_voucher' => $this->resource->useVoucher->value(),
            'use_itinerary' => $this->resource->useItinerary->value(),
            'voucher_sent' => $this->resource->voucherSent->value(),
            'voucher_number' => $this->resource->voucherNumber->value(),
            'use_accounting_document' => $this->resource->useAccountingDocument->value(),
            'accounting_document_sent' => $this->resource->accountingDocumentSent->value(),
            'branch_number' => $this->resource->branchNumber->value(),
            'document_skeleton' => $this->resource->documentSkeleton->value(),
            'document_purchase_order' => $this->resource->documentPurchaseOrder->value(),
            'room_amount' => new FileRoomAmountResource($this->roomAmount->jsonSerialize()),
            'file_room_amount_logs' => FileHotelRoomAmountLogResource::collection($this->fileRoomAmountLogs->jsonSerialize()),
            'units' => FileHotelRoomUnitResource::collection($this->units->jsonSerialize()),
        ];
    }


}

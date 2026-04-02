<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileTemporaryServiceCompositionSupplierResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'file_temporary_service_composition_id' => $this->resource->fileTemporaryServiceCompositionId->value(),
            'reservation_for_send' => $this->resource->reservationForSend->value(),
            'assigned' => $this->resource->assigned->value(),
            'for_assign' => $this->resource->forAssign->value(),
            'code_request_book' => $this->resource->codeRequestBook->value(),
            'code_request_invoice' => $this->resource->codeRequestInvoice->value(),
            'code_request_voucher' =>  $this->codeRequestVoucher->jsonSerialize(),
            'policies_cancellation_service' => $this->policiesCancellationService->jsonSerialize(),
            'send_communication' => $this->sendCommunication->jsonSerialize() 
        ];
    }


}

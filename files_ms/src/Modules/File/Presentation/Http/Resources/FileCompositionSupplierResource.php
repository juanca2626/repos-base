<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileCompositionSupplierResource extends JsonResource
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
            'file_service_composition_id' => $this->resource['file_service_composition_id'],
            'reservation_for_send' => $this->resource['reservation_for_send'],
            'assigned' => $this->resource['assigned'],
            'for_assign' => $this->resource['for_assign'],
            'code_request_book' => $this->resource['code_request_book'],
            'code_request_invoice' => $this->resource['code_request_invoice'],
            'code_request_voucher' =>  $this->resource['code_request_voucher'],
            'policies_cancellation_service' => $this->resource['policies_cancellation_service'],
            'send_communication' => $this->resource['send_communication'] 
        ];
    }


}

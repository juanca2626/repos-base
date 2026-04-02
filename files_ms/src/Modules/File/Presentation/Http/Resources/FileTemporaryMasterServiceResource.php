<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileTemporaryMasterServiceResource extends JsonResource
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
            'master_service_id' => $this->resource->masterServiceId->value(),
            'file_temporary_service_id' => $this->resource->fileTemporaryServiceId->value(),
            'name' => $this->resource->name->value(),
            'code_ifx' => $this->resource->codeIFX->value(),
            'type_ifx' => $this->resource->typeIFX->value(),
            'start_time' => $this->resource->startTime->value(),
            'departure_time' => $this->resource->departureTime->value(),
            'amount_cost' => $this->resource->amountCost->value(),
            'status' => $this->resource->status->value(),
            'confirmation_status' => $this->confirmationStatus->value(),
            'is_in_ope' => $this->resource->isInOpe->value(),   
            'sent_to_ope' => $this->resource->sentToOpe->value(), 
            'compositions' => FileTemporaryServiceCompositionResource::collection($this->compositions->jsonSerialize()),
        ];
    }


}


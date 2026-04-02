<?php

namespace Src\Modules\File\Presentation\Http\Resources\Cancelation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileServiceResource extends JsonResource
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
            'file_itinerary_id' => $this->resource->fileItineraryId->value(),
            'name' => $this->resource->name->value(),
            'code_ifx' => $this->resource->codeIFX->value(),
            'type_ifx' => $this->resource->typeIFX->value(),
            'status' => $this->resource->status->value(),
            'confirmation_status' => $this->resource->confirmationStatus->value(), 
            'start_time' => $this->resource->startTime->value(),
            'departure_time' => $this->resource->departureTime->value(),
            'amount_cost' => $this->resource->amountCost->value(),           
            'compositions' => FileServiceCompositionResource::collection($this->compositions->jsonSerialize()),            
        ];
    }


}


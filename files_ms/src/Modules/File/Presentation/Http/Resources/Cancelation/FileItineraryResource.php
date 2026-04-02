<?php

namespace Src\Modules\File\Presentation\Http\Resources\Cancelation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileItineraryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {     
        $results =  [
            'id' => $this->resource->id, 
            'entity' => $this->resource->entity->value(),
            'name'=> $this->resource->name->value(),                          
            'date_in'=> $this->resource->dateIn->value(),
            'date_out'=> $this->resource->dateOut->value(),            
            'origin'=> $this->resource->cityInIso->value(),   
            'destiny'=> $this->resource->cityOutIso->value(),   
            'adults' => $this->resource->totalAdults->value(),
            'children' => $this->resource->totalChildren->value(),  
            'status' => $this->resource->status->value(),
            'confirmation_status' => $this->resource->confirmationStatus->value(),      
        ];

        if($this->resource->entity->value() == "hotel"){
            $results["rooms"] = FileHotelRoomResource::collection($this->rooms->jsonSerialize()); 
        }

        if($this->resource->entity->value() == "service"){
            $results["services"] = FileServiceResource::collection($this->services->jsonSerialize()); 
        }
        
        return $results;

    }


}

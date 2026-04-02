<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileServiceUnitResource extends JsonResource
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
            'status' => $this->resource['status'],                        
            'amount_sale' => $this->resource['amount_sale'],
            'amount_cost' => $this->resource['amount_cost'],
            'amount_sale_origin' => $this->resource['amount_sale_origin'],
            'amount_cost_origin' => $this->resource['amount_cost_origin'],
            'accommodations' => [] //FileServiceAccommodationResource::collection($this->resource['accommodations'])        
        ];
    }


}

// 'accommodations' => FileHotelRoomUnitAccommodationResource::collection(
//     $this->accommodations->jsonSerialize()
// )  
<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileItineraryFlightResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {         
      
        $results =  [
            'id' => $this->resource['id'], 
            'entity' => $this->resource['entity'],
            'name'=> $this->resource['name'],                          
            'date_in'=> $this->resource['date_in'],
            'date_out'=> $this->resource['date_out'],            
            'origin'=> $this->resource['city_in_iso'],   
            'destiny'=> $this->resource['city_out_iso'],   
            'adults' => $this->resource['total_adults'],
            'children' => $this->resource['total_children'],  
            'status' => $this->resource['status'],
            'is_in_ope' => $this->resource['is_in_ope'],
            'sent_to_ope' => $this->resource['sent_to_ope'],
            'confirmation_status' => $this->resource['confirmation_status'], 
            'flights' => isset($this->resource['flights']) ? FileItineraryFlightFlightResource::collection($this->resource['flights']) : [] 
        ];

        return $results;
 
    }


}

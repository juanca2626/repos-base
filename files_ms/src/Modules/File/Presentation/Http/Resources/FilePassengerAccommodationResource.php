<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FilePassengerAccommodationResource extends JsonResource
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
            'name'=> $this->resource['name'],
            'surnames'=> $this->resource['surnames'],        
            'type_passenger'=> strtoupper($this->resource['type']), 
        ];
    }


}

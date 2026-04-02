<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileItineraryLasterResource extends JsonResource
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
            'file_id'=> $this->resource['file_id'],
            'status'=> $this->resource['status'],
            'total_amount'=> $this->resource['total_amount'],
            
        ];
  

        return $results;
    }


}

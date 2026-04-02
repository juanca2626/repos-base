<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileStatementByIdDetailsResource extends JsonResource
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
            'description' => $this->resource['description'], 
            'quantity' => $this->resource['quantity'], 
            'unit_price' => $this->resource['unit_price'],           
            'amount' => $this->resource['amount']            
        ];
    }


}

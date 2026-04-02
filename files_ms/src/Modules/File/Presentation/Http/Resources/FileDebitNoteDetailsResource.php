<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use function Ramsey\Uuid\v1;

class FileDebitNoteDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {  
        return [
            'id' => $this['id'],            
            'description'=> $this['description'],
            'quantity'=> $this['quantity'],       
            'unit_price'=> $this['unit_price'],
            'amount'=> $this['amount'] 
        ];
    }
}

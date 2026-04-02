<?php

namespace Src\Modules\File\Presentation\Http\Resources;
use Carbon\Carbon; 
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileItineraryServiceAmountLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {      
     
        if(isset($this->resource['file_service_amount_log']['amount_previous'])){
            $amount_previous = $this->resource['file_service_amount_log']['amount_previous'];
            $amount = $this->resource['file_service_amount_log']['amount'];
            $date = Carbon::parse($this->resource['file_service_amount_log']['created_at'])->format('d/m/Y H:i:s');
        }else{
            $amount_previous = NULL;
            $amount = $this->resource['value'];
            $date = Carbon::parse($this->resource['created_at'])->format('d/m/Y H:i:s');
        }

        return [
            'id' => $this->resource['id'],
            'user_id' => isset($this->resource['file_service_amount_log']['user_id']) ? $this->resource['file_service_amount_log']['user_id'] : NULL,
            'amount_previous' => $amount_previous, 
            'amount' => $amount, 
            'markup' => $this->resource['markup'],
            'date' => $date 
        ];
    }
}

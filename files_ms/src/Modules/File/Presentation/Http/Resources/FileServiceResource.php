<?php

namespace Src\Modules\File\Presentation\Http\Resources;

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
        $sendCommunication = 'N';
        if(isset($this->resource['compositions']))
        {
            foreach($this->resource['compositions'] as $composition){ 
                
                if(isset($composition['supplier']) && isset($composition['supplier']['file_service_composition_id'])){  
    
                    if($composition['supplier']['send_communication'] == 'S'){
                        $sendCommunication = 'S';
                    } 
                } 
            }
        }
        
        // dd($this->resource);

        return [
            'id' => $this->resource['id'],
            'master_service_id' => $this->resource['master_service_id'],
            'file_itinerary_id' => $this->resource['file_itinerary_id'],
            'name' => $this->resource['name'],
            'code_ifx' => $this->resource['code'],
            'type_ifx' => $this->resource['type_ifx'],
            'date_in'=> $this->resource['date_in'],
            'date_out'=> $this->resource['date_out'],
            'start_time' => $this->resource['start_time'],
            'departure_time' => $this->resource['departure_time'],
            'amount_cost' => $this->resource['amount_cost'],
            'status' => $this->resource['status'],
            'confirmation_status' => $this->resource['confirmation_status'],
            'is_in_ope' => $this->resource['is_in_ope'],   
            'frecuency_code' => $this->resource['frecuency_code'], 
            'sent_to_ope' => $this->resource['sent_to_ope'],
            'send_communication' => $sendCommunication, 
            'service_amount' => isset($this->resource['file_service_amount']['id']) ? new FileServiceAmountResource($this->resource['file_service_amount']) : [],
            'file_service_amount_logs' => isset($this->resource['file_service_amount_logs']) ? FileServiceAmountLogResource::collection(
                $this->resource['file_service_amount_logs']
            ) : [],
            'compositions' => isset($this->resource['compositions']) ? FileServiceCompositionResource::collection($this->resource['compositions']) :[],
        ];
    }


}


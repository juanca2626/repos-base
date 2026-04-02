<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileStatementDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => isset($this->resource['statement']) ? $this->resource['statement']['id'] : NULL,
            'date' => isset($this->resource['statement']) ?  $this->resource['statement']['date'] : NULL, 
            'deadline' => isset($this->resource['statement']) ? $this->resource['statement']['deadline'] : NULL, 
            'file_number' => $this->resource['file_number'], 
            'file_name' => $this->resource['description'], 
            'file_ref' => "",
            'file_date_in' => $this->resource['date_in'] ,
            'file_date_out' => $this->resource['date_out'],            
            'file_client' => [
                'name' => $this->resource['client_name'],
                'address' => $this->resource['client_aurora']->address,
                'ruc' => $this->resource['client_aurora']->ruc,
                'country' => $this->resource['client_aurora']->pais,
                'city' => $this->resource['client_aurora']->ciudad,
                
            ],
            'limatours' => config('global.limatours'),           
            'total' => isset($this->resource['statement']) ? $this->resource['statement']['total'] : NULL, 
            'logs' => isset($this->resource['statement']) ? $this->resource['statement']['logs'] : NULL, 
            'details' => isset($this->resource['statement']) ? FileStatementByIdDetailsResource::collection($this->resource['statement']['details']) :[]
        ];
    }


}

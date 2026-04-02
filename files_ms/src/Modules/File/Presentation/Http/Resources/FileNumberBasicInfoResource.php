<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Src\Shared\Presentation\Resources\BaseResource;

class FileNumberBasicInfoResource extends BaseResource
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
            'file_number' => $this['file_number'],
            'client_id' => $this['client_id'],
            'client_code' => $this['client_code'],
            'client_name' => $this['client_name'],
            'date_in' => $this['date_in'],
            'date_out' => $this['date_out'],
            'description' => $this['description'],
            'adults' => $this['adults'],
            'children' => $this['children'],
            'infants' => $this['infants'],
            'category' => $this['category'],
            'hotel_object_id' => $this['hotel_object_id'],
            'executive_code' => $this['executive_code'],
            'status' => $this['status'],
            'status_reason_id' => $this['status_reason_id'],            
            'status_reason' => $this['status_reason']['name'],
            'lang' => $this['lang'],
            'processing' => $this['processing'] ? $this['processing'] : 0,
            'revision_stages' => $this['revision_stages'], 
            'ope_assign_stages' => $this['ope_assign_stages'],    
            'suggested_accommodation_sgl' => $this['suggested_accommodation_sgl'],
            'suggested_accommodation_dbl' => $this['suggested_accommodation_dbl'],
            'suggested_accommodation_tpl' => $this['suggested_accommodation_tpl'],
            'categories' => isset($this['categories']) ? FileCategoriesResource::collection($this['categories']) :[],
            'vip' => isset($this['vip']) ? $this['vip'] : '',
            'vips' => isset($this['vips']) ? FileVipResource::collection($this['vips']) : []  
        ];
    }


}

<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileServiceAmountResource extends JsonResource
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
            'file_amount_type_flag_id' => $this->resource['file_amount_type_flag_id'],
            'file_amount_reason_id' => $this->resource['file_amount_reason_id'],
            'file_service_id' => $this->resource['file_service_id'],
            'user_id' => $this->resource['user_id'],
            'amount_previous' => $this->resource['amount_previous'],
            'amount' => $this->resource['amount'],
            'file_amount_reason' => isset($this->resource['file_amount_reason']) ?  $this->resource['file_amount_reason'] : [],
            'file_amount_type_flag' => $this->resource['file_amount_type_flag'],
        ];
    }


}

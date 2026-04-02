<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VipResource extends JsonResource
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
            'user_id' => $this->resource['user_id'],
            'entity' => $this->resource['entity'],
            'name' => $this->resource['name'],
            'iso' => $this->resource['iso'],
        ];
    }


}

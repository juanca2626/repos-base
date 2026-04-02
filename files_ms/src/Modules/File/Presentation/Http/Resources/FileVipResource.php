<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileVipResource extends JsonResource
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
            'file_id' => $this->resource['file_id'],
            'vip_id' => $this->resource['vip_id'],
            'vip' => new VipResource($this->resource['vip']),
        ];
    }


}

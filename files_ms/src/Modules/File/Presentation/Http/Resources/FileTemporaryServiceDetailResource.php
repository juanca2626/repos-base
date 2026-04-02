<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileTemporaryServiceDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'language_id' => $this->resource->languageId->value(),
            'itinerary' => $this->resource->itinerary->value(),
            'skeleton' => $this->resource->skeleton->value() 
        ];
    }

}

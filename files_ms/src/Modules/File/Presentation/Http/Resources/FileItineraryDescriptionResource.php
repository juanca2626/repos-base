<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileItineraryDescriptionResource extends JsonResource
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
            'file_itinerary_id' => $this->resource['file_itinerary_id'],
            'language_id' => $this->resource['language_id'],
            'code' => $this->resource['code'],
            'description' => $this->resource['description'],
        ];
    }


}

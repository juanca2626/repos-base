<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileNoteExternalHousingResource extends JsonResource{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request) :array
    {
        return [
            'id'                            => $this['id'] ?? '',
            'date_check_in'                 => $this['date_check_in'] ?? '',
            'date_check_out'                => $this['date_check_out'] ?? '',
            'name_housing'                  => $this['accommodation_name'] ?? '',
            'code_phone'                    => $this['accommodation_code_phone'] ?? '',
            'phone'                         => $this['accommodation_number_phone'] ?? '',
            'address'                       => $this['accommodation_address'] ?? '',
            'lat'                           => $this['accommodation_lat'] ?? '',
            'lng'                           => $this['accommodation_lng'] ?? '',
            'file_id'                       => $this['file_id'] ?? '',
            'created_at'                    => $this['created_at'] ?? '',
            'city'                          => $this['city'] ?? '',
            'passengers'                    => FileNoteExternalHousingPassengersResource::collection($this['passengers'] ?? [])
        ];
    }
}

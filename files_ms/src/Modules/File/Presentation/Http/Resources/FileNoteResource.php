<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileNoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                            => $this['id'],
            'type_note'                     => $this['type_note'],
            'record_type'                   => $this['record_type'],
            'assignment_mode'               => $this['assignment_mode'],
            'dates'                         => $this['dates'],
            'description'                   => $this['description'],
            'classification_code'           => $this['file_classification_code'],
            'classification_name'           => $this['file_classification_name'],
            'file_id'                       => $this['file_id'],
            'itinerary_id'                  => $this['file_itinerary_id'],
            'status'                        => $this['status'],
            'created_by'                    => $this['created_by'],
            'created_by_name'               => $this['created_by_name'],
            'created_date'                  => $this['created_date'],
            'created_hour'                  => $this['created_time']
        ];
    }


}

<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Src\Shared\Presentation\Resources\BaseResource;

class FileNoteGeneralResource extends BaseResource{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(Request $request) :array
    {
        return [
            'id'                            => $this['id'],
            'date_event'                    => $this['date_event'] ?? '',
            'type_event'                    => $this['type_event'] ?? '',
            'description_event'             => $this['description_event'] ?? '',
            'description_client'            => $this['description_client'] ?? '',
            'description_note_general'      => $this['description_note_general'] ?? '',
            'image_logo'                    => $this['image_logo'] ?? '',
            'classification_code'           => $this['classification_code'] ?? '',
            'classification_name'           => $this['classification_name'] ?? '',
            'file_id'                       => $this['file_id'],
        ];
    }
}

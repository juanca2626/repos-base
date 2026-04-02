<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileNoteClassificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(Request $request): array
    {
        return [
            "code"          => trim($this['codigo']),
            "description"   => trim($this['descri'])
        ];
    }
}

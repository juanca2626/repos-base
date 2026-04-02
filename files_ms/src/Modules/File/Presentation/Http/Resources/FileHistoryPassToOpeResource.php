<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Src\Shared\Presentation\Resources\BaseResource;

class FileHistoryPassToOpeResource extends BaseResource
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
            'file_number' => $this['file']['file_number'],                     
            'sede' => $this['sede'],
            'created_at' => \Carbon\Carbon::parse($this['created_at'])->format('d/m/Y H:i:s')                      
        ];
    }

}

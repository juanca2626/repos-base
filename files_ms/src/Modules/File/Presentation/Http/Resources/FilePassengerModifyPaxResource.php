<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FilePassengerModifyPaxResource extends JsonResource
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
            'file_passenger_id' => $this['file_passenger_id'], 
            'name' => $this['name'],
            'surnames' => $this['surnames'],
            'date_birth' => $this['date_birth'],
            'type' => $this['type'],
            'room_type' => $this['suggested_room_type'], 
            'accommodation' => $this['accommodation'],
            'cost_by_passenger' => $this['cost_by_passenger']
        ];
    }


}

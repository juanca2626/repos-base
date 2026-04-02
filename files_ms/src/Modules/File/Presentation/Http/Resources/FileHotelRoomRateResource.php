<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Src\Shared\Presentation\Resources\BaseResource;

class FileHotelRoomRateResource extends BaseResource
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
            'file_id' => $this['file_hotel_room']['itinerary']['file_id'],  
            'file_number' => $this['file_hotel_room']['itinerary']['file']['file_number'],  
            'file_name' => $this['file_hotel_room']['itinerary']['file']['description'],
            'client_code' => $this['file_hotel_room']['itinerary']['file']['client_code'],  
            'client_name' => $this['file_hotel_room']['itinerary']['file']['client_name'],  
            'executive_code' => $this['file_hotel_room']['itinerary']['file']['executive_code'],  
            'date_in' => $this['file_hotel_room']['itinerary']['date_in'], 
            'date_out' => $this['file_hotel_room']['itinerary']['date_out'],      
            'room_name' => $this['file_hotel_room']['room_name'],   
            'file_hotel_room_id' => $this['file_hotel_room']['id'], 
            'amount_cost' => $this['amount_cost'], 
            'file_amount_type_flag_id' => $this['file_amount_type_flag_id'],
            'open' => $this['open'],
            'nights' => $this['nights']
        ];
    }


}

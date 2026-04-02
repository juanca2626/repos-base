<?php

namespace Src\Modules\File\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileAmountLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return object
     */
    public function toArray(Request $request)
    {
        
        if($this->resource['type'] == 'service'){
            return (new FileItineraryServiceAmountLogResource($this->resource));
        }

        if($this->resource['type'] == 'hotel'){ 
            
            return (new FileItineraryRoomAmountLogResource($this->resource));
        }
        

    }


}

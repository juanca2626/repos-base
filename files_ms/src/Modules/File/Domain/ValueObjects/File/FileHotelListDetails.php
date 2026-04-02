<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Modules\File\Domain\Exceptions\FileItineraryCancelationException; 
use Src\Modules\File\Domain\Model\File; 
use Src\Shared\Domain\ValueObjects\ValueObjectArray;
use Illuminate\Support\Collection;

final class FileHotelListDetails extends ValueObjectArray
{
    public readonly array $file;
    public readonly array $services_info_aurora; 

    public function __construct(array $file, array $services_info_aurora  )
    {
        parent::__construct($file);      

        $this->services_info_aurora = $services_info_aurora;  
        $this->file = $this->itineraries($file); 
    }

    /**
     * @return array
     */
    public function itineraries($file): array
    { 
        $hotels = []; 
        foreach($file['itineraries'] as $itinerary){

            if($itinerary['entity'] == 'hotel')
            {   
         
                $confirmation_code = [];
                foreach($itinerary['rooms'] as $room){                                  
                    array_push($confirmation_code, $room['confirmation_code']);
                } 

                $hotels_details = collect($this->services_info_aurora['hotels'])->filter(function($item) use($itinerary) {                                      
                    return $item->itinerary_id == $itinerary['id'];
                })->first();

                $itinerary_filter = collect($hotels_details->meals)->filter(function($item) {              
                    return $item->iso == "es";
                })->first();
             
                array_push($hotels, [                        
                    'city' => $itinerary['city_in_name'],
                    'hotel' => $itinerary['name'],  
                    'date_in' => $itinerary['date_in'],
                    'start_time' => substr($itinerary['start_time'], 0, 5),
                    'date_out' => $itinerary['date_out'], 
                    'departure_time' => substr($itinerary['departure_time'], 0 , 5),
                    'url' => $this->agregarHttpSiNoTiene($hotels_details->url), 
                    'url_origin' => $hotels_details->url, 
                    'stars' => $hotels_details->hotel_stars,
                    'category' => $itinerary['category'],
                    'meal' => $itinerary_filter->name,
                    'confirmation_code' => count($confirmation_code)>0 ? implode(",", $confirmation_code) : ''
                ]);
            } 
 
        } 
        return [ 
            'hotels' => $hotels 
        ];
    }

     
    function agregarHttpSiNoTiene($url) {
        // Verifica si la URL ya comienza con http:// o https:// (ignorando mayúsculas/minúsculas)
        if (!preg_match('/^https?:\/\//i', $url)) {
            $url = 'https://' . $url;
        }
        return $url;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->file;
    }
}

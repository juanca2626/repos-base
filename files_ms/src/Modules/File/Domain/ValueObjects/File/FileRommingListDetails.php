<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Modules\File\Domain\Exceptions\FileItineraryCancelationException; 
use Src\Modules\File\Domain\Model\File; 
use Src\Shared\Domain\ValueObjects\ValueObjectArray;
use Illuminate\Support\Collection;

final class FileRommingListDetails extends ValueObjectArray
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
                $rooms = [];
                foreach($itinerary['rooms'] as $room){ 
                        
                    $passengers = [];
                    foreach($room['units'] as $unit){ 
                        foreach($unit['accommodations'] as $accommodation){  
                            array_push($passengers, [
                                'name' =>  $accommodation['file_passenger']['name'],
                                'surnames' =>  $accommodation['file_passenger']['surnames'],
                                'genre' =>  $accommodation['file_passenger']['genre'],
                                'type' =>  $accommodation['file_passenger']['type'],
                                'country_iso' =>  $accommodation['file_passenger']['country_iso'],
                                'city_iso' =>  $accommodation['file_passenger']['city_iso'],
                                'doctype_iso' =>  $accommodation['file_passenger']['doctype_iso'],
                                'document_number' =>  $accommodation['file_passenger']['document_number'],
                                'date_birth' =>  $accommodation['file_passenger']['date_birth'],
                                'medical_restrictions' =>  $accommodation['file_passenger']['medical_restrictions'],
                                'dietary_restrictions' =>  $accommodation['file_passenger']['dietary_restrictions'] 
                            ]);
                        }
                        
                    }

                    array_push($rooms, [                        
                        'confirmation_code' => $room['confirmation_code'],
                        'room' => $room['room_name'],
                        'passengers' => $passengers
                    ]);
                }  
                
                array_push($hotels, [                        
                    'city' => $itinerary['city_in_name'],
                    'hotel' => $itinerary['name'],  
                    'date_in' => $itinerary['date_in'],
                    'date_out' => $itinerary['date_out'], 
                    'rooms' => $rooms 
                ]);
            } 
 
        } 
        return [ 
            'hotels' => $hotels 
        ];
    }

     
 
    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->file;
    }
}

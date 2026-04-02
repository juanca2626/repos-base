<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Modules\File\Domain\Exceptions\FileItineraryCancelationException; 
use Src\Modules\File\Domain\Model\File; 
use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileItineraryServiceByCommunicationHeader extends ValueObjectArray
{
    public readonly array $file;

    public function __construct(array $file, int $fileItineraryId = null, array $serachAuroraInformation)
    {
        parent::__construct($file);    
        $this->file = $this->parser($file, $fileItineraryId, $serachAuroraInformation);
    }

    /**
     * @return array
     */
    public function parser($file, $fileItineraryId = null ,$serachAuroraInformation): array
    {
            
        $itinerarySelected = null;
        foreach($file['itineraries'] as $itinerary){
            if($itinerary['id'] == $fileItineraryId){
                $itinerarySelected = $itinerary;
            }
        }

        $results = [
            'file_number' => $file['file_number'],
            'description' => $file['description'],  
            'created_at' => $itinerarySelected ? $itinerarySelected['created_at'] : $file['created_at'], 
            'adults' => $itinerarySelected ? $itinerarySelected['total_adults'] : $file['adults'],
            'children' => $itinerarySelected ? $itinerarySelected['total_children'] : $file['children'], 
            'infants' => $itinerarySelected ? $itinerarySelected['total_infants']: $file['infants'], 
        ];

        $results +=  $serachAuroraInformation;         
        return $results;     
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->file;
    }
}

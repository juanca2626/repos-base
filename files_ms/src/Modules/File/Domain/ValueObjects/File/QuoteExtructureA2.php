<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Modules\File\Domain\Exceptions\FileItineraryCancelationException; 
use Src\Modules\File\Domain\Model\File; 
use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class QuoteExtructureA2 extends ValueObjectArray
{
    public readonly array $results;

    public function __construct(array $quote_aurora)
    {
        parent::__construct($quote_aurora);    
        $this->results = $this->parser($quote_aurora);
    }

    /**
     * @return array
     */
    public function parser($quote_aurora): array
    {
        $quote = (array) $quote_aurora[0];
        $itineraries = $quote['categories'][0]->services;             
        $results = [];          
        foreach($itineraries as $ix => $itinerary){
            
            $itinerary = (array) $itinerary;

            if($itinerary['type'] == "group_header"){
                continue;
            }

            if($itinerary['type'] == "service"){
                
                array_push($results, $itinerary);
            }

            if($itinerary['type'] == "flight"){
                
                array_push($results, $itinerary);
            }
        
            if($itinerary['type'] == "hotel"){
                     
                $hotel = collect($results)->filter(function ($result) use ($itinerary){
                    return ($result['type'] === 'hotel') && $result['grouped_code'] === $itinerary['grouped_code'];
                });

                if($hotel && count($hotel)>0){

                    $firstKey = array_key_first($hotel->toArray());   
                    if(!$results[$firstKey]['file_itinerary_id'] and $itinerary['file_itinerary_id']){
                        $results[$firstKey]['file_itinerary_id'] = $itinerary['file_itinerary_id'];
                    }                 
                    array_push($results[$firstKey]['rooms'], $itinerary);

                }else{
                    $itineraryData = $itinerary;
                    $itinerary['rooms'] = [];
                    $itinerary['rooms'][] = $itineraryData;
                    array_push($results, $itinerary);
                     
                }
                                   
            }

        }
        
        $quote['itineraries'] = $results;

        return $quote;     
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->results;
    }
}

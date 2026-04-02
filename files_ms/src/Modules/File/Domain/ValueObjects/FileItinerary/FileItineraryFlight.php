<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;
use Carbon\Carbon; 
use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileItineraryFlight extends ValueObjectArray
{
    public readonly array $filItinerary;
    
    public function __construct(array $filItinerary)
    {
        parent::__construct($filItinerary);    
        $this->filItinerary = $this->parser($filItinerary);        
    }

    /**
     * @return array
     */
    public function parser(array $filItinerary): array
    {
 
        $data = [] ;
        if(count($filItinerary['flights'])>0){
           
           foreach($filItinerary['flights'] as $fights){
                     
                array_push($data, [                
                    "auto_order" => 1, /* con esta llave (y otras más) se debe llegar al nroite 17 x ejem */
                    "type" => substr($filItinerary['name'], 0, 3),
                    "codsvs" => substr($filItinerary['name'], 0, 3),
                    "origin" => $filItinerary['city_in_iso'],
                    "destiny" => $filItinerary['city_out_iso'],
                    "date" => Carbon::parse($filItinerary['date_in'])->format('d/m/Y'),
                    "departure_current" => null, // * nuevo
                    "departure" => $fights['departure_time'],
                    "arrival" => $fights['arrival_time'],
                    "pnr" => $fights['pnr'],
                    "airline" => $fights['airline_code'],
                    "number_flight" => $fights['airline_number'],
                    "paxs" => $fights['nro_pax']                    
                ]);
           }            
        } 

        return $data ;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->filItinerary;
    }
}

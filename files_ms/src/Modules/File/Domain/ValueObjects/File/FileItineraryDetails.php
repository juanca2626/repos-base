<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Modules\File\Domain\Exceptions\FileItineraryCancelationException; 
use Src\Modules\File\Domain\Model\File; 
use Src\Shared\Domain\ValueObjects\ValueObjectArray;
use Illuminate\Support\Collection;

final class FileItineraryDetails extends ValueObjectArray
{
    public readonly array $file;
    public readonly array $services_info_aurora;
    public readonly string $lang;

    public function __construct(array $file, array $services_info_aurora, $lang = 'es' )
    {
        parent::__construct($file);      

        $this->services_info_aurora = $services_info_aurora; 
        $this->lang = $lang;    
        $this->file = $this->itineraries($file); 
    }

    /**
     * @return array
     */
    public function itineraries($file): array
    {
        $itineraries = []; 
        $hotels = [];
        $trains = [];
        foreach($file['itineraries'] as $itinerary){
            
            if($itinerary['entity'] == 'flight')
            {
                if(!isset($itineraries[$itinerary['date_in']])  and count($itinerary['flights'])>0 ){
                    $itineraries[$itinerary['date_in']] = [];

                    $itinerary['positions'] = 1; 
                    array_push($itineraries[$itinerary['date_in']], $itinerary);
                }

            }

            if(in_array($itinerary['entity'], ['service', 'service-temporary']))
            {  
               
                if(!isset($itineraries[$itinerary['date_in']])){
                    $itineraries[$itinerary['date_in']] = [];
                }

                $itinerary['positions'] = 2;

                $train = $this->getTrain($itinerary);
                if(count($train)>0)
                {
                    array_push($trains,$train);
                } 

                array_push($itineraries[$itinerary['date_in']], $itinerary);
            }

            if($itinerary['entity'] == 'hotel')
            { 
                if(!isset($itineraries[$itinerary['date_in']])){
                    $itineraries[$itinerary['date_in']] = [];
                }

                $itinerary['positions'] = 3;       
                $desdeFecha = $itinerary['date_in'];
                $hastaFecha = $itinerary['date_out'];                   
                do{			   	 					   	 			   	 				   	 	 			

                    if(!isset($itineraries[$desdeFecha])){
                        $itineraries[$desdeFecha] = [];
                    }                    
                    array_push($itineraries[$desdeFecha], $itinerary);                	
                    $desdeFecha =  date("Y-m-d", strtotime("$desdeFecha + 1 days")); 	
                }while($desdeFecha<=$hastaFecha);
 
                foreach($itinerary['rooms'] as $room){                  
                    array_push($hotels, [                        
                        'city' => $itinerary['city_in_name'],
                        'hotel' => $itinerary['name'],
                        'confirmation' => $room['confirmation_code'],
                        'room' => $room['room_name'],
                        'date_in' => $itinerary['date_in'],
                        'date_out' => $itinerary['date_out'],
                        'status' => $itinerary['confirmation_status'] == "1" ? "OK" : "RQ"
                    ]);

                }                  
            } 
 
        }
        $data = [];
        $day=1;
  
        foreach($itineraries as $date => $rows){
            
            $city_iso_in = '';
            $city_name_in = '';
            $city_iso_out = '';
            $city_name_out = '';            
            $date_in = '';
            foreach($rows as $row){
                if(in_array($row['entity'], ['service', 'service-temporary','hotel'])){ 
                    $city_iso_in = $row['city_in_iso'];
                    $city_name_in = $row['city_in_name'];
                    $city_iso_out = $row['city_out_iso'];
                    $city_name_out = $row['city_out_name'];                    
                    $date_in = $row['date_in'];
                }
            }

            $itinerary = [];

            foreach($rows as $row){
                $name = '';
                $description = '';
                $hotel_info = [];
                $flight_info = [];
                $city_in_name = $row['city_in_name'];
                $city_out_name = $row['city_out_name'];
                if($row['entity'] == 'flight'){

                    $description = '';

                    $city_in_name = $this->getCityIso($row['city_in_iso']);
                    $city_out_name = $this->getCityIso($row['city_out_iso']);

                    foreach($row['flights'] as $flight){

                        array_push($itinerary, [
                            'id' => $row['id'],
                            'entity' => $row['entity'],
                            'positions' => $row['positions'],
                            'city_in_iso' => $row['city_in_iso'],
                            'city_in_name' => $city_in_name,
                            'date_in' => $row['date_in'],
                            'start_time' => substr($flight['arrival_time'], 0, 5),
                            'city_out' => $row['city_out_iso'],
                            'city_out_name' => $city_out_name,
                            'date_out' => $row['date_out'],
                            'departure_time' => substr($flight['departure_time'], 0 , 5),
                            'name' => $name,
                            'description' => $description, 
                            'airline_code' => $flight['airline_code'],
                            'airline_name' => $flight['airline_name'],
                            'airline_number' => $flight['airline_number'],
                            'departure_time' => $flight['departure_time'],
                            'arrival_time' => $flight['arrival_time']
                        ]);

                        array_push($flight_info, [
                            'airline_code' => $flight['airline_code'],
                            'airline_name' => $flight['airline_name'],
                            'airline_number' => $flight['airline_number'],
                            'departure_time' => $flight['departure_time'],
                            'arrival_time' => $flight['arrival_time']
                        ]);
                    }

                }

                if($row['entity'] == 'hotel'){
                  
                    $hotels_details = collect($this->services_info_aurora['hotels'])->filter(function($item) use($row) {                                      
                        return $item->itinerary_id == $row['id'];
                    })->first();
 
                    $itinerary_filter = collect($hotels_details->meals)->filter(function($item) {              
                        return $item->iso == $this->lang;
                    })->first();

                    $name = $row['name'];
                    $hotel_info = [   
                        'url' => $hotels_details->url,
                        'meal' => $itinerary_filter->name
                    ];

                    array_push($itinerary, [
                        'id' => $row['id'],
                        'entity' => $row['entity'],
                        'positions' => $row['positions'],
                        'city_in_iso' => $row['city_in_iso'],
                        'city_in_name' => $city_in_name,
                        'date_in' => $row['date_in'],
                        'start_time' => substr($row['start_time'], 0, 5),
                        'city_out' => $row['city_out_iso'],
                        'city_out_name' => $city_out_name,
                        'date_out' => $row['date_out'],
                        'departure_time' => substr($row['departure_time'], 0 , 5),
                        'name' => $name,
                        'description' => $description,
                        'hotel_detail' => $hotel_info,
                        'flights' => $flight_info
                    ]);
                }

                if(in_array($row['entity'], ['service', 'service-temporary'])){ 
 
                    $service_details = collect($this->services_info_aurora['services'])->filter(function($item) use($row) {                                      
                        return $item->code == $row['object_code'];
                    })->first();
                    
                    $name = "";
                    $description = "";

                    if($service_details){
                        $itinerary_filter = collect($service_details->details)->filter(function($item) use($row) {              
                            return $item->iso == $this->lang;
                        })->first();
                        $name = $itinerary_filter->name;
                        $description = $itinerary_filter->itinerary;
                    }
                    
                    array_push($itinerary, [
                        'id' => $row['id'],
                        'entity' => $row['entity'],
                        'positions' => $row['positions'],
                        'city_in_iso' => $row['city_in_iso'],
                        'city_in_name' => $city_in_name,
                        'date_in' => $row['date_in'],
                        'start_time' => substr($row['start_time'], 0, 5),
                        'city_out' => $row['city_out_iso'],
                        'city_out_name' => $city_out_name,
                        'date_out' => $row['date_out'],
                        'departure_time' => substr($row['departure_time'], 0 , 5),
                        'name' => $name,
                        'description' => $description,
                        'hotel_detail' => $hotel_info,
                        'flights' => $flight_info
                    ]);
                }

                
            }
    
            array_push($data, [
                'day' => $day,
                'city_in_iso' => $city_iso_in,
                'city_in_name' => $city_name_in,
                'city_out_iso' => $city_iso_out,
                'city_out_name' => $city_name_out,                
                'date' => $date_in, 
                'itineraries' => collect($itinerary)->sortBy('positions')->values()->all()
            ]);

            $day++;
        }      

        return [
            'itineraries' => collect($data)->sortBy('date')->values()->all(),
            'hotels' => $hotels,
            'trains' => $trains
        ];
    }

    public function getCityIso($iso): string
    {
        $fligth_details = collect($this->services_info_aurora['flights'])->filter(function($item) use($iso) {                                      
            return $item->iso == $iso;
        })->first();

        if($fligth_details)
        {
            $itinerary_filter = collect($fligth_details->translations)->filter(function($item){              
                return $item->iso == $this->lang;
            })->first();

            return  isset($itinerary_filter->name) ? $itinerary_filter->name : '';
        }

        return "";
    }

    public function getTrain($itinerary): array{
        $isTrain = false;
        $train_confirmation_code = '';
        $train_pax = 0;
        $train_date_in = '';
        $train_start_time = '';
        $train_date_out = '';
        $train_departure_time = '';
        foreach($itinerary['services'] as $service){
            foreach($service['compositions'] as $composition){
                if($composition['type_service'] == 'TRN'){
                   $isTrain = true;
                   $train_confirmation_code = $composition['confirmation_code'];
                   $train_pax = $composition['total_adults'] + $composition['total_children'] + $composition['total_infants'];
                   $train_date_in = $itinerary['date_in'];
                   $train_start_time = substr($itinerary['start_time'],0,5);
                   $train_date_out = $itinerary['date_out'];
                   $train_departure_time = $itinerary['departure_time'];
                }
            }
        } 

        if($isTrain){
            return  [ 
                'city' => $itinerary['city_in_name'],
                'name' => $itinerary['name'],
                'confirmation' => $train_confirmation_code,
                'pax' => $train_pax,
                'date_in' => $train_date_in,
                'start_time' => $train_start_time,
                'date_out' => $train_date_out,                            
                'departure_time' => $train_departure_time,
                'status' => $itinerary['confirmation_status'] == "1" ? "OK" : "RQ"
            ];
        }

        return [];
    }   
 
    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->file;
    }
}

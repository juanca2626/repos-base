<?php

namespace Src\Modules\File\Domain\ValueObjects\FilePassenger;
  
use Src\Shared\Domain\ValueObjects\ValueObjectArray;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Src\Modules\File\Domain\ValueObjects\File\FilePassengers;

final class AccommodationNew extends ValueObjectArray
{
    public readonly array $results;
    public array $accomodation_passenger_news;
    public array $file_passengers;
    public array $accomodation_passenger;
    public function __construct(array $accomodation_passenger_news, array $accomodation_passenger , array $file_passengers)
    {
        parent::__construct($accomodation_passenger_news);  
        $this->accomodation_passenger_news =  $accomodation_passenger_news;     
        $this->file_passengers = $file_passengers;  
        $this->accomodation_passenger = $accomodation_passenger;            
        $this->results = $this->parser(); 
    }

    /**
     * @return array
     */
    public function parser(): array
    {
        $accomodation_news = []; 
        foreach($this->accomodation_passenger_news as $accomodation){  

            $row_passegnter = $this->search_passenger($accomodation['file_passenger_id']); 
            $room_type = $row_passegnter['suggested_room_type'];
            $exist = $this->exist_room_type_news($room_type,$accomodation_news);

            $passengers = $this->add_passenger_news($accomodation);
            
            if($exist === false){                                
                array_push($accomodation_news, [
                    'type' =>  $room_type,
                    'passengers' => $passengers
                ]);
            }else{
                $accomodation_pre_news = [
                    'type' => $room_type,
                    'passengers' => []
                ];
                foreach($passengers as $passenger){
                    $exist_passnger_accommodation = $this->search_accommodation($room_type,$passenger,$accomodation_news);
                    if(!$exist_passnger_accommodation){ 
                        array_push($accomodation_pre_news['passengers'], $passenger);
                    }
                }

                if(count($accomodation_pre_news['passengers'])>0){

                    array_push($accomodation_news, [
                        'type' =>  $room_type,
                        'passengers' => $passengers
                    ]);
                }
                 
            } 

        }
        
        usort($accomodation_news, fn($a, $b) => $a['type'] <=> $b['type']);

        $this->validateResults($accomodation_news);

        // comparamos la acomodacion actual con la acomodacion nueva, deben tener la misma estructura.
        $this->compareTo($accomodation_news);
        
        return $accomodation_news;     
    }
     
    public function compareTo($accomodation_news)
    {
        $validated = [];
        $equal = [];
        foreach($this->accomodation_passenger as $accomodation){

            $tipe_room = $accomodation['type'];
            foreach($accomodation_news as $index => $accomodation_new){

                if(($tipe_room == $accomodation_new['type']) and  !in_array($index, $validated)){
                    if(count($accomodation['passengers']) == count($accomodation_new['passengers'])){
                        array_push($validated, $index);                        
                        array_push($equal, $this->compareToPassenger($accomodation, $accomodation_new));
                        break;
                    }                
                }

            }
        }
       
        if(count($validated) !== count($this->accomodation_passenger)){
            throw new \DomainException('The structure of the new passenger accommodation should not change.');
        }

        $count = 0;
        foreach($equal as $value){
            if($value == true){
                $count++;
            }
        }

        if($count === count($this->accomodation_passenger)){
            throw new \DomainException('there are no changes in accommodations');
        }
 
    }

    //comparamos si son iguales cada valor 
    public function compareToPassenger($accomodation, $accomodation_new): bool{

        $dataPassenger = $accomodation['passengers']->toArray();
        sort($dataPassenger);
        sort($accomodation_new['passengers']);
        $data1 = implode(",",$dataPassenger);
        $data2 = implode(",",$accomodation_new['passengers']);
        if($data1 == $data2){
           return true;
        }
        return false;
    }

    public function validateResults($accomodation_news)
    {
        //validar que un pasajero no este en varios tipos de habiaticon asignados

        $passengers_type_rooms = [];
        foreach($this->file_passengers as $file_passenger){

            $passengers_type_rooms[$file_passenger['id']] = [];
            foreach($accomodation_news as $index => $accommodation){                
                foreach($accommodation['passengers'] as  $passenger){
                    if($file_passenger['id'] == $passenger){
                        array_push($passengers_type_rooms[$file_passenger['id']], $index);
                    }
                }
            }
 
        }
        
        foreach($passengers_type_rooms as $passengerstyperoom){
            if(count($passengerstyperoom)>1){
                throw new \DomainException('the passenger cannot be in more than one type of room');
            }
        }
        
    }

    public function add_passenger_news($accomodation){

        $passengers = [];

        array_push($passengers, $accomodation['file_passenger_id']);

        if(isset($accomodation['accommodation']) and is_array($accomodation['accommodation']) and count($accomodation['accommodation'])>0){
            foreach($accomodation['accommodation'] as $accommodation){
                $this->search_passenger($accommodation);                         
                array_push($passengers, $accommodation);
            }
        }
        return $passengers;
    }

    /** 
     * Buscar si el room_type  existe dentro de la acomodacion nueva.
    */
    public function exist_room_type_news($room_type,$accomodation_news): bool
    {

        foreach($accomodation_news as $accomodation_new){
            if($room_type == $accomodation_new['type']){
                return true;
            }
        }

        return false;
    }

    public function search_accommodation($room_type,$passenger,$accomodation_news): bool
    {

        foreach($accomodation_news as $accomodation){
            if($accomodation['type'] == $room_type){
                foreach($accomodation['passengers'] as $passenger_new){
                    if($passenger_new == $passenger){
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function search_passenger($passenger): array
    {
        foreach($this->file_passengers as $file_passenger){
            if($file_passenger['id'] == $passenger){
                return $file_passenger;
            }
        }

        throw new \DomainException('passenger ' . $passenger . ' not exists');
    }


    

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->results;
    }
}

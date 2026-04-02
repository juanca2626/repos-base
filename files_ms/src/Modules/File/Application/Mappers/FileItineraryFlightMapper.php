<?php

namespace Src\Modules\File\Application\Mappers;
use Illuminate\Http\Request;
use Src\Modules\File\Domain\Model\File;
use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Model\FileItineraryFlight;
use Src\Modules\File\Domain\ValueObjects\File\FilePassengers;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryId;
use Src\Modules\File\Domain\ValueObjects\FileItineraryFlight\AirlineCode;
use Src\Modules\File\Domain\ValueObjects\FileItineraryFlight\AirlineName;
use Src\Modules\File\Domain\ValueObjects\FileItineraryFlight\AirlineNumber;
use Src\Modules\File\Domain\ValueObjects\FileItineraryFlight\ArrivalTime;
use Src\Modules\File\Domain\ValueObjects\FileItineraryFlight\DepartureTime;
use Src\Modules\File\Domain\ValueObjects\FileItineraryFlight\FileItineraryFlightAccommodations;
use Src\Modules\File\Domain\ValueObjects\FileItineraryFlight\NroPax;
use Src\Modules\File\Domain\ValueObjects\FileItineraryFlight\Pnr;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryFlightEloquentModel;

class FileItineraryFlightMapper
{

    public static function fromRequestCreate(Request $request, array $file ,FileItinerary $fileItinerary, array $file_passengers): FileItineraryFlight 
    {   
        $itinerary = $request->toArray();
            
        // $itinerryTotalPassengers = $fileItinerary->totalAdults->value() + $fileItinerary->totalChildren->value();
        $itinerryTotalPassengers = $file['adults'] + $file['children'];
        $itiniraryFlights = $fileItinerary->flights;
        $totalPassengerAsign = 0; 
        foreach($itiniraryFlights as $itiniraryFlight){ 
            $totalPassengerAsign = $totalPassengerAsign + $itiniraryFlight->nroPax->value();
        } 

        $totalPassengerPerimit = $itinerryTotalPassengers - $totalPassengerAsign;

        $nro_pax = isset($itinerary['nro_pax']) ? $itinerary['nro_pax'] : 0;
        $accommodations = isset($itinerary['accommodations']) ? $itinerary['accommodations'] : [];

        if($nro_pax == 0 and count($accommodations) == 0){
            throw new \DomainException("The number of passengers cannot be zero");
        }

        if($nro_pax != count($accommodations)){
            throw new \DomainException("Different number of passengers");
        }

        if($totalPassengerPerimit<$nro_pax){
            throw new \DomainException("The number of passengers admitted is greater than allowed");
        }

        self::validatePassengers($itinerary['accommodations'],  $file_passengers);
        self::validatePassengerAsigns($itinerary['accommodations'],  $itiniraryFlights);

        $itinerary['file_itinerary_id'] = $fileItinerary->id;
        $itinerary['accommodations'] = self::createFileFlightAccommodationsEntity((array) $itinerary['accommodations']);
        return FileItineraryFlightMapper::fromArray($itinerary);    

    }
    private static function validatePassengers(array $passengers, array $file_passengers): void
    {
        foreach($passengers as $passenger){
            $find = false;
            foreach($file_passengers as $file_passenger){
                if($passenger == $file_passenger['id']){
                    $find = true;
                    break;
                }
            }
            if($find == false){
                throw new \DomainException("The passenger does not exist or is not associated with the file");
            }
        }
    }

    private static function validatePassengerAsigns(array $passengers, $itiniraryFlights): void
    {
     
        foreach($passengers as $passenger){
            $find = false;
            foreach($itiniraryFlights as $itiniraryFlight){ 
                foreach($itiniraryFlight->accommodations as $accommodation){
                    if($passenger == $accommodation->filePassengerId->value()){
                        $find = true;
                        break;break;
                    }
                }
            }
            if($find == true){
                throw new \DomainException("The passenger has already been added to a flight");
            }
        } 
    }


    private static function createFileFlightAccommodationsEntity(array $accommodations): array
    {
        $flightAccommodations = [];
        foreach ($accommodations as $accommodation) {

            $flightAccommodations[] = [
                'id' => null,
                'file_hotel_room_unit_id' => null,
                'file_passenger_id' => $accommodation 
            ];
        }

        return $flightAccommodations;
    }

    public static function fromRequestUpdate(Request $request, array $file , FileItinerary $fileItinerary, array $file_passengers, int $file_tinerary_flight_id): FileItineraryFlight 
    {   
        $itinerary = $request->toArray();
            
        // $itinerryTotalPassengers = $fileItinerary->totalAdults->value() + $fileItinerary->totalChildren->value();
        $itinerryTotalPassengers = $file['adults'] + $file['children'];
        $itiniraryFlights = $fileItinerary->flights;
        $totalPassengerAsign = 0; 
        foreach($itiniraryFlights as $itiniraryFlight){ 
            if($itiniraryFlight->id != $file_tinerary_flight_id){ //quitamos el itinerary_flight_id
                $totalPassengerAsign = $totalPassengerAsign + $itiniraryFlight->nroPax->value();
            }            
        } 

        $totalPassengerPerimit = $itinerryTotalPassengers - $totalPassengerAsign;

        $nro_pax = isset($itinerary['nro_pax']) ? $itinerary['nro_pax'] : 0;
        $accommodations = isset($itinerary['accommodations']) ? $itinerary['accommodations'] : [];

        if($nro_pax == 0 and count($accommodations) == 0){
            throw new \DomainException("The number of passengers cannot be zero");
        }

        if($nro_pax != count($accommodations)){
            throw new \DomainException("Different number of passengers");
        }

        if($totalPassengerPerimit<$nro_pax){
            throw new \DomainException("The number of passengers admitted is greater than allowed");
        }

        self::validatePassengers($itinerary['accommodations'],  $file_passengers);
        self::validatePassengerAsignUpdate($itinerary['accommodations'],  $itiniraryFlights, $file_tinerary_flight_id);

        $itinerary['id'] = $file_tinerary_flight_id;
        $itinerary['file_itinerary_id'] = $fileItinerary->id;
        $itinerary['accommodations'] = self::createFileFlightAccommodationsEntity((array) $itinerary['accommodations']);
        return FileItineraryFlightMapper::fromArray($itinerary);    

    }

    private static function validatePassengerAsignUpdate(array $passengers, $itiniraryFlights, $file_tinerary_flight_id): void
    {
     
        foreach($passengers as $passenger){
            $find = false;
            foreach($itiniraryFlights as $itiniraryFlight){ 
                if($itiniraryFlight->id != $file_tinerary_flight_id){ //quitamos el itinerary_flight_id
                    foreach($itiniraryFlight->accommodations as $accommodation){
                        if($passenger == $accommodation->filePassengerId->value()){
                            $find = true;
                            break;break;
                        }
                    }
                }
            }
            if($find == true){
                throw new \DomainException("The passenger has already been added to a flight");
            }
        } 
    }

    public static function fromArray(array $itineraryFlights): FileItineraryFlight
    { 
        $fileItineraryFlightEloquentModel = new FileItineraryFlightEloquentModel($itineraryFlights);
        $fileItineraryFlightEloquentModel->id = $itineraryFlights['id'] ?? null;
        $fileItineraryFlightEloquentModel->accommodations = collect();

        if (isset($itineraryFlights['accommodations'])) {
            foreach ($itineraryFlights['accommodations'] as $accommodation) {
                $fileItineraryFlightEloquentModel->accommodations->add($accommodation);
            }
        }

        return self::fromEloquent($fileItineraryFlightEloquentModel);
    }

    public static function fromEloquent(FileItineraryFlightEloquentModel $fileItineraryFlightEloquentModel): FileItineraryFlight
    {

        $accommodations = array_map(function ($accommodations) { 
            return FileItineraryFlightAccommodationMapper::fromArray($accommodations);
        }, $fileItineraryFlightEloquentModel->accommodations?->toArray() ?? []);

        return new FileItineraryFlight(
            id: $fileItineraryFlightEloquentModel->id,
            fileItineraryId: new FileItineraryId($fileItineraryFlightEloquentModel->file_itinerary_id),
            airlineName: new AirlineName($fileItineraryFlightEloquentModel->airline_name),
            airlineCode: new AirlineCode($fileItineraryFlightEloquentModel->airline_code),
            airlineNumber: new AirlineNumber($fileItineraryFlightEloquentModel->airline_number),
            pnr: new Pnr($fileItineraryFlightEloquentModel->pnr),
            departureTime: new DepartureTime ($fileItineraryFlightEloquentModel->departure_time),
            arrivalTime: new ArrivalTime ($fileItineraryFlightEloquentModel->arrival_time),        
            nroPax: new NroPax ($fileItineraryFlightEloquentModel->nro_pax),
            accommodations: new FileItineraryFlightAccommodations($accommodations),
        );
    }

    public static function toEloquent(FileItineraryFlight $fileItineraryFlight): FileItineraryFlightEloquentModel
    {
        $fileItineraryFlightEloquent = new FileItineraryFlightEloquentModel();
        if ($fileItineraryFlight->id) {
            $fileItineraryFlightEloquent = FileItineraryFlightEloquentModel::query()->findOrFail($fileItineraryFlight->id);
        }
        $fileItineraryFlightEloquent->file_itinerary_id = $fileItineraryFlight->fileItineraryId->value();
        $fileItineraryFlightEloquent->airline_name = $fileItineraryFlight->airlineName->value();
        $fileItineraryFlightEloquent->airline_code = $fileItineraryFlight->airlineCode->value();
        $fileItineraryFlightEloquent->airline_number = $fileItineraryFlight->airlineNumber->value();
        $fileItineraryFlightEloquent->pnr = $fileItineraryFlight->pnr->value();
        $fileItineraryFlightEloquent->departure_time = $fileItineraryFlight->departureTime->value();
        $fileItineraryFlightEloquent->arrival_time = $fileItineraryFlight->arrivalTime->value();
        $fileItineraryFlightEloquent->nro_pax = $fileItineraryFlight->nroPax->value(); 
       
        return $fileItineraryFlightEloquent;
    }
}

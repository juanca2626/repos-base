<?php
namespace Src\Modules\Itineraries\Submodules\Hotels\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

use Src\Modules\Itineraries\Submodules\Hotels\Application\Builders\HotelItineraryDataBuilder;
use Src\Modules\Itineraries\Submodules\Hotels\Application\Builders\HotelRoomDataBuilder;
use Src\Modules\Itineraries\Infrastructure\Persistence\HotelItineraryRepository;
use Src\Modules\Itineraries\Submodules\Hotels\Infrastructure\Persistence\HotelRoomRepository;
use Src\Modules\Itineraries\Submodules\Hotels\Infrastructure\Persistence\HotelRoomUnitRepository;
use Src\Modules\Itineraries\Submodules\Hotels\Infrastructure\Persistence\HotelRoomUnitNightRepository;
use Src\Modules\Itineraries\Submodules\Hotels\Infrastructure\Persistence\RoomAccommodationRepository;


class CreateHotelItinerary
{
    public function __construct(
        private HotelItineraryDataBuilder $itineraryBuilder,
        private HotelRoomDataBuilder $roomBuilder,
        private HotelItineraryRepository $itineraryRepo,
        private HotelRoomRepository $roomRepo,
        private HotelRoomUnitRepository $unitRepo,
        private HotelRoomUnitNightRepository $nightRepo,
        private RoomAccommodationRepository $accommodationRepo
    ) {}

    public function execute(int $fileId, array $hotel, array $passengerMapBySequence): void
    {
        if (empty($hotel['reservations_hotel_rooms'])) {
            throw new \Exception('Hotel sin rooms');
        }

        DB::transaction(function () use ($fileId, $hotel, $passengerMapBySequence) {

            // ITINERARY
            $itineraryData = $this->itineraryBuilder->build($fileId, $hotel);
            $fileItinerary = $this->itineraryRepo->create($itineraryData);

            $roomData = $this->roomBuilder->build($hotel['reservations_hotel_rooms']);
            
            foreach($roomData as $room)
            {         
                // ROOM          
                $room['file_itinerary_id'] = $fileItinerary->id;
                $units = Arr::pull($room, 'units');                           
                $hotelRoom = $this->roomRepo->create($room);

                foreach($units as $unit)
                {                                        
                    $nights = Arr::pull($unit, 'nights');
                    $accommodations = Arr::pull($unit, 'accommodations');                    

                    $unit['file_hotel_room_id'] = $hotelRoom->id;
                    $hotelRoomUnit = $this->unitRepo->create($unit);

                    // NIGHTS
                    $nights = array_map(function ($item) use ($hotelRoomUnit) {
                        $item['file_hotel_room_unit_id'] = $hotelRoomUnit->id;
                        return $item;
                    }, $nights);

                    $this->nightRepo->insertMany($nights);

                    // ACCOMMODATIONS
                    $accommodations = array_map(function ($item) use ($hotelRoomUnit, $passengerMapBySequence) {
                        $item['file_hotel_room_unit_id'] = $hotelRoomUnit->id;
                        $item['file_passenger_id'] = $passengerMapBySequence[$item['file_passenger_id']];
                        return $item;
                    }, $accommodations);
                    
                    $this->accommodationRepo->insertMany($accommodations);
                }
            }
                    
        });
    }
}
<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileHotelRooms extends ValueObjectArray
{
    public readonly array $fileItineraryHotelRooms;

    public function __construct(array $fileItineraryHotelRooms)
    {
        parent::__construct($fileItineraryHotelRooms);
        $this->fileItineraryHotelRooms = array_values($fileItineraryHotelRooms);
    }

    public function getFileHotelRooms(): FileHotelRooms
    {
        return new FileHotelRooms($this->fileItineraryHotelRooms);
    }

    public function toArray(): array
    {
        $itineraryHotelRooms = [];

        foreach($this->fileItineraryHotelRooms as $hotelRooms) {
            array_push($itineraryHotelRooms,[
                'id' => $hotelRooms->id,
                'room_name' => $hotelRooms->roomName->value(),
                'room_type' => $hotelRooms->roomType->value(),
                'rate_plan_id' => $hotelRooms->ratePlanId->value(),
                'rate_plan_name' => $hotelRooms->ratePlanName->value(),
                'rate_plan_code' => $hotelRooms->ratePlanCode->value(),
                'channel_id' => $hotelRooms->channelId->value(),
                'total_rooms' => $hotelRooms->totalRooms->value(),
                'status' => $hotelRooms->status->value(),
                'confirmation_status' => $hotelRooms->confirmationStatus->value(),
                'total_adults' => $hotelRooms->totalAdults->value(),
                'total_children' => $hotelRooms->totalChildren->value(),
                'amount_sale' => $hotelRooms->amountSale->value(),
                'amount_cost' => $hotelRooms->amountCost->value(),
                'channel_reservation_code_master' => $hotelRooms->channelReservationCodeMaster->value(),
                'units' => $hotelRooms->units,
            ]);
        }

        return $itineraryHotelRooms;
                    
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->fileItineraryHotelRooms;
    }
}

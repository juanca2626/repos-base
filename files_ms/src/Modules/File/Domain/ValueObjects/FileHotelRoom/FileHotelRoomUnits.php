<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;


use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileHotelRoomUnits extends ValueObjectArray
{
    public readonly array $fileHotelRoomUnits;

    public function __construct(array $fileHotelRoomUnits)
    {
        parent::__construct($fileHotelRoomUnits);

        $this->fileHotelRoomUnits = array_values($fileHotelRoomUnits);
    }

    public function getFileHotelRooms(): FileHotelRoomUnits
    {
        return new FileHotelRoomUnits($this->fileHotelRoomUnits);
    }

    public function toArray(): array
    {
        // return $this->fileHotelRoomUnits;

        $itineraryHotelRoomUnits = [];

        foreach($this->fileHotelRoomUnits as $hotelRoomUnit){

            array_push($itineraryHotelRoomUnits,[
                'id' => $hotelRoomUnit->id->value(),
                'file_hotel_room_id' => $hotelRoomUnit->fileHotelRoomId->value(), 
                'confirmation_code' => $hotelRoomUnit->confirmationCode->value(),
                'amount_sale' => $hotelRoomUnit->amountSale->value(),
                'amount_cost' => $hotelRoomUnit->amountCost->value(),
                'taxed_unit_sale' => $hotelRoomUnit->taxedUnitSale->value(),
                'taxed_unit_cost' => $hotelRoomUnit->taxedUnitCost->value(), 
                'adult_num' => $hotelRoomUnit->adultNum->value(), 
                'child_num' => $hotelRoomUnit->childNum->value(), 
                'infant_num' => $hotelRoomUnit->infantNum->value(), 
                'extra_num' => $hotelRoomUnit->extraNum->value(), 
                'status' => $hotelRoomUnit->status->value(),
                'confirmation_status' => $hotelRoomUnit->confirmationStatus->value(),
                'channel_reservation_code' => $hotelRoomUnit->channelReservationCode->value(),
                'penality' =>  $hotelRoomUnit->getPenalty(),               
                'penality_info' =>  $hotelRoomUnit->calculatePenalty(),
                // 'nights' => $hotelRoomUnit->nights,
                // 'accommodations' => $hotelRoomUnit->accommodations
            ]);
        }

        return $itineraryHotelRoomUnits;

    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->fileHotelRoomUnits;
    }
}

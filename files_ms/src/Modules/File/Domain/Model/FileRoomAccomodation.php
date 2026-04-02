<?php

namespace Src\Modules\File\Domain\Model;

use Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit\FileHotelRoomUnitId;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\FilePassengerId;
use Src\Modules\File\Domain\ValueObjects\FileRoomAccomodation\FilePassenger;
use Src\Modules\File\Domain\ValueObjects\FileRoomAccomodation\RoomKey;
use Src\Shared\Domain\Entity;

class FileRoomAccomodation extends Entity
{
    public function __construct(
        public readonly ?int $id,
        public readonly FileHotelRoomUnitId $fileHotelRoomUnitId,
        public readonly FilePassengerId $filePassengerId,
        public readonly RoomKey $roomKey,
        public readonly FilePassenger $filePassenger
    ) {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'file_hotel_room_unit_id' => $this->fileHotelRoomUnitId->value(),
            'file_passenger_id' => $this->filePassengerId->value(),
            'room_key' => $this->roomKey->value(),
            'file_passenger' => $this->filePassenger->jsonSerialize()
        ];
    }

}

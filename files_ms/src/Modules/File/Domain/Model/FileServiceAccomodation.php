<?php

namespace Src\Modules\File\Domain\Model;

use Src\Modules\File\Domain\ValueObjects\FilePassenger\FilePassengerId;
use Src\Modules\File\Domain\ValueObjects\FileServiceAccomodation\Passenger;
use Src\Modules\File\Domain\ValueObjects\FileServiceAccomodation\RoomKey;
use Src\Modules\File\Domain\ValueObjects\FileServiceUnit\FileServiceUnitId;
use Src\Shared\Domain\Entity;

class FileServiceAccomodation extends Entity
{
    public function __construct(
        public readonly ?int $id,
        public readonly FileServiceUnitId $fileServiceUnitId,
        public readonly FilePassengerId $filePassengerId,
        public readonly RoomKey $roomKey,
        public readonly Passenger $passenger,
        
    ) {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'file_service_unit_id' => $this->fileServiceUnitId->value(),
            'file_passenger_id' => $this->filePassengerId->value(),
            'room_key' => $this->roomKey->value(),
            'passenger' => $this->passenger->jsonSerialize(),
        ];
    }

}

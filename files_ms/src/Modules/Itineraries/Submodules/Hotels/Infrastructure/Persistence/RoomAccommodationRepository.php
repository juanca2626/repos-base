<?php

namespace Src\Modules\Itineraries\Submodules\Hotels\Infrastructure\Persistence;

use Src\Modules\Itineraries\Submodules\Hotels\Domain\Models\FileRoomAccommodation;

class RoomAccommodationRepository
{
    public function insertMany(array $rows): void
    {
        FileRoomAccommodation::insert($rows);
    }
}
<?php

namespace Src\Modules\Itineraries\Submodules\Hotels\Infrastructure\Persistence;

use Src\Modules\Itineraries\Submodules\Hotels\Domain\Models\FileHotelRoomUnitNight;

class HotelRoomUnitNightRepository
{
    public function insertMany(array $rows): void
    {
        FileHotelRoomUnitNight::insert($rows);
    }
}
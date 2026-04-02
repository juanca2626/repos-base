<?php

namespace Src\Modules\Itineraries\Submodules\Hotels\Infrastructure\Persistence;

use Src\Modules\Itineraries\Submodules\Hotels\Domain\Models\FileHotelRoomUnit;

class HotelRoomUnitRepository
{
    public function create(array $data): object
    {
        return FileHotelRoomUnit::create($data);
    }
}
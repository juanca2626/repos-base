<?php

namespace Src\Modules\Itineraries\Submodules\Hotels\Infrastructure\Persistence;

use Src\Modules\Itineraries\Submodules\Hotels\Domain\Models\FileHotelRoom;

class HotelRoomRepository
{
    public function create(array $data): object
    {
        return FileHotelRoom::create($data);
    }
}
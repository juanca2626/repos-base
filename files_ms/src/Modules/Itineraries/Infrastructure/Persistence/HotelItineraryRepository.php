<?php

namespace Src\Modules\Itineraries\Infrastructure\Persistence;

use Src\Modules\Itineraries\Domain\Models\FileItinerary;

class HotelItineraryRepository
{
    public function create(array $data): FileItinerary
    {
        return FileItinerary::create($data);
    }
}
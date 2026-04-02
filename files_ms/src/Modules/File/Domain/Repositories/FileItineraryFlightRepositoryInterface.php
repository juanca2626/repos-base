<?php

namespace Src\Modules\File\Domain\Repositories;

use Src\Modules\File\Domain\Model\FileItineraryFlight;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryId;

interface FileItineraryFlightRepositoryInterface
{
    public function findById(int $id): array;
    public function create(int $file_id, FileItineraryFlight $fileItineraryFlight): FileItineraryFlight;
    public function update(int $file_id, FileItineraryFlight $fileItineraryFlight): FileItineraryFlight;
    public function destroy(int $file_id, int $fileItineraryFlightId): array;
    public function updateCityIso(int $file_id, int $fileItineraryFlightId, array $params): array;
    public function updateDate(int $file_id, int $id, array $params): array;
    public function updateItineraryTime(int $fileItineraryId):bool;
}

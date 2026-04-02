<?php

namespace Src\Modules\File\Domain\Repositories;

use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\ValueObjects\File\FileId;
use Src\Modules\File\Domain\ValueObjects\File\FileItineraries;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\StartTime;

interface FileItineraryRepositoryInterface
{
    
    public function searchFlights(int $file_id): array;
    public function create(FileItinerary $fileData): array;
    public function update(FileItinerary $fileData, int $fileItineraryId): array;     
    public function updateSchedule(int $id, array $params): array;
    public function updateStatusRate(int $id, array $params): bool;
    public function updateStatusRateNeg(int $id, array $params): bool;
    public function searchMasterServicesByItinerary(array $params): array;
    public function findById(int $id, string $type): FileItinerary|array;
    public function findByIdArray(int $id, string $type): array;
    public function updateTotalCostAmount(int $id): bool;
    public function updateTotalAmount(int $id): bool;    
    public function updateProfitability(int $id): bool;
    public function serachFileItineraryByCancellation(array $params): array;
    public function cancelItineraryRoomUnits(int $id): array;
    public function updateStatus(int $id, int $status): bool;
    public function cancel(int $id): bool;
    public function updateAmountSale(int $id, array $params): bool;
    public function serch_accomodation(int $id): array;
    public function updateAccommodation(int $id, array $params): bool;
    public function updatePax(int $file_itinerary_id): bool;
    public function updateConfirmationStatus(int $id, array $params): bool;
    public function update_number_of_passengers(int $id, array $params): bool;
    public function delete_flight(int $id): array;   
    public function updateAccommodationServices(int $id, array $params): bool;   
    public function associateTemporaryService(int $id, array $params): bool; 
    public function viewProtectedRate(int $id, bool $status): bool;
    public function update_add_statement(int $file_id, array $params): bool;
    public function searchFileLatestItineriesQuery(array $params): array;  
}

<?php

namespace Src\Modules\File\Domain\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Application\DTO\FileWithRelationsData;
use Src\Modules\File\Domain\Model\File;


interface FileRepositoryInterface
{
    public function create(File $file): bool;
    // public function create_basic_file(File $file): File;
    public function validateFileExist(int|null $file_id, int|null $file_number): array | null;
    public function findByIdFields(int $id, array $fields): array;
    public function findByNumber(int $file_number): array | null;
    public function findById(int $id): ?File;
    public function findByIdAll(int $id): ?array;
    public function findByIdComplet(int $id): ?array;    
    public function find_file_basic_info(int $id): array  | null;
    public function find_file_number_basic_info(int $file_number, array $params): array | null;
    public function findByIdToArray(int $id): ?array;
    public function update(int $id, array $file): File;
    public function delete(int $id): bool;
    public function searchFilesQuery(array $filters): LengthAwarePaginator;
    public function searchFileStelaQuery(array $filters): array;
    public function findAll(): LengthAwarePaginator;
    public function statistics(string $date): array;
    public function updateSerie(array $params): bool;
    public function removeSerie(array $params): bool;
    public function searchAllStatus(array $lang_iso): array;
    public function searchAllStages(): array;
    public function searchHaveInvoice(array $params): array;
    public function updateAccommodations(int $id, string $type, int $type_id, array $passengers): bool;
    public function updatePassengers(int $id, array $passengers): bool;
    public function updateStatement(int $id): bool;
    public function getEternalExtructureReservation(array $params, array $file) : array;
    public function cancelFile(int $id, int|null $status_reason_id): bool;
    public function statusChanges(int $id, string $status, int  $status_reason_id): bool;
    public function activateFile(int $id, int $status_reason_id): bool;
    public function viewProtectedRate(int $id, bool $status): bool;
    public function canceledServices(int $id) : array;
    public function updatePassengerChanges(int $id, bool $statusChanges): bool;    
    public function invoicedChanges(int $id, bool $status ): bool;
    public function inOpeFinish(int $id, int $status ): bool;
    public function searchFileCreateExistQuery(File $file): File|null;
    public function searchHotelRateFilesQuery(array $params): array;
    public function updateAmountFromAurora(array $params): array;
    public function searchByCommunication(array $params): array;
    public function searchItineraryReport(int $file_id,array $params): array;
    public function searchItineraryReportByHotel(int $file_id,string $hotel_code, array $params): array;
    public function searchInA2DetailsService(array $params): array;
    public function create_stela(array $params): array;
    public function create_stela_all(array $params): bool;
    public function create_file_services_stela(int $file_id, array $params): bool;
    public function processingChanges(int $id, string $status): bool;
 

}

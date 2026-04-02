<?php

namespace Src\Modules\File\Domain\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Model\FileService;
use Src\Modules\File\Domain\ValueObjects\FileService\StartTime; 

interface FileRoomAccommodationRepositoryInterface
{     
    public function create(int $file_service_unit_id, int $file_passenger_id, string $room_key): bool;
    public function delete(int $file_service_unit_id): bool;
     
}

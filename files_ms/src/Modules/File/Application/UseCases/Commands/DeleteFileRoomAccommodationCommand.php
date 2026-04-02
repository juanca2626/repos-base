<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Repositories\FileRoomAccommodationRepositoryInterface;
use Src\Modules\File\Domain\ValueObjects\FileService\AmountCost;
use Src\Shared\Domain\CommandInterface;

class DeleteFileRoomAccommodationCommand implements CommandInterface
{
    private FileRoomAccommodationRepositoryInterface $fileServiceAccommodationRepository;

    public function __construct(private readonly int $file_hotel_room_unit_id)
    {
        $this->fileServiceAccommodationRepository = app()->make(FileRoomAccommodationRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function execute(): bool
    {
        return $this->fileServiceAccommodationRepository->delete($this->file_hotel_room_unit_id);
    }
}

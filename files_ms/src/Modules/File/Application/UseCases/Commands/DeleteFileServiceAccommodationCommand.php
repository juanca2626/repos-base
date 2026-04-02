<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Repositories\FileServiceAccommodationRepositoryInterface;
use Src\Modules\File\Domain\ValueObjects\FileService\AmountCost;
use Src\Shared\Domain\CommandInterface;

class DeleteFileServiceAccommodationCommand implements CommandInterface
{
    private FileServiceAccommodationRepositoryInterface $fileServiceAccommodationRepository;

    public function __construct(private readonly int $file_service_unit_id)
    {
        $this->fileServiceAccommodationRepository = app()->make(FileServiceAccommodationRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function execute(): bool
    {
        return $this->fileServiceAccommodationRepository->delete($this->file_service_unit_id);
    }
}

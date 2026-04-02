<?php

namespace Src\Modules\File\Application\UseCases\Commands;
 
use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Repositories\FileItineraryRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class UpdateFileItineraryStatusCommand implements CommandInterface
{
    private FileItineraryRepositoryInterface $repository;

    public function __construct(private readonly int $fileItineraryId, private readonly int $status,)
    {
        $this->repository = app()->make(FileItineraryRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->repository->updateStatus($this->fileItineraryId, $this->status);
    }
}


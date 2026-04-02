<?php

namespace Src\Modules\File\Application\UseCases\Commands;
 
use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Repositories\FileItineraryRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class UpdateFileItineraryCommand implements CommandInterface
{
    private FileItineraryRepositoryInterface $repository;

    public function __construct(private readonly FileItinerary $fileItinerary, private readonly int $fileItineraryId)
    {
        $this->repository = app()->make(FileItineraryRepositoryInterface::class);
    }

    public function execute(): array
    {
        return $this->repository->update($this->fileItinerary, $this->fileItineraryId);
    }
}


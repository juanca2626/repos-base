<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Repositories\FileItineraryFlightRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class UpdateFileItineraryFlightTimeCommand implements CommandInterface
{
    private FileItineraryFlightRepositoryInterface $repository;

    public function __construct(private readonly int $fileItineraryId)
    {
        $this->repository = app()->make(FileItineraryFlightRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->repository->updateItineraryTime($this->fileItineraryId);
    }
}

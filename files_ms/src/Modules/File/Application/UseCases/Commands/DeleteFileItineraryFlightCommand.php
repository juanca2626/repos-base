<?php

namespace Src\Modules\File\Application\UseCases\Commands;
  
use Src\Modules\File\Domain\Repositories\FileItineraryFlightRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class DeleteFileItineraryFlightCommand implements CommandInterface
{
    private FileItineraryFlightRepositoryInterface $repository;

    public function __construct(private readonly int $file_id, private readonly int $fileItineraryFlightId)
    {
        $this->repository = app()->make(FileItineraryFlightRepositoryInterface::class);
    }

    public function execute(): array
    {
        return $this->repository->destroy($this->file_id, $this->fileItineraryFlightId);
    }
}


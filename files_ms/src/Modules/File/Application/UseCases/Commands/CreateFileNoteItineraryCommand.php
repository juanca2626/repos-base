<?php

namespace Src\Modules\File\Application\UseCases\Commands;
use Src\Modules\File\Domain\Repositories\FileNoteItineraryRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class CreateFileNoteItineraryCommand implements CommandInterface{
    private FileNoteItineraryRepositoryInterface $repository;

    public function __construct(private readonly int $fileNoteId, private readonly int $id, private readonly array $params)
    {
        $this->repository = app()->make(FileNoteItineraryRepositoryInterface::class);
    }

    public function execute() : array
    {
        return $this->repository->storeFileNoteItinerary($this->fileNoteId, $this->id, $this->params);
    }
}

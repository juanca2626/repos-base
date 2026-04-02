<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Repositories\FileNoteItineraryRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class DeleteFileNoteItineraryServiceCommand implements CommandInterface
{
    private FileNoteItineraryRepositoryInterface $repository;

    public function __construct(private readonly int $itinerary_id, private readonly array $params)
    {
        $this->repository = app()->make(FileNoteItineraryRepositoryInterface::class);
    }
    public function execute(): bool{
        return $this->repository->deleteFileNoteItineraryService($this->itinerary_id, $this->params);
    }
}

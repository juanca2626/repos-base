<?php

namespace Src\Modules\File\Application\UseCases\Commands;
use Src\Modules\File\Domain\Model\FileNote;
use Src\Modules\File\Domain\Repositories\FileNoteItineraryRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class UpdateFileNoteItineraryCommand implements CommandInterface{
    private FileNoteItineraryRepositoryInterface $repository;

    public function __construct(private readonly int $fileNoteId, private readonly int $itinerary_id, private readonly int $note_id, private readonly array $params)
    {
        $this->repository = app()->make(FileNoteItineraryRepositoryInterface::class);
    }

    public function execute() : array
    {
        return $this->repository->updateFileNoteItinerary($this->fileNoteId, $this->itinerary_id, $this->note_id, $this->params);
    }
}

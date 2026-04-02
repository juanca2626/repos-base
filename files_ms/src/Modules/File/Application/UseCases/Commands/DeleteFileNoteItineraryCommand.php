<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Repositories\FileNoteItineraryRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class DeleteFileNoteItineraryCommand implements CommandInterface
{
    private FileNoteItineraryRepositoryInterface $repository;

    public function __construct(private readonly int $file_id,private readonly int $note_id, private readonly array $params)
    {
        $this->repository = app()->make(FileNoteItineraryRepositoryInterface::class);
    }
    public function execute(): bool{
        return $this->repository->deleteFileNoteItinerary($this->file_id,$this->note_id, $this->params);
    }
}

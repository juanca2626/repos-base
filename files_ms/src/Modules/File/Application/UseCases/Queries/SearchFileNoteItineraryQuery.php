<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Repositories\FileNoteItineraryRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchFileNoteItineraryQuery implements QueryInterface {
    private FileNoteItineraryRepositoryInterface $fileRepository;

    public function __construct(private readonly int $fileId, private readonly int $id)
    {
        $this->fileRepository = app()->make(FileNoteItineraryRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileRepository->listFileNoteItinerary($this->fileId, $this->id);
    }
}

<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Repositories\FileItineraryRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class FindFileItineraryByIdQuery implements QueryInterface
{
    private FileItineraryRepositoryInterface $fileItineraryRepository;

    public function __construct(private readonly int $fileItineraryId, private readonly string $type="")
    {
        $this->fileItineraryRepository = app()->make(FileItineraryRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): FileItinerary|array
    {
        return $this->fileItineraryRepository->findById($this->fileItineraryId, $this->type);
    }
}

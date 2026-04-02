<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Repositories\FileItineraryRepositoryInterface;
use Src\Modules\File\Domain\ValueObjects\File\FileItineraries;
use Src\Shared\Domain\QueryInterface;

class SearchFileItineraryFlightQuery implements QueryInterface
{
    private FileItineraryRepositoryInterface $fileItineraryRepository;

    public function __construct(private readonly int $fileId)
    {
        $this->fileItineraryRepository = app()->make(FileItineraryRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileItineraryRepository->searchFlights($this->fileId);
    }
}

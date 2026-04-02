<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Repositories\FileItineraryRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchMasterServiceByIdQuery implements QueryInterface
{
    private FileItineraryRepositoryInterface $fileItineraryRepository;

    public function __construct(private readonly array $params)
    {
        $this->fileItineraryRepository = app()->make(FileItineraryRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileItineraryRepository->searchMasterServicesByItinerary($this->params);
    }
}

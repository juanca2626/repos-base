<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Repositories\FileTemporaryServiceRepositoryInterface; 
use Src\Modules\File\Domain\ValueObjects\File\FileTemporaryServices;
use Src\Shared\Domain\QueryInterface;

class SearchServiceTemporaryQuery implements QueryInterface
{
    private FileTemporaryServiceRepositoryInterface $fileItineraryRepository;

    public function __construct(private readonly array $filters)
    {
        $this->fileItineraryRepository = app()->make(FileTemporaryServiceRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): FileTemporaryServices
    {
        return $this->fileItineraryRepository->searchItineraryQueryServiceTemporary($this->filters);
    }
}

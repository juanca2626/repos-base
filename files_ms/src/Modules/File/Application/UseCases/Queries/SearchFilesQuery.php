<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Repositories\FileRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchFilesQuery implements QueryInterface
{
    private FileRepositoryInterface $fileRepository;

    public function __construct(private readonly array $filters)
    {
        $this->fileRepository = app()->make(FileRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): LengthAwarePaginator
    {
        return $this->fileRepository->searchFilesQuery($this->filters);
    }
}

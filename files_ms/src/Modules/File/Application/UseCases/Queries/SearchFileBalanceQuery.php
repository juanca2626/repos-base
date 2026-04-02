<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Repositories\FileBalanceRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchFileBalanceQuery implements QueryInterface{
    private FileBalanceRepositoryInterface $fileRepository;

    public function __construct(private readonly array $filters)
    {
        $this->fileRepository = app()->make(FileBalanceRepositoryInterface::class);
    }
    /**
     * @return mixed
     */
    public function handle(): LengthAwarePaginator{
        return $this->fileRepository->index($this->filters);
    }
}


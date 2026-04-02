<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Repositories\FileReasonStatementRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchFileReasonStatementQuery implements QueryInterface
{
    private FileReasonStatementRepositoryInterface $fileAmountReasonRepository;

    public function __construct(private readonly array $filters)
    {
        $this->fileAmountReasonRepository = app()->make(FileReasonStatementRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileAmountReasonRepository->searchAll($this->filters);
    }
}

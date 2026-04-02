<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Repositories\FileStatementReasonsModificationRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchFileStatementReasonsModificationQuery implements QueryInterface
{
    private FileStatementReasonsModificationRepositoryInterface $fileAmountReasonRepository;

    public function __construct(private readonly array $filters)
    {
        $this->fileAmountReasonRepository = app()->make(FileStatementReasonsModificationRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileAmountReasonRepository->searchAll($this->filters);
    }
}
